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

namespace Shopfront\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class BrandController extends AbstractActionController
{
    private $dbTables = array();
    private $translator;
    
    /**
     * 商品品牌首页
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction ()
    {
        $this->layout()->title_name  = $this->getDbshopLang()->translate('商品品牌');
        //统计使用
        $this->layout()->dbTongJiPage= 'brandlist';
        
        $array = array();
        //品牌列表
        $array['brand_array'] = $this->getDbshopTable('GoodsBrandTable')->listGoodsBrand();
        
        return $array;
    }
    public function brandGoodsAction ()
    {
        $array = array();
        
        $brandId = (int) $this->params('brand_id', 0);
        if($brandId <= 0) return $this->redirect()->toRoute('shopfront/default');
        
        $array['brand_info'] = $this->getDbshopTable('GoodsBrandTable')->infoBrand(array('e.brand_id'=>$brandId));
        //统计使用
        $this->layout()->dbTongJiPage = 'brandinfo';
        $this->layout()->tj_brand_name= $array['brand_info']->brand_name;
        $this->layout()->tj_brand_id  = $brandId;
        
        //商品品牌信息输出到layout
        $this->layout()->title_name         = $array['brand_info']->brand_name;
        $this->layout()->extend_title_name  = $array['brand_info']->brand_title_extend;
        $this->layout()->extend_keywords    = $array['brand_info']->brand_keywords;
        $this->layout()->extend_description = $array['brand_info']->brand_description;
        
        $searchArray = array();
        $searchWhere = array();
        $sortArray   = array();
        $sortStr     = '';
        $getArray    = $this->request->getQuery()->toArray();
        /*===========================排序检索=================================*/
        if(isset($getArray['time_sort'])  and !empty($getArray['time_sort']))  $sortArray['goods_add_time']  = $getArray['time_sort'];
        if(isset($getArray['click_sort']) and !empty($getArray['click_sort'])) $sortArray['goods_click']     = $getArray['click_sort'];
        if(isset($getArray['price_sort']) and !empty($getArray['price_sort'])) $sortArray['goods_shop_price+1']= $getArray['price_sort'];
        $sortArray       = (is_array($sortArray) and !empty($sortArray)) ? $sortArray : ((isset($getArray['sort_c']) and !empty($getArray['sort_c'])) ? unserialize(base64_decode($getArray['sort_c'])) : array());
        if(!empty($sortArray)) {
            $sortKey         = key($sortArray);
            $sortValue       = current($sortArray);
            if(in_array($sortKey, array('goods_add_time', 'goods_click', 'goods_shop_price+1')) and in_array($sortValue, array('ASC', 'DESC'))) {
                $array['sort_c'] = base64_encode(serialize($sortArray));
                $searchWhere     = array_merge($searchWhere, $sortArray);
                $sortStr         = 'dbshop_goods.' . $sortKey . ' ' . $sortValue;

                //这里之所以这样处理，是因为商品价格处使用char类型，需要+1排序才正常，下面的？语句，是为了对应模板中的排序选中状态
                $array['sort_selected'] = ($sortKey=='goods_shop_price+1' ? 'goods_shop_price' : $sortKey).$sortValue;
            }

        }
        /*===========================排序检索=================================*/
        
        //获取搜索商品列表 商品分页
        $searchWhere['goods_state']  = 1;
        $searchWhere['brand_id']     = $brandId;
        $page = $this->params('page',1);
        $array['goods_list'] = $this->getDbshopTable('GoodsTable')->searchGoods(array('page'=>$page, 'page_num'=>16), $searchWhere, $sortStr);
        
        return $array;
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName)
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
    /**
     * 语言包调用
     * @return Ambigous <object, multitype:, \Zend\I18n\Translator\Translator>
     */
    private function getDbshopLang ()
    {
        if (! $this->translator) {
            $this->translator = $this->getServiceLocator()->get('translator');
        }
        return $this->translator;
    }
}