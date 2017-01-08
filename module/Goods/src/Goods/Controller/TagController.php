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

use Admin\Controller\BaseController;
use Zend\View\Model\ViewModel;

class TagController extends BaseController
{
    /** 
     * 标签列表
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction ()
    {
        if($this->request->isPost()) {
            //接收POST数据
            $tagArray = $this->request->getPost()->toArray();
            if(is_array($tagArray['tag_id']) and !empty($tagArray['tag_id'])) {
                $tagSortArray = array();
                foreach($tagArray['tag_id'] as $value) {
                    $tagSortArray[$value] = $tagArray['tag_sort'][$value];
                }
                if(is_array($tagSortArray) and !empty($tagSortArray)) $this->getDbshopTable()->updateGoodsTagArray($tagSortArray);
            }
            $this->redirect()->toRoute('tag/default',array('action'=>'index'));
        }

        $array = array();
        $array['tag_type']  = $this->readerTagIni();
        $array['tag_array'] = $this->getDbshopTable()->listGoodsTag(array('dbshop_goods_tag.template_tag=\'\' or (dbshop_goods_tag.template_tag="" and dbshop_goods_tag.tag_type is NULL)', 'e.language'=>$this->getDbshopLang()->getLocale()), array('tag_group_sort ASC', 'dbshop_goods_tag.tag_sort ASC', 'dbshop_goods_tag.tag_id ASC'));

        return $array;
    }
    /** 
     * 添加标签
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function addAction ()
    {
        if($this->request->isPost()) {
            //接收POST数据
            $tagArray = $this->request->getPost()->toArray();

            $tagNameArray = explode("\r\n", $tagArray['tag_name']);
            if(is_array($tagNameArray) and !empty($tagNameArray)) {
                $tagArray['language'] = $this->getDbshopLang()->getLocale();
                foreach($tagNameArray as $nameValue) {
                    if(!empty($nameValue)) {
                        $tagArray['tag_name'] = $nameValue;
                        $tagId    = $this->getDbshopTable()->addGoodsTag($tagArray);
                        if($tagId) {
                            $tagArray['tag_id']   = $tagId;
                            $this->getDbshopTable('GoodsTagExtendTable')->addTagExtend($tagArray);
                            unset($tagArray['tag_id']);
                        }
                    }
                }
            }
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('普通商品标签'), 'operlog_info'=>$this->getDbshopLang()->translate('添加商品标签') . '&nbsp;' . implode(' ', $tagNameArray)));
            unset($tagArray);
            return $this->redirect()->toRoute('tag/default',array('action'=>'index'));
        }
        $array = array();
        $array['tag_group'] = $this->getDbshopTable('GoodsTagGroupTable')->listTagGroup();

        return $array;
    }
    /** 
     * 编辑标签
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:NULL
     */
    public function editAction ()
    {
        $tagId = (int) $this->params('tag_id', 0);
        if(!$tagId) {
            return $this->redirect()->toRoute('tag/default',array('action'=>'index'));
        }
        $array = array();
        if($this->request->isPost()) {
            //接收POST数据
            $tagArray    = $this->request->getPost()->toArray();
            
            $this->getDbshopTable()->updateGoodsTag($tagArray, array('tag_id'=>$tagId));
            $updateState = $this->getDbshopTable('GoodsTagExtendTable')->updateTagExtend($tagArray, array('tag_id'=>$tagId, 'language'=>$this->getDbshopLang()->getLocale()));
            //标签中的产品批量编辑
            if(isset($tagArray['goods_id']) and is_array($tagArray['goods_id']) and !empty($tagArray['goods_id']) and $tagArray['tag_goods_editall'] != '') {
                if($tagArray['tag_goods_editall'] == 'del') {//标签中的产品删除
                    $this->getDbshopTable('GoodsTagInGoodsTable')->delTagInGoods(array('goods_id IN ('.implode(',',$tagArray['goods_id']).')', 'tag_id='.$tagId));
                    //更新商品数据表中的冗余标签信息
                    foreach ($tagArray['goods_id'] as $goodsIdValue) {
                        $goodsInfo = array();
                        $goodsInfo = $this->getDbshopTable('GoodsTable')->oneGoodsInfo(array('goods_id'=>$goodsIdValue));
                        if(isset($goodsInfo->goods_id)) {
                            $goodsTagStr = str_replace(','.$tagId, '', $goodsInfo->goods_tag_str);
                            $this->getDbshopTable('GoodsTable')->oneUpdateGoods(array('goods_tag_str'=>$goodsTagStr), array('goods_id'=>$goodsIdValue));
                        }
                    }                    
                } elseif ($tagArray['tag_goods_editall'] == 'update') {//标签中的产品更新
                    foreach ($tagArray['goods_id'] as $tagGoodsId) {
                        $this->getDbshopTable('GoodsTagInGoodsTable')->updateTagInGoods(array('tag_goods_sort'=>intval($tagArray['tag_goods_sort'][$tagGoodsId])), array('tag_id'=>$tagId, 'goods_id'=>$tagGoodsId));
                    }
                }
            }
            
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('普通商品标签'), 'operlog_info'=>$this->getDbshopLang()->translate('更新商品标签') . '&nbsp;' . $tagArray['tag_name']));
            
            unset($tagArray);
            return $this->redirect()->toRoute('tag/default',array('action'=>'index'));
        }
        //标签类型
        $array['tag_type']  = $this->readerTagIni();
        //标签信息
        $array['tag_info'] = $this->getDbshopTable('GoodsTagExtendTable')->infoTagExtend(array('dbshop_goods_tag_extend.tag_id'=>$tagId,'dbshop_goods_tag_extend.language'=>$this->getDbshopLang()->getLocale()));
        //标签分组
        $array['tag_group'] = $this->getDbshopTable('GoodsTagGroupTable')->listTagGroup();
        
        return $array;
    }
    /** 
     * 删除标签
     */
    public function delAction ()
    {
        $tagId   = intval($this->request->getPost('tag_id'));
        if($tagId) {
            //为了记录操作日志使用
            $tagInfo = $this->getDbshopTable('GoodsTagExtendTable')->infoTagExtend(array('dbshop_goods_tag_extend.tag_id'=>$tagId,'dbshop_goods_tag_extend.language'=>$this->getDbshopLang()->getLocale()));
            
            if($this->getDbshopTable()->delGoodsTag(array('tag_id'=>$tagId))) {
                $this->getDbshopTable('GoodsTagExtendTable')->delTagExtend(array('tag_id'=>$tagId));
                $this->getDbshopTable('GoodsTagInGoodsTable')->delTagInGoods(array('tag_id'=>$tagId));
                
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('普通商品标签'), 'operlog_info'=>$this->getDbshopLang()->translate('删除商品标签') . '&nbsp;' . $tagInfo->tag_name));
               
                echo 'true';
            }
        }
        exit();
    }
    /**
     * 特定商品标签列表
     * @return array
     */
    public function specTagAction()
    {
        $array = array();
        $array['tag_type']  = $this->readerTagIni();
        $where = "(dbshop_goods_tag.template_tag='" . DBSHOP_TEMPLATE . "' and dbshop_goods_tag.show_type='pc') or (dbshop_goods_tag.template_tag='" . DBSHOP_PHONE_TEMPLATE . "' and dbshop_goods_tag.show_type='phone')";
        $array['tag_array'] = $this->getDbshopTable()->listGoodsTag(array($where, 'e.language'=>$this->getDbshopLang()->getLocale()), array('dbshop_goods_tag.tag_id ASC'));

        return $array;
    }
    /**
     * 添加特定商品标签
     * @return array|\Zend\Http\Response
     */
    public function addSpecTagAction()
    {
        if($this->request->isPost()) {
            //接收POST数据
            $tagArray = $this->request->getPost()->toArray();
            if(strpos($tagArray['tag_type'], 'phone_') === false) {
                $tagArray['template_tag'] = DBSHOP_TEMPLATE;
            } else {
                $tagArray['template_tag'] = DBSHOP_PHONE_TEMPLATE;
                $tagArray['show_type']    = 'phone';
            }

            $tagId    = $this->getDbshopTable()->addGoodsTag($tagArray);
            if($tagId) {
                $tagArray['tag_id']   = $tagId;
                $tagArray['language'] = $this->getDbshopLang()->getLocale();
                $this->getDbshopTable('GoodsTagExtendTable')->addTagExtend($tagArray);

                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('特殊商品标签'), 'operlog_info'=>$this->getDbshopLang()->translate('添加商品标签') . '&nbsp;' . $tagArray['tag_name']));

                unset($tagArray);
                return $this->redirect()->toRoute('tag/default',array('action'=>'specTag'));
            }
        }

        $array = array();
        $array['tag_type']  = $this->readerTagIni();
        //去掉已经添加了特定标签
        $where = "(dbshop_goods_tag.template_tag='" . DBSHOP_TEMPLATE . "' and dbshop_goods_tag.show_type='pc') or (dbshop_goods_tag.template_tag='" . DBSHOP_PHONE_TEMPLATE . "' and dbshop_goods_tag.show_type='phone')";
        $goodsTagArray      = $this->getDbshopTable()->goodsTagArray(array($where));
        if(!empty($goodsTagArray)) {
            foreach($goodsTagArray as $value) {
                if(isset($array['tag_type'][$value['tag_type']])) unset($array['tag_type'][$value['tag_type']]);
            }
        }

        return $array;
    }
    /**
     * 编辑特定商品标签
     * @return array|\Zend\Http\Response
     */
    public function editSpecTagAction()
    {
        $tagId = (int) $this->params('tag_id', 0);
        if(!$tagId) {
            return $this->redirect()->toRoute('tag/default',array('action'=>'specTag'));
        }
        $array = array();
        if($this->request->isPost()) {
            //接收POST数据
            $tagArray    = $this->request->getPost()->toArray();
            if(strpos($tagArray['tag_type'], 'phone_') === false) {
                $tagArray['template_tag'] = DBSHOP_TEMPLATE;
            } else {
                $tagArray['template_tag'] = DBSHOP_PHONE_TEMPLATE;
            }

            $this->getDbshopTable()->updateGoodsTag($tagArray, array('tag_id'=>$tagId));
            $updateState = $this->getDbshopTable('GoodsTagExtendTable')->updateTagExtend($tagArray, array('tag_id'=>$tagId, 'language'=>$this->getDbshopLang()->getLocale()));
            //标签中的产品批量编辑
            if(isset($tagArray['goods_id']) and is_array($tagArray['goods_id']) and !empty($tagArray['goods_id']) and $tagArray['tag_goods_editall'] != '') {
                if($tagArray['tag_goods_editall'] == 'del') {//标签中的产品删除
                    $this->getDbshopTable('GoodsTagInGoodsTable')->delTagInGoods(array('goods_id IN ('.implode(',',$tagArray['goods_id']).')', 'tag_id='.$tagId));
                    //更新商品数据表中的冗余标签信息
                    foreach ($tagArray['goods_id'] as $goodsIdValue) {
                        $goodsInfo = array();
                        $goodsInfo = $this->getDbshopTable('GoodsTable')->oneGoodsInfo(array('goods_id'=>$goodsIdValue));
                        if(isset($goodsInfo->goods_id)) {
                            $goodsTagStr = str_replace(','.$tagId, '', $goodsInfo->goods_tag_str);
                            $this->getDbshopTable('GoodsTable')->oneUpdateGoods(array('goods_tag_str'=>$goodsTagStr), array('goods_id'=>$goodsIdValue));
                        }
                    }
                } elseif ($tagArray['tag_goods_editall'] == 'update') {//标签中的产品更新
                    foreach ($tagArray['goods_id'] as $tagGoodsId) {
                        $this->getDbshopTable('GoodsTagInGoodsTable')->updateTagInGoods(array('tag_goods_sort'=>intval($tagArray['tag_goods_sort'][$tagGoodsId])), array('tag_id'=>$tagId, 'goods_id'=>$tagGoodsId));
                    }
                }
            }

            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('特定商品标签'), 'operlog_info'=>$this->getDbshopLang()->translate('更新商品标签') . '&nbsp;' . $tagArray['tag_name']));

            unset($tagArray);
            return $this->redirect()->toRoute('tag/default',array('action'=>'specTag'));
        }
        //标签类型
        $array['tag_type']  = $this->readerTagIni();
        //标签信息
        $array['tag_info'] = $this->getDbshopTable('GoodsTagExtendTable')->infoTagExtend(array('dbshop_goods_tag_extend.tag_id'=>$tagId,'dbshop_goods_tag_extend.language'=>$this->getDbshopLang()->getLocale()));
        //去掉已经添加了特定标签,保留当前类型
        $where = "(dbshop_goods_tag.template_tag='" . DBSHOP_TEMPLATE . "' and dbshop_goods_tag.show_type='pc') or (dbshop_goods_tag.template_tag='" . DBSHOP_PHONE_TEMPLATE . "' and dbshop_goods_tag.show_type='phone')";
        $goodsTagArray      = $this->getDbshopTable()->goodsTagArray(array($where));
        if(!empty($goodsTagArray)) {
            foreach($goodsTagArray as $value) {
                if(isset($array['tag_type'][$value['tag_type']]) and $value['tag_type'] != $array['tag_info']->tag_type) unset($array['tag_type'][$value['tag_type']]);
            }
        }

        return $array;
    }
    /**
     * 删除特定商品标签
     */
    public function delSpecTagAction()
    {
        $tagId   = intval($this->request->getPost('tag_id'));
        if($tagId) {
            //为了记录操作日志使用
            $tagInfo = $this->getDbshopTable('GoodsTagExtendTable')->infoTagExtend(array('dbshop_goods_tag_extend.tag_id'=>$tagId,'dbshop_goods_tag_extend.language'=>$this->getDbshopLang()->getLocale()));

            if($this->getDbshopTable()->delGoodsTag(array('tag_id'=>$tagId))) {
                $this->getDbshopTable('GoodsTagExtendTable')->delTagExtend(array('tag_id'=>$tagId));
                $this->getDbshopTable('GoodsTagInGoodsTable')->delTagInGoods(array('tag_id'=>$tagId));

                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('特定商品标签'), 'operlog_info'=>$this->getDbshopLang()->translate('删除商品标签') . '&nbsp;' . $tagInfo->tag_name));

                echo 'true';
            }
        }
        exit();
    }
    //对于标签编辑里的，标签商品进行单次删除
    public function delOneTagGoodsAction()
    {
        if($this->request->isPost()) {
            $goodsId = $this->request->getPost('goods_id');
            $tagId   = $this->request->getPost('tag_id');
            if($goodsId != 0 and $tagId != 0) {
                $this->getDbshopTable('GoodsTagInGoodsTable')->delTagInGoods(array('goods_id'=>$goodsId, 'tag_id'=>$tagId));
                echo 'true';
            }
        }
        exit();
    }
    /**
     * ajax获取标签对应的商品
     * @return Ambigous <\Zend\View\Model\ViewModel, \Zend\View\Model\ViewModel>
     */
    public function ajaxgoodsAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
    
        $searchArray = array();
        $tagId     = (int) $this->params('tag_id', 0);
        if($tagId != 0) $searchArray['tag_id'] = $tagId;
    
        //商品分页
        $page = $this->params('page',1);
        $array['goods_list'] = $this->getDbshopTable('GoodsTable')->goodsPageList(array('page'=>$page, 'page_num'=>20), $searchArray,array('goods_tag_in_goods'=>true), array('goods_tag_in.tag_goods_sort ASC', 'goods_tag_in.goods_id DESC'));
        
        $array['tag_id']      = $tagId;
        $array['show_div_id'] = $this->request->getQuery('show_div_id');
        
        //商品属性组
        $array['attribute_group'] = $this->getDbshopTable('GoodsAttributeGroupTable')->listAttributeGroup(array('e.language'=>$this->getDbshopLang()->getLocale()));
        
        return $viewModel->setVariables($array);
    }
    /**
     * 单独添加标签商品
     */
    public function addTagGoodsAction()
    {
        $tagId        = intval($this->request->getPost('tag_id'));
        $tagGoodsId   = intval($this->request->getPost('tag_goods_id'));

        if($tagId != 0 and $tagGoodsId != 0) {
            $tagGoodsInfo  = $this->getDbshopTable('GoodsTagInGoodsTable')->getInGoodsTag(array('tag_id'=>$tagId,'goods_id'=>$tagGoodsId));
            if(!empty($tagGoodsInfo)) exit(json_encode(array('state'=>'have')));

            $tagGoodsArray = array();
            $tagGoodsArray['tag_id']          = $tagId;
            $tagGoodsArray['goods_id']        = $tagGoodsId;
            $tagGoodsArray['tag_goods_sort']  = 255;
            $this->getDbshopTable('GoodsTagInGoodsTable')->addOneTagInGoods($tagGoodsArray);

            exit(json_encode(array('state'=>'true')));
        }
        exit(json_encode(array('state'=>'false')));
    }
    /** 
     * 标签组列表
     */
    public function tagGroupAction()
    {
        $array = array();
        
        $array['tag_group'] = $this->getDbshopTable('GoodsTagGroupTable')->listTagGroup();
        
        return $array;
    }
    /** 
     * 添加标签组
     */
    public function addTagGroupAction()
    {
        if($this->request->isPost()) {
            $tagGroupArray  = $this->request->getPost()->toArray();
            $tagGroupId     = $this->getDbshopTable('GoodsTagGroupTable')->addTagGroup($tagGroupArray);
            if($tagGroupId) {
                $tagGroupArray['tag_group_id']   = $tagGroupId;
                $tagGroupArray['language']       = $this->getDbshopLang()->getLocale();
                $this->getDbshopTable('GoodsTagGroupExtendTable')->addTagGroupExtend($tagGroupArray);
                
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('商品标签组'), 'operlog_info'=>$this->getDbshopLang()->translate('添加商品标签组') . '&nbsp;' . $tagGroupArray['tag_group_name']));
                 
                unset($tagGroupArray);
                
                return $this->redirect()->toRoute('tag/default',array('action'=>'tagGroup'));
            }
        }
    }
    /** 
     * 编辑标签组
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:unknown
     */
    public function editTagGroupAction()
    {
        $array = array();
        $tagGroupId   = $this->params('tag_group_id', 0);
        if($this->request->isPost()) {
            $tagGroupArray  = $this->request->getPost()->toArray();
            if($this->getDbshopTable('GoodsTagGroupTable')->editTagGroup($tagGroupArray, array('tag_group_id'=>$tagGroupId))) {
                
            	$tagGroupArray['tag_group_id']   = $tagGroupId;
                $tagGroupArray['language']       = $this->getDbshopLang()->getLocale();
                $this->getDbshopTable('GoodsTagGroupExtendTable')->editTagGroupExtend($tagGroupArray, array('tag_group_id'=>$tagGroupId, 'language'=>$tagGroupArray['language']));

                if(is_array($tagGroupArray['tag_sort']) and !empty($tagGroupArray['tag_sort'])) $this->getDbshopTable()->updateGoodsTagArray($tagGroupArray['tag_sort']);

                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('商品标签组'), 'operlog_info'=>$this->getDbshopLang()->translate('更新商品标签组') . '&nbsp;' . $tagGroupArray['tag_group_name']));
                //跳转
                if($tagGroupArray['tag_group_save_type'] != 'save_return_edit') {
                    return $this->redirect()->toRoute('tag/default',array('action'=>'tagGroup'));
                }

                unset($tagGroupArray);
                $array['success_msg'] = $this->getDbshopLang()->translate('标签组编辑成功！');
            }
        }
        
        $tagGroupInfo = $this->getDbshopTable('GoodsTagGroupTable')->infoTagGroup(array('dbshop_goods_tag_group.tag_group_id'=>$tagGroupId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        if(!$tagGroupInfo) return $this->redirect()->toRoute('tag/default',array('action'=>'tagGroup'));
        
        $array['group_info'] = $tagGroupInfo[0];

        $tagList = $this->getDbshopTable()->listGoodsTag(array('dbshop_goods_tag.tag_group_id'=>$tagGroupId, 'e.language'=>$this->getDbshopLang()->getLocale()), array('dbshop_goods_tag.tag_sort ASC'));
        $array['tag_list'] = $tagList;

        return $array;
    }
    /** 
     * 删除标签组
     */
    public function delTagGroupAction()
    {
        $state = 'false';
        
        $tagGroupId   = intval($this->request->getPost('tag_group_id'));
        $tagArray     = $this->getDbshopTable()->listGoodsTag(array('dbshop_goods_tag.tag_group_id'=>$tagGroupId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        if($tagGroupId and empty($tagArray)) {
            //为了记录操作日志使用
            $tagGroupInfo = $this->getDbshopTable('GoodsTagGroupTable')->infoTagGroup(array('dbshop_goods_tag_group.tag_group_id'=>$tagGroupId, 'e.language'=>$this->getDbshopLang()->getLocale()));
            
            if($this->getDbshopTable('GoodsTagGroupTable')->delTagGroup(array('tag_group_id'=>$tagGroupId))) {
                $this->getDbshopTable('GoodsTagGroupExtendTable')->delTagGroupExtend(array('tag_group_id'=>$tagGroupId));
                
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('商品标签组'), 'operlog_info'=>$this->getDbshopLang()->translate('删除商品标签组') . '&nbsp;' . $tagGroupInfo[0]['tag_group_name']));
                
                //下面还有标签删除，标签商品删除，这里暂时不写
                $state = 'true';
            }
        }
        exit($state);
    }
    /**
     * 批量修改标签组信息
     * @return \Zend\Http\Response
     */
    public function allTagGroupUpdateAction()
    {
        if($this->request->isPost()) {
            $array  = $this->request->getPost()->toArray();
            if(is_array($array) and !empty($array)) {
                foreach($array['tag_group_sort'] as $key => $value) {
                    $this->getDbshopTable('GoodsTagGroupTable')->allTagGroupUpdate(array('tag_group_sort'=>$value), array('tag_group_id'=>$key));
                }
            }
        }
        //跳转处理
        return $this->redirect()->toRoute('tag/default',array('action'=>'tagGroup'));
    }
    /**
     * 获取模板对于特殊设置的信息
     * @return Ambigous <multitype:>
     */
    private function readerTagIni()
    {
        $adIni       = array();
        $adIniReader = new \Zend\Config\Reader\Ini();
        $adIni       = $adIniReader->fromFile(DBSHOP_PATH . '/module/Shopfront/view/' . DBSHOP_TEMPLATE . '/shopfront/template.ini');
        //手机模板的tag
        if(defined('DBSHOP_PHONE_TEMPLATE') and DBSHOP_PHONE_TEMPLATE != '') {
            $phoneIni = $adIniReader->fromFile(DBSHOP_PATH . '/module/Mobile/view/' . DBSHOP_PHONE_TEMPLATE . '/mobile/template.ini');
            if(isset($phoneIni['tag_type']) and !empty($phoneIni['tag_type'])) $adIni['tag_type'] = array_merge($adIni['tag_type'], $phoneIni['tag_type']);
        }

        return (isset($adIni['tag_type']) ? $adIni['tag_type'] : null);
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'GoodsTagTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}

?>