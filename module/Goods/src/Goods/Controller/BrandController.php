<?php
/**
 * DBShop 电子商务系统
 *
 * ==========================================================================
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2015 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 * ==========================================================================
 *
 * @author    静静的风
 *
 */

namespace Goods\Controller;

use Zend\View\Model\ViewModel;
use Admin\Controller\BaseController;

class BrandController extends BaseController
{
    /**
     * The default action - show the home page
     */
    public function indexAction ()
    {
        $array = array();
        //品牌列表
        $array['brand_array'] = $this->getDbshopTable()->listGoodsBrand();
        
        return $array;
    }
    /**
     * 商品品牌添加
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:
     */
    public function addAction ()
    {
        $array = array();
        if($this->request->isPost()) {
            $brandArray = $this->request->getPost()->toArray();
            //添加商品品牌
            $brandId    = $this->getDbshopTable()->addBrand($brandArray);
            if($brandId) {
                $brandArray['brand_id'] = $brandId;
                $brandArray['language'] = $this->getDbshopLang()->getLocale();
                //品牌logo上传
                $logoInfo = $this->getServiceLocator()->get('shop_goods_upload')->brandLogoUpload('brand_logo');
                if(!empty($logoInfo['image'])) {
                    $brandArray['brand_logo'] = $logoInfo['image'];
                }
                //品牌扩展信息添加
                $this->getDbshopTable('GoodsBrandExtendTable')->addGoodsBrandExtend($brandArray);
                
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('商品品牌'), 'operlog_info'=>$this->getDbshopLang()->translate('添加商品品牌') . '&nbsp;' . $brandArray['brand_name']));
                
                unset($brandArray);
                return $this->redirect()->toRoute('brand/default',array('controller'=>'brand','action'=>'index'));
                
            }
        }
        return $array;
    }
    
    public function editAction ()
    {
        $brandId = (int) $this->params('brand_id', 0);
        if(!$brandId) {//品牌id不存在时
            return $this->redirect()->toRoute('brand/default',array('controller'=>'brand'));
        }
        $array   = array();
        if($this->request->isPost()) {
            $brandArray = $this->request->getPost()->toArray();
            $this->getDbshopTable()->updateGoodsBrand($brandArray,array('brand_id'=>$brandId));
            
            //品牌logo上传
            $logoInfo = $this->getServiceLocator()->get('shop_goods_upload')->brandLogoUpload('brand_logo', (isset($brandArray['old_brand_logo']) ? $brandArray['old_brand_logo'] : ''));
            $brandArray['brand_logo'] = $logoInfo['image'];

            $this->getDbshopTable('GoodsBrandExtendTable')->updateBrandextend($brandArray,array('brand_id'=>$brandId,'language'=>$this->getDbshopLang()->getLocale()));
            
            //品牌中的产品删除
            if(isset($brandArray['goods_id']) and is_array($brandArray['goods_id']) and !empty($brandArray['goods_id'])) {
                $this->getDbshopTable('GoodsTable')->oneUpdateGoods(array('brand_id'=>0), array('goods_id IN ('.implode(',',$brandArray['goods_id']).')'));
            }
            
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('商品品牌'), 'operlog_info'=>$this->getDbshopLang()->translate('更新商品品牌') . '&nbsp;' . $brandArray['brand_name']));
            
            if($brandArray['brand_save_type'] != 'save_return_edit') {
                return $this->redirect()->toRoute('brand/default',array('controller'=>'brand','action'=>'index'));
            }
            $array['success_msg'] = $this->getDbshopLang()->translate('商品品牌编辑成功！');
        }
        
        $array['brand_info']  = $this->getDbshopTable()->infoBrand(array('dbshop_goods_brand.brand_id'=>$brandId,'e.language'=>$this->getDbshopLang()->getLocale()));

        return $array;
    }
    /**
     * 品牌删除操作
     */
    public function delAction ()
    {
        $brandId   = intval($this->request->getPost('brand_id'));
        if($brandId) {
            //为了记录操作日志使用
            $brandInfo  = $this->getDbshopTable()->infoBrand(array('dbshop_goods_brand.brand_id'=>$brandId,'e.language'=>$this->getDbshopLang()->getLocale()));
            
            if($this->getDbshopTable()->delBrand(array('brand_id'=>$brandId))) {
                $this->getDbshopTable('GoodsBrandExtendTable')->delBrandextend(array('brand_id'=>$brandId));
                $this->getDbshopTable('GoodsTable')->oneUpdateGoods(array('brand_id'=>0),array('brand_id'=>$brandId));
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('商品品牌'), 'operlog_info'=>$this->getDbshopLang()->translate('删除商品品牌') . '&nbsp;' . $brandInfo->brand_name));
                
                echo 'true';
            }
        }
        exit;
    }
    /**
     * 批量修改品牌信息
     * @return \Zend\Http\Response
     */
    public function allBrandUpdateAction()
    {
        if($this->request->isPost()) {
            $array  = $this->request->getPost()->toArray();
            if(is_array($array) and !empty($array)) {
                foreach($array['brand_sort'] as $key => $value) {
                    $this->getDbshopTable()->allBrandUpdate(array('brand_sort'=>$value), array('brand_id'=>$key));
                }
            }
        }
        //跳转处理
        return $this->redirect()->toRoute('brand/default',array('controller'=>'brand'));
    }
    public function brandajaxgoodsAction ()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        
        $searchArray = array();
        $brandId     = (int) $this->params('brand_id', 0);
        if($brandId != 0) $searchArray['brand_id'] = $brandId;
        
        //商品分页
        $page = $this->params('page',1);
        $array['goods_list'] = $this->getDbshopTable('GoodsTable')->goodsPageList(array('page'=>$page, 'page_num'=>20), $searchArray);
        
        $array['brand_id']    = $brandId;
        $array['show_div_id'] = $this->request->getQuery('show_div_id');
        
        //商品属性组
        $array['attribute_group'] = $this->getDbshopTable('GoodsAttributeGroupTable')->listAttributeGroup(array('e.language'=>$this->getDbshopLang()->getLocale()));
        
        return $viewModel->setVariables($array);
    }
    /**
     * 品牌logo删除
     */
    public function dellogoAction ()
    {
        $view = new ViewModel();
        $view->setTerminal(true);
        
        $brandId   = intval($this->request->getPost('brand_id'));
        if($brandId) {
            $brandInfo = $this->getDbshopTable()->infoBrand(array('dbshop_goods_brand.brand_id'=>$brandId,'e.language'=>$this->getDbshopLang()->getLocale()));
            @unlink(DBSHOP_PATH . $brandInfo->brand_logo);
        
            $this->getDbshopTable('GoodsBrandExtendTable')->updateBrandextend(array('brand_logo'=>'del'),array('brand_id'=>$brandId,'language'=>$this->getDbshopLang()->getLocale()));
            echo 'true';
        }
        exit;
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'GoodsBrandTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}