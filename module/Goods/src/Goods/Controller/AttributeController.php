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

class AttributeController extends BaseController
{
    /** 
     * 商品属性列表
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $array = array();
        
        $array['list_attribute'] = $this->getDbshopTable('GoodsAttributeTable')->listAttribute(array('e.language'=>$this->getDbshopLang()->getLocale()));
        $array['input_array']    = $this->getInputArray();

        return $array;
    }
    /** 
     * 添加商品属性
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:NULL
     */
    public function addAction()
    {
        if($this->request->isPost()) {
            $attributeArray = $this->request->getPost()->toArray();
            $attributeId    = $this->getDbshopTable('GoodsAttributeTable')->addAttribute($attributeArray);
            if($attributeId) {
                $attributeExtendArray = array();
                $attributeExtendArray['attribute_id']   = $attributeId;
                $attributeExtendArray['attribute_name'] = $attributeArray['attribute_name'];
                $attributeExtendArray['language']       = $this->getDbshopLang()->getLocale();
                $this->getDbshopTable('GoodsAttributeExtendTable')->addAttributeExtend($attributeExtendArray);
            }
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('商品属性'), 'operlog_info'=>$this->getDbshopLang()->translate('添加商品属性') . '&nbsp;' . $attributeArray['attribute_name']));
            
            unset($attributeArray, $attributeExtendArray);
            return $this->redirect()->toRoute('attribute/default');
        }
        
        $array = array();
        
        $array['attribute_group'] = $this->getDbshopTable('GoodsAttributeGroupTable')->listAttributeGroup(array('e.language'=>$this->getDbshopLang()->getLocale()));
        
        $array['input_array']     = $this->getInputArray();
        
        return $array;
    }
    /** 
     * 属性编辑
     * @return multitype:NULL multitype:NULL Ambigous <string, string, NULL, multitype:NULL , multitype:string NULL >
     */
    public function editAction()
    {
        $attributeId = $this->params('attribute_id', 0);
        if(!$attributeId) return $this->redirect()->toRoute('attribute/default');
        if($this->request->isPost()) {
            $attributeArray = $this->request->getPost()->toArray();
            if($this->getDbshopTable('GoodsAttributeTable')->updateAttribute($attributeArray, array('attribute_id'=>$attributeId))) {
                $attributeExtendArray = array();
                $attributeExtendArray['attribute_id']   = $attributeId;
                $attributeExtendArray['attribute_name'] = $attributeArray['attribute_name'];
                $attributeExtendArray['language']       = $this->getDbshopLang()->getLocale();
                $this->getDbshopTable('GoodsAttributeExtendTable')->updateAttributeExtend($attributeExtendArray, array('attribute_id'=>$attributeId, 'language'=>$this->getDbshopLang()->getLocale()));
            }
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('商品属性'), 'operlog_info'=>$this->getDbshopLang()->translate('更新商品属性') . '&nbsp;' . $attributeArray['attribute_name']));
            
            unset($attributeArray, $attributeExtendArray);
            return $this->redirect()->toRoute('attribute/default');
        }
        
        $array = array();
        
        $array['attribute_info']  = $this->getDbshopTable('GoodsAttributeTable')->infoAttribute(array('e.attribute_id'=>$attributeId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        
        $array['attribute_group'] = $this->getDbshopTable('GoodsAttributeGroupTable')->listAttributeGroup(array('e.language'=>$this->getDbshopLang()->getLocale()));
        
        $array['input_array']     = $this->getInputArray();
        
        return $array;       
    }
    /** 
     * 删除属性
     */
    public function delAction ()
    {
        $attributeId   = intval($this->request->getPost('attribute_id'));
        if($attributeId) {
            //为了记录操作日志使用
            $attributeInfo = $this->getDbshopTable('GoodsAttributeTable')->infoAttribute(array('e.attribute_id'=>$attributeId, 'e.language'=>$this->getDbshopLang()->getLocale()));
            
            $where = array('attribute_id'=>$attributeId);
            $this->getDbshopTable('GoodsAttributeTable')->delAttribute($where);
            $this->getDbshopTable('GoodsAttributeExtendTable')->delAttributeExtend($where);
            $this->getDbshopTable('GoodsAttributeValueTable')->delAttributeValue($where);
            $this->getDbshopTable('GoodsAttributeValueExtendTable')->delAttributeValueExtend($where);
            
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('商品属性'), 'operlog_info'=>$this->getDbshopLang()->translate('删除商品属性') . '&nbsp;' . $attributeInfo->attribute_name));
            
            echo 'true';
        }
        exit();
    }
    /**
     * 批量修改属性信息
     * @return \Zend\Http\Response
     */
    public function allAttributeUpdateAction()
    {
        if($this->request->isPost()) {
            $array  = $this->request->getPost()->toArray();
            if(is_array($array) and !empty($array)) {
                foreach($array['attribute_sort'] as $key => $value) {
                    $this->getDbshopTable('GoodsAttributeTable')->updateGoodsAttribute(array('attribute_sort'=>$value), array('attribute_id'=>$key));
                }
            }
        }
        //跳转处理
        return $this->redirect()->toRoute('attribute/default');
    }
    /**
     * 属性值列表
     */
    public function attributeValueAction ()
    {
        $attributeId = $this->params('attribute_id', 0);
        if(!$attributeId) return $this->redirect()->toRoute('attribute/default');
        $array = array();
        
        $array['attribute_info']        = $this->getDbshopTable('GoodsAttributeTable')->infoAttribute(array('e.attribute_id'=>$attributeId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        
        $array['attribute_value_list']  = $this->getDbshopTable('GoodsAttributeValueTable')->listAttributeValue(array('dbshop_goods_attribute_value.attribute_id'=>$attributeId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        
        return $array;
    }
    /** 
     * 添加属性值
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function addAttributeValueAction ()
    {
        $attributeId = $this->params('attribute_id', 0);
        if(!$attributeId) return $this->redirect()->toRoute('attribute/default');
        
        if($this->request->isPost()) {//添加属性值
            $attributeValueArray = $this->request->getPost()->toArray();
            $attributeValueArray['attribute_id'] = $attributeId;
            $attributeValueId    = $this->getDbshopTable('GoodsAttributeValueTable')->addAttributeValue($attributeValueArray);
            if($attributeValueId) {
                $valueExtendArray = array();
                $valueExtendArray['value_id']     = $attributeValueId;
                $valueExtendArray['value_name']   = $attributeValueArray['value_name'];
                $valueExtendArray['attribute_id'] = $attributeId;
                $valueExtendArray['language']     = $this->getDbshopLang()->getLocale();
                $this->getDbshopTable('GoodsAttributeValueExtendTable')->addAttributeValueExtend($valueExtendArray);
            }
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('商品属性值'), 'operlog_info'=>$this->getDbshopLang()->translate('添加商品属性值') . '&nbsp;' . $attributeValueArray['value_name']));
        
            unset($attributeValueArray, $valueExtendArray);
        }
        return $this->redirect()->toRoute('attribute/default/attribute-id', array('action'=>'attributeValue', 'attribute_id'=>$attributeId));
    }
    /** 
     * 编辑属性值
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:NULL
     */
    public function editAttributeValueAction ()
    {
        $attributeId = $this->params('attribute_id', 0);
        if(!$attributeId) return $this->redirect()->toRoute('attribute/default');
        
        $valueId = $this->params('value_id', 0);
        if(!$valueId) return $this->redirect()->toRoute('attribute/default/attribute-id',array('action'=>'attributeValue','attribute_id'=>$attributeId));
        
        if($this->request->isPost()) {//编辑属性值
            $attributeValueArray = $this->request->getPost()->toArray();
            $attributeValueArray['attribute_id'] = $attributeId;
            $attributeValueId    = $this->getDbshopTable('GoodsAttributeValueTable')->updateAttributeValue($attributeValueArray, array('value_id'=>$valueId));
            if($attributeValueId) {
                $valueExtendArray = array();
                $valueExtendArray['value_id']     = $attributeValueId;
                $valueExtendArray['value_name']   = $attributeValueArray['value_name'];
                $valueExtendArray['attribute_id'] = $attributeId;
                $valueExtendArray['language']     = $this->getDbshopLang()->getLocale();
                $this->getDbshopTable('GoodsAttributeValueExtendTable')->updateAttributeValueExtend($valueExtendArray, array('value_id'=>$valueId, 'language'=>$this->getDbshopLang()->getLocale()));
            }
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('商品属性值'), 'operlog_info'=>$this->getDbshopLang()->translate('更新商品属性值') . '&nbsp;' . $attributeValueArray['value_name']));
            
            unset($attributeValueArray, $valueExtendArray);
            return $this->redirect()->toRoute('attribute/default/attribute-id', array('action'=>'attributeValue', 'attribute_id'=>$attributeId));
        }
        
        $array = array();
        $array['attribute_info']        = $this->getDbshopTable('GoodsAttributeTable')->infoAttribute(array('e.attribute_id'=>$attributeId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        $array['attribute_value_list']  = $this->getDbshopTable('GoodsAttributeValueTable')->listAttributeValue(array('dbshop_goods_attribute_value.attribute_id'=>$attributeId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        $array['value_info']            = $this->getDbshopTable('GoodsAttributeValueTable')->infoAttributeValue(array('dbshop_goods_attribute_value.value_id'=>$valueId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        
        return $array;
    }
    /** 
     * 属性值删除
     */
    public function delAttributeValueAction()
    {
        $valueId   = intval($this->request->getPost('value_id'));
        if($valueId) {
            //为了记录操作日志使用
            $valueInfo = $this->getDbshopTable('GoodsAttributeValueTable')->infoAttributeValue(array('dbshop_goods_attribute_value.value_id'=>$valueId, 'e.language'=>$this->getDbshopLang()->getLocale()));
            
            $where = array('value_id'=>$valueId);
            $this->getDbshopTable('GoodsAttributeValueTable')->delAttributeValue($where);
            $this->getDbshopTable('GoodsAttributeValueExtendTable')->delAttributeValueExtend($where);
            
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('商品属性值'), 'operlog_info'=>$this->getDbshopLang()->translate('删除商品属性值') . '&nbsp;' . $valueInfo->value_name));
            
            echo 'true';
        }
        exit();
    }
    /**
     * 批量修改属性值信息
     * @return \Zend\Http\Response
     */
    public function allAttributeValueUpdateAction()
    {
        if($this->request->isPost()) {
            $array  = $this->request->getPost()->toArray();
            if(is_array($array) and !empty($array)) {
                foreach($array['value_sort'] as $key => $value) {
                    $this->getDbshopTable('GoodsAttributeValueTable')->allUpdateGoodsAttributeValue(array('value_sort'=>$value), array('value_id'=>$key));
                }
                return $this->redirect()->toRoute('attribute/default/attribute-id', array('action'=>'attributeValue', 'attribute_id'=>$Array['attribute_id']));
            }
        }
        //跳转处理
        return $this->redirect()->toRoute('attribute/default',array('action'=>'index'));
    }
    /** 
     * 商品属性组列表
     * @return multitype:NULL
     */
    public function attributeGroupAction()
    {
        $array = array();
        
        $array['attribute_group'] = $this->getDbshopTable('GoodsAttributeGroupTable')->listAttributeGroup(array('e.language'=>$this->getDbshopLang()->getLocale()));

        return $array;
    }
    /**
     * 添加商品属性组
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function addAttributeGroupAction ()
    {
        if($this->request->isPost()) {
            $attributeGroupArray  = $this->request->getPost()->toArray();
            $attributeGroupId     = $this->getDbshopTable('GoodsAttributeGroupTable')->addAttributeGroup($attributeGroupArray);
            if($attributeGroupId) {
                $attributeGroupExtendArray = array();
                $attributeGroupExtendArray['attribute_group_id']   = $attributeGroupId;
                $attributeGroupExtendArray['attribute_group_name'] = $attributeGroupArray['attribute_group_name'];
                $attributeGroupExtendArray['language']             = $this->getDbshopLang()->getLocale();
                
                $this->getDbshopTable('GoodsAttributeGroupExtendTable')->addAttributeGroupExtend($attributeGroupExtendArray);
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('商品属性组'), 'operlog_info'=>$this->getDbshopLang()->translate('添加属性组') . '&nbsp;' . $attributeGroupArray['attribute_group_name']));
                
                unset($attributeGroupArray, $attributeGroupExtendArray);
                return $this->redirect()->toRoute('attribute/default',array('action'=>'attributeGroup'));
            }
        }
    }
    /** 
     * 编辑属性组
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:NULL
     */
    public function editAttributeGroupAction ()
    {
        $array = array();
        $attributeGroupId = $this->params('attribute_group_id', 0);
        if($attributeGroupId == 0) return $this->redirect()->toRoute('attribute/default',array('action'=>'attributeGroup'));
        
        if($this->request->isPost()) {
            $attributeGroupArray  = $this->request->getPost()->toArray();
            if($this->getDbshopTable('GoodsAttributeGroupTable')->editAttributeGroup($attributeGroupArray, array('attribute_group_id'=>$attributeGroupId))) {
                $attributeGroupExtendArray = array();
                $attributeGroupExtendArray['attribute_group_id']   = $attributeGroupId;
                $attributeGroupExtendArray['attribute_group_name'] = $attributeGroupArray['attribute_group_name'];
                $attributeGroupExtendArray['language']             = $this->getDbshopLang()->getLocale();

                $this->getDbshopTable('GoodsAttributeGroupExtendTable')->editAttributeGroupExtend($attributeGroupExtendArray, array('attribute_group_id'=>$attributeGroupId, 'language'=>$attributeGroupExtendArray['language']));
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('商品属性组'), 'operlog_info'=>$this->getDbshopLang()->translate('更新属性组') . '&nbsp;' . $attributeGroupArray['attribute_group_name']));
                
                unset($attributeGroupArray, $attributeGroupExtendArray);
                return $this->redirect()->toRoute('attribute/default',array('action'=>'attributeGroup'));
            }
        }
        
        $array['group_info'] = $this->getDbshopTable('GoodsAttributeGroupTable')->infoAttributeGroup(array('dbshop_goods_attribute_group.attribute_group_id'=>$attributeGroupId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        
        return $array;
    }
    /**  
     * 删除属性组
     */
    public function delAttributeGroupAction()
    {
        $state = 'false';
        
        $attributeGroupId   = intval($this->request->getPost('attribute_group_id'));
        $attributeArray     = $this->getDbshopTable('GoodsAttributeTable')->listAttribute(array('dbshop_goods_attribute.attribute_group_id'=>$attributeGroupId));
        
        if($attributeGroupId and empty($attributeArray)) {
            //为了记录操作日志使用
            $attributeGroupInfo = $this->getDbshopTable('GoodsAttributeGroupTable')->infoAttributeGroup(array('dbshop_goods_attribute_group.attribute_group_id'=>$attributeGroupId, 'e.language'=>$this->getDbshopLang()->getLocale()));
            
            if($this->getDbshopTable('GoodsAttributeGroupTable')->delAttributeGroup(array('attribute_group_id'=>$attributeGroupId))) {
                $this->getDbshopTable('GoodsAttributeGroupExtendTable')->delAttributeGroupExtend(array('attribute_group_id'=>$attributeGroupId));
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('商品属性组'), 'operlog_info'=>$this->getDbshopLang()->translate('删除属性组') . '&nbsp;' . $attributeGroupInfo->attribute_group_name));
                
                $state = 'true';
            }
        }
        exit($state);
    }
    /**
     * 批量修改属性组信息
     * @return \Zend\Http\Response
     */
    public function allAttributeGroupUpdateAction()
    {
        if($this->request->isPost()) {
            $Array  = $this->request->getPost()->toArray();
            if(is_array($Array) and !empty($Array)) {
                foreach($Array['attribute_group_sort'] as $key => $value) {
                    $this->getDbshopTable('GoodsAttributeGroupTable')->updateGoodsAttributeGroup(array('attribute_group_sort'=>$value), array('attribute_group_id'=>$key));
                }
            }
        }
        //跳转处理
        return $this->redirect()->toRoute('attribute/default',array('action'=>'attributeGroup'));
    }

    private function getInputArray()
    {
        $array = array();
        $array['select']   = $this->getDbshopLang()->translate('下拉');
        $array['radio']    = $this->getDbshopLang()->translate('单选');
        $array['checkbox'] = $this->getDbshopLang()->translate('多选');
        $array['input']    = $this->getDbshopLang()->translate('单行表单');
        $array['textarea'] = $this->getDbshopLang()->translate('文本域');
        
        return $array;
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

?>