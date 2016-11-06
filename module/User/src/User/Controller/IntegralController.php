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

namespace User\Controller;

use Admin\Controller\BaseController;

class IntegralController extends BaseController
{
    public function indexAction()
    {
        return $this->redirect()->toRoute('integral/default', array('action'=>'integralRule'));
    }
    /** 
     * 积分记录列表
     * @return multitype:NULL
     */
    public function integrallogAction()
    {
        $array        = array();
    
        $page = $this->params('page',1);
        $array['integral_log_list'] = $this->getDbshopTable('IntegralLogTable')->listIntegralLog(array('page'=>$page, 'page_num'=>20));

        $integralType = $this->getDbshopTable('UserIntegralTypeTable')->listUserIntegralType(array('e.language'=>$this->getDbshopLang()->getLocale()));
        $typeArray = array();
        if(is_array($integralType) and !empty($integralType)) {
            foreach($integralType as $typeValue) {
                $typeArray[$typeValue['integral_type_id']] = $typeValue['integral_type_name'];
            }
        }
        $array['integral_type_array'] = $typeArray;

        return $array;
    }
    /**
     * 积分类型列表
     * @return array
     */
    public function integralTypeAction()
    {
        $array = array();

        $array['user_integral_type_list'] = $this->getDbshopTable('UserIntegralTypeTable')->listUserIntegralType(array('e.language'=>$this->getDbshopLang()->getLocale()));

        return $array;
    }
    /**
     * 积分类型编辑
     */
    public function integralTypeEditAction()
    {
        $array = array();

        if($this->request->isPost()) {//积分类型更新处理
            $integralTypeArray = $this->request->getPost()->toArray();
            if(is_array($integralTypeArray) and !empty($integralTypeArray)) {
                $this->getDbshopTable('UserIntegralTypeTable')->updateUserIntegralType($integralTypeArray, array('integral_type_id'=>$integralTypeArray['integral_type_id']));
                //对类型的名称，不进行修改，注释掉
                //$this->getDbshopTable('UserIntegralTypeExtendTable')->updateUserIntegralTypeExtend($integralTypeArray, array('integral_type_id'=>$integralTypeArray['integral_type_id'], 'language'=>$this->getDbshopLang()->getLocale()));
                $array['success_msg'] = $this->getDbshopLang()->translate('积分类型信息编辑成功！');
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('积分类型'), 'operlog_info'=>$this->getDbshopLang()->translate('编辑积分类型') . '&nbsp;' . $integralTypeArray['integral_type_name']));
            }
        }

        $integralTypeId = (int) $this->params('integral_type_id', 0);
        if($integralTypeId == 0) return $this->redirect()->toRoute('integral/default', array('action'=>'integralType'));

        $array['integralTypeInfo'] = $this->getDbshopTable('UserIntegralTypeTable')->userIntegralTypeInfo(array('e.integral_type_id'=>$integralTypeId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        if(empty($array['integralTypeInfo'])) return $this->redirect()->toRoute('integral/default', array('action'=>'integralType'));

        return $array;
    }
    /**
     * 积分规则列表
     * @return multitype:
     */
    public function integralRuleAction()
    {
        $array = array();
        
        $array['integral_rule_list'] = $this->getDbshopTable()->listIntegralRule();

        return $array;
    }
    /** 
     * 积分规则添加
     */
    public function addIntegralRuleAction()
    {
        $array = array();
        
        if($this->request->isPost()) {
            $integralRuleArray = $this->request->getPost()->toArray();
            if(isset($integralRuleArray['integral_rule_user_group']) and !empty($integralRuleArray['integral_rule_user_group']) and $integralRuleArray['integral_rule_user_type'] == 'user_group') {
                $integralRuleArray['integral_rule_user_group'] = serialize($integralRuleArray['integral_rule_user_group']);
            } else {
                $integralRuleArray['integral_rule_user_group'] = '';
            }
            if($integralRuleArray['integral_rule_goods_type'] == 'class_goods' and !empty($integralRuleArray['class_id'])) {
                $integralRuleArray['integral_rule_goods_content'] = serialize($integralRuleArray['class_id']);
            } elseif($integralRuleArray['integral_rule_goods_type'] == 'brand_goods' and !empty($integralRuleArray['brand_id'])) {
                $integralRuleArray['integral_rule_goods_content'] = serialize($integralRuleArray['brand_id']);
            } else {
                $integralRuleArray['integral_rule_goods_content'] = '';
            }
        
            $this->getDbshopTable()->addIntegralRule($integralRuleArray);
            $this->createIntegralRuleIni();
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('积分规则'), 'operlog_info'=>$this->getDbshopLang()->translate('添加积分规则') . '&nbsp;' . $integralRuleArray['integral_rule_name']));
        
            unset($integralRuleArray);
            return $this->redirect()->toRoute('integral/default', array('action'=>'integralRule'));
        }
        
        //会员分组
        $array['group_array'] = $this->getDbshopTable('UserGroupTable')->listUserGroup(array('language'=>$this->getDbshopLang()->getLocale()));
        //商品分类
        $array['goods_class']  = $this->getDbshopTable('GoodsClassTable')->classOptions(0,$this->getDbshopTable('GoodsClassTable')->listGoodsClass());
        //商品品牌
        $array['goods_brand'] = $this->getDbshopTable('GoodsBrandTable')->listGoodsBrand();
        //积分类型
        $array['integral_type'] = $this->getDbshopTable('UserIntegralTypeTable')->listUserIntegralType(array('dbshop_user_integral_type.integral_type_state=1', 'e.language'=>$this->getDbshopLang()->getLocale()));

        return $array;        
    }
    /** 
     * 编辑积分规则
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:NULL Ambigous <string, string, NULL, boolean, \Zend\EventManager\mixed, mixed>
     */
    public function editIntegralRuleAction()
    {
        $integralRuleId = (int) $this->params('integral_rule_id', 0);
        if($integralRuleId == 0) return $this->redirect()->toRoute('integral/default', array('action'=>'integralRule'));
        
        $array = array();
        
        if($this->request->isPost()) {
            $integralRuleArray = $this->request->getPost()->toArray();
            if(isset($integralRuleArray['integral_rule_user_group']) and !empty($integralRuleArray['integral_rule_user_group']) and $integralRuleArray['integral_rule_user_type'] == 'user_group') {
                $integralRuleArray['integral_rule_user_group'] = serialize($integralRuleArray['integral_rule_user_group']);
            } else {
                $integralRuleArray['integral_rule_user_group'] = '';
            }
            if($integralRuleArray['integral_rule_goods_type'] == 'class_goods' and !empty($integralRuleArray['class_id'])) {
                $integralRuleArray['integral_rule_goods_content'] = serialize($integralRuleArray['class_id']);
            } elseif($integralRuleArray['integral_rule_goods_type'] == 'brand_goods' and !empty($integralRuleArray['brand_id'])) {
                $integralRuleArray['integral_rule_goods_content'] = serialize($integralRuleArray['brand_id']);
            } else {
                $integralRuleArray['integral_rule_goods_content'] = '';
            }
            
            $this->getDbshopTable()->updateIntegralRule($integralRuleArray, array('integral_rule_id'=>$integralRuleId));
            $this->createIntegralRuleIni();
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('积分规则'), 'operlog_info'=>$this->getDbshopLang()->translate('编辑积分规则') . '&nbsp;' . $integralRuleArray['integral_rule_name']));
        
            if($integralRuleArray['integral_rule_save_type'] != 'save_return_edit') {
                return $this->redirect()->toRoute('integral/default', array('action'=>'integralRule'));
            }
            $array['success_msg'] = $this->getDbshopLang()->translate('积分规则编辑成功！');
        }
        
        $array['integral_rule_info'] = $this->getDbshopTable()->infoIntegralRule(array('integral_rule_id'=>$integralRuleId));
        //会员分组
        $array['group_array'] = $this->getDbshopTable('UserGroupTable')->listUserGroup(array('language'=>$this->getDbshopLang()->getLocale()));
        //商品分类
        $array['goods_class']  = $this->getDbshopTable('GoodsClassTable')->classOptions(0,$this->getDbshopTable('GoodsClassTable')->listGoodsClass());
        //商品品牌
        $array['goods_brand'] = $this->getDbshopTable('GoodsBrandTable')->listGoodsBrand();
        //积分类型
        $array['integral_type'] = $this->getDbshopTable('UserIntegralTypeTable')->listUserIntegralType(array('dbshop_user_integral_type.integral_type_state=1', 'e.language'=>$this->getDbshopLang()->getLocale()));

        return $array;
    }
    /** 
     * 删除积分规则
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function delIntegralRuleAction()
    {
        $integralRuleId = (int) $this->params('integral_rule_id', 0);
        if($integralRuleId == 0) return $this->redirect()->toRoute('integral/default', array('action'=>'integralRule'));
        $integralRuleInfo = $this->getDbshopTable()->infoIntegralRule(array('integral_rule_id'=>$integralRuleId));
        
        $this->getDbshopTable()->delIntegralRule(array('integral_rule_id'=>$integralRuleId));
        $this->createIntegralRuleIni();
        //记录操作日志
        $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('积分规则'), 'operlog_info'=>$this->getDbshopLang()->translate('删除积分规则') . '&nbsp;' . $integralRuleInfo->integral_rule_name));
        
        return $this->redirect()->toRoute('integral/default', array('action'=>'integralRule'));
    }
    /**
     * 管理员调增会员积分值
     */
    public function changeIntegralNumAction()
    {
        $message = $this->getDbshopLang()->translate('用户积分调整失败！');
        if($this->request->isPost()) {
            $changeIntegralArray = $this->request->getPost()->toArray();
            $changeIntegralArray['change_user_integral_num']    = intval($changeIntegralArray['change_user_integral_num']);
            $changeIntegralArray['change_integral_type_2_num']  = intval($changeIntegralArray['change_integral_type_2_num']);

            $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$changeIntegralArray['integral_user_id']));
            if(isset($userInfo->user_id) and !empty($userInfo->user_id) and (!empty($changeIntegralArray['change_user_integral_num']) or !empty($changeIntegralArray['change_integral_type_2_num']))) {
                $integralLogArray = array();
                $integralLogArray['user_id']    = $userInfo->user_id;
                $integralLogArray['user_name']  = $userInfo->user_name;
                $integralLogArray['integral_log_time'] = time();

                if($changeIntegralArray['change_user_integral_num'] > 0) {
                    //当消费积分减少值大于当前值时，自动设置为当前值
                    if($changeIntegralArray['change_type_1'] == '-' and $changeIntegralArray['change_user_integral_num'] > $userInfo->user_integral_num) $changeIntegralArray['change_user_integral_num'] = $userInfo->user_integral_num;

                    $integralLogArray['integral_log_info'] = $this->getDbshopLang()->translate('管理员调整消费积分：') . $changeIntegralArray['change_type_1'] . $changeIntegralArray['change_user_integral_num'];
                    $integralLogArray['integral_num_log']  = str_replace('+', '', $changeIntegralArray['change_type_1']) . $changeIntegralArray['change_user_integral_num'];
                    if($this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray)) {
                        //会员消费积分更新
                        $this->getDbshopTable('UserTable')->updateUserIntegralNum($integralLogArray, array('user_id'=>$integralLogArray['user_id']));
                        //操作日志
                        $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('消费积分'), 'operlog_info'=>$this->getDbshopLang()->translate('管理员调整处理').' '.$this->getDbshopLang()->translate('被调整人:').$userInfo->user_name.' '.$this->getDbshopLang()->translate('调整值:') . $changeIntegralArray['change_type_1'] . $changeIntegralArray['change_user_integral_num']));
                    }
                }
                if($changeIntegralArray['change_integral_type_2_num'] > 0) {
                    //当消费积分减少值大于当前值时，自动设置为当前值
                    if($changeIntegralArray['change_type_2'] == '-' and $changeIntegralArray['change_integral_type_2_num'] > $userInfo->integral_type_2_num) $changeIntegralArray['change_integral_type_2_num'] = $userInfo->integral_type_2_num;

                    $integralLogArray['integral_type_id'] = 2;
                    $integralLogArray['integral_log_info'] = $this->getDbshopLang()->translate('管理员调整等级积分：') . $changeIntegralArray['change_type_2'] . $changeIntegralArray['change_integral_type_2_num'];
                    $integralLogArray['integral_num_log']  = str_replace('+', '', $changeIntegralArray['change_type_2']) . $changeIntegralArray['change_integral_type_2_num'];
                    if($this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray)) {
                        //会员等级积分更新
                        $this->getDbshopTable('UserTable')->updateUserIntegralNum($integralLogArray, array('user_id'=>$integralLogArray['user_id']), 2);
                        //操作日志
                        $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('等级积分'), 'operlog_info'=>$this->getDbshopLang()->translate('管理员调整处理').' '.$this->getDbshopLang()->translate('被调整人:').$userInfo->user_name.' '.$this->getDbshopLang()->translate('调整值:') . $changeIntegralArray['change_type_2'] . $changeIntegralArray['change_integral_type_2_num']));
                    }
                }

                $message = 'true';
            }
        }
        exit($message);
    }
    /**
     * 商品价格转换为消费积分
     */
    public function goodsPriceChangeIntegralAction()
    {
        $state = 'false';
        if($this->request->isPost()) {
            $price = floatval($this->request->getPost('price'));
            $integralInfo = $this->getDbshopTable('UserIntegralTypeTable')->userIntegarlTypeOneInfo(array('integral_type_id'=>1));
            $state = $price / $integralInfo->integral_currency_con * 100;
        }
        exit($state);
    }
    /**
     * 生成優惠規則ini文件
     */
    private function createIntegralRuleIni()
    {
        $IntegralRule = array();
        $iniCreate      = new \Zend\Config\Writer\Ini();
        $ruleArray      = $this->getDbshopTable()->listIntegralRule();
        if(!empty($ruleArray)) {
            foreach ($ruleArray as $key => $value) {
                $value['integral_rule_name']          = str_replace('"', "'", $value['integral_rule_name']);
                $value['integral_rule_info']          = str_replace('"', "'", $value['integral_rule_info']);
                $value['integral_rule_user_group']    = (!empty($value['integral_rule_user_group'])    ? unserialize($value['integral_rule_user_group'])    : array());
                $value['integral_rule_goods_content'] = (!empty($value['integral_rule_goods_content']) ? unserialize($value['integral_rule_goods_content']) : array());
                $ruleArray[$key] = $value;
            }
            $IntegralRule = $ruleArray;
        }
        $iniCreate->toFile(DBSHOP_PATH . '/data/moduledata/User/integral_rule.ini', $IntegralRule);
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'IntegralRuleTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}