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
/**
 * 
 * 优惠促销规则
 *
 */
class PromotionsController extends BaseController
{
    public function indexAction()
    {
        $array = array();
        
        $array['promotions_rule_list'] = $this->getDbshopTable()->listPromotionsRule();

        return $array;
    }
    /** 
     * 添加规则
     * @return multitype:NULL
     */
    public function addAction()
    {
        $array = array();
        
        if($this->request->isPost()) {
            $promotionsArray = $this->request->getPost()->toArray();
            if(isset($promotionsArray['promotions_user_group']) and !empty($promotionsArray['promotions_user_group']) and $promotionsArray['promotions_user_type'] == 'user_group') {
                $promotionsArray['promotions_user_group'] = serialize($promotionsArray['promotions_user_group']);
            } else {
                $promotionsArray['promotions_user_group'] = '';
            }
            if($promotionsArray['promotions_goods_type'] == 'class_goods' and !empty($promotionsArray['class_id'])) {
                $promotionsArray['promotions_goods_content'] = serialize($promotionsArray['class_id']);
            } elseif($promotionsArray['promotions_goods_type'] == 'brand_goods' and !empty($promotionsArray['brand_id'])) {
                $promotionsArray['promotions_goods_content'] = serialize($promotionsArray['brand_id']);
            } else {
                $promotionsArray['promotions_goods_content'] = '';
            }
            
            $this->getDbshopTable()->addPromotionsRule($promotionsArray);
            $this->createPromotionsRuleIni();
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('优惠规则'), 'operlog_info'=>$this->getDbshopLang()->translate('添加商品优惠规则') . '&nbsp;' . $promotionsArray['promotions_name']));
            
            unset($promotionsArray);
            return $this->redirect()->toRoute('promotions/default');
        }
        
        //会员分组
        $array['group_array'] = $this->getDbshopTable('UserGroupTable')->listUserGroup(array('language'=>$this->getDbshopLang()->getLocale()));
        //商品分类
        $array['goods_class']  = $this->getDbshopTable('GoodsClassTable')->classOptions(0,$this->getDbshopTable('GoodsClassTable')->listGoodsClass());
        //商品品牌
        $array['goods_brand'] = $this->getDbshopTable('GoodsBrandTable')->listGoodsBrand();
        
        return $array;
    }
    /** 
     * 编辑规则
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:NULL Ambigous <string, string, NULL, boolean, \Zend\EventManager\mixed, mixed>
     */
    public function editAction()
    {
        $promotionsId = (int) $this->params('promotions_id', 0);
        if($promotionsId == 0) return $this->redirect()->toRoute('promotions/default');
        
        $array = array();
        
        if($this->request->isPost()) {
            $promotionsArray = $this->request->getPost()->toArray();
            if(isset($promotionsArray['promotions_user_group']) and !empty($promotionsArray['promotions_user_group']) and $promotionsArray['promotions_user_type'] == 'user_group') {
                $promotionsArray['promotions_user_group'] = serialize($promotionsArray['promotions_user_group']);
            } else {
                $promotionsArray['promotions_user_group'] = '';
            }
            if($promotionsArray['promotions_goods_type'] == 'class_goods' and !empty($promotionsArray['class_id'])) {
                $promotionsArray['promotions_goods_content'] = serialize($promotionsArray['class_id']);
            } elseif($promotionsArray['promotions_goods_type'] == 'brand_goods' and !empty($promotionsArray['brand_id'])) {
                $promotionsArray['promotions_goods_content'] = serialize($promotionsArray['brand_id']);
            } else {
                $promotionsArray['promotions_goods_content'] = '';
            }
            $this->getDbshopTable()->updatePromotionsRule($promotionsArray, array('promotions_id'=>$promotionsId));
            $this->createPromotionsRuleIni();
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('优惠规则'), 'operlog_info'=>$this->getDbshopLang()->translate('编辑商品优惠规则') . '&nbsp;' . $promotionsArray['promotions_name']));
            
            if($promotionsArray['promotions_save_type'] != 'save_return_edit') {
                return $this->redirect()->toRoute('promotions/default');
            }
            $array['success_msg'] = $this->getDbshopLang()->translate('优惠规则编辑成功！');
        }
        
        $array['promotions_info'] = $this->getDbshopTable()->infoPromotionsRule(array('promotions_id'=>$promotionsId));
        //会员分组
        $array['group_array'] = $this->getDbshopTable('UserGroupTable')->listUserGroup(array('language'=>$this->getDbshopLang()->getLocale()));
        //商品分类
        $array['goods_class']  = $this->getDbshopTable('GoodsClassTable')->classOptions(0,$this->getDbshopTable('GoodsClassTable')->listGoodsClass());
        //商品品牌
        $array['goods_brand'] = $this->getDbshopTable('GoodsBrandTable')->listGoodsBrand();
        
        return $array;
    }
    /** 
     * 删除优惠规则
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function delAction()
    {
        $promotionsId = (int) $this->params('promotions_id', 0);
        if($promotionsId == 0) return $this->redirect()->toRoute('promotions/default');
        $promotionsInfo = $this->getDbshopTable()->infoPromotionsRule(array('promotions_id'=>$promotionsId));
        
        $this->getDbshopTable()->delPromotionsRule(array('promotions_id'=>$promotionsId));
        $this->createPromotionsRuleIni();
        //记录操作日志
        $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('优惠规则'), 'operlog_info'=>$this->getDbshopLang()->translate('删除商品优惠规则') . '&nbsp;' . $promotionsInfo->promotions_name));
        
        return $this->redirect()->toRoute('promotions/default');
    }
    /** 
     * 生成優惠規則ini文件
     */
    private function createPromotionsRuleIni()
    {
        $PromotionsRule = array();
        $iniCreate      = new \Zend\Config\Writer\Ini(); 
        $ruleArray      = $this->getDbshopTable()->listPromotionsRule();
        if(!empty($ruleArray)) {
            foreach ($ruleArray as $key => $value) {
                $value['promotions_name']          = str_replace('"', "'", $value['promotions_name']);
                $value['promotions_info']          = str_replace('"', "'", $value['promotions_info']);
                $value['promotions_user_group']    = (!empty($value['promotions_user_group']) ? unserialize($value['promotions_user_group']) : array());
                $value['promotions_goods_content'] = (!empty($value['promotions_goods_content']) ? unserialize($value['promotions_goods_content']) : array());
                $ruleArray[$key] = $value;
            }
            $PromotionsRule = $ruleArray;   
        }
        $iniCreate->toFile(DBSHOP_PATH . '/data/moduledata/System/promotions_rule.ini', $PromotionsRule);
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'PromotionsRuleTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}
