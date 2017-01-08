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

namespace User\Service;

class IntegralRuleService
{
    /**
     * 计算最终的积分数额
     * @param array $integralData
     * @param int $integralTypeId
     * @return array|int
     */
    public function integralRuleCalculation(array $integralData, $integralTypeId=1)
    {
        $integralRule = $this->getIntegralRuleIni();
        //如果规则为空，则返回无积分0
        if(empty($integralRule)) return 0;
        //判断购物车中是否有商品，如果没有返回无积分0
        if(is_array($integralData['cartGoods']) and !empty($integralData['cartGoods'])) {
            //定义积分数额数组
            $integralCost = array();
            //定义积分名称数组
            $integralName = array();
            //在规则中匹配符合要求的积分方式
            foreach ($integralRule as $ruleKey => $ruleValue) {
                if($integralTypeId == 1 and $ruleValue['integral_type_id'] == 2) continue;  //消费积分计算，去除等级积分
                if($integralTypeId == 2 and $ruleValue['integral_type_id'] == 1) continue;  //等级积分计算，去除消费积分
                $costTotal = 0;
                if($ruleValue['integral_rule_state'] == '2') continue;
                if(!empty($ruleValue['integral_rule_start_time']) and !empty($ruleValue['integral_rule_end_time']) and $ruleValue['integral_rule_start_time']>$ruleValue['integral_rule_end_time']) continue;
                if(!empty($ruleValue['integral_rule_end_time']) and $ruleValue['integral_rule_end_time'] < time()) continue;
    
                //获取符合条件的商品价格总和，并与积分规则中的各个规则进行匹配
                $costTotal = $this->cartCostTotal(array('cartGoods'=>$integralData['cartGoods'], 'rule'=>$ruleValue, 'user_group'=>$integralData['user_group']));
    
                //符合条件的积分数额，存入数组中
                $shoppingAmount = (int) $ruleValue['shopping_amount'];
                //每多少，送x积分
                if($costTotal >= $shoppingAmount) {
                    if(isset($ruleValue['shopping_type']) and $ruleValue['shopping_type'] == 2) {
                        $n = floor($costTotal/$shoppingAmount);
                        $integralCost[$ruleKey] = (int) ($ruleValue['integral_num'] * $n);
                        $integralName[$integralCost[$ruleKey]] = $ruleValue['integral_rule_info'];
                    } else {
                        $integralCost[$ruleKey] = (int) $ruleValue['integral_num'];
                        //使用积分金额作为键名，积分名称作为键值，可能会出现的问题，就是当有多个积分金额相同的时候，积分名称可能会对应不上
                        $integralName[$integralCost[$ruleKey]] = $ruleValue['integral_rule_info'];
                    }
                }
            }
            if(empty($integralCost)) return 0;
    
            $integralNum = max($integralCost);
            return array('integralNum'=>$integralNum, 'integalRuleInfo'=>$integralName[$integralNum]);
        }
        return 0;
    }
    /**
     * 获取符合条件的商品价格总和，并与积分规则中的各个规则进行匹配
     * @param array $data
     * @return number
     */
    private function cartCostTotal(array $data)
    {
        $costTotal = 0;
        foreach ($data['cartGoods'] as $cartValue) {
            //所有用户所有商品
            if($data['rule']['integral_rule_user_type'] == 'all_user' and $data['rule']['integral_rule_goods_type'] == 'all_goods') {
                $costTotal = $costTotal + $cartValue['goods_shop_price'] * $cartValue['buy_num'];
                continue;
                //客户组，所有商品
            } elseif ($data['rule']['integral_rule_user_type'] == 'user_group' and $data['rule']['integral_rule_goods_type'] == 'all_goods') {
                if(!empty($data['user_group']) and isset($data['rule']['integral_rule_user_group']) and is_array($data['rule']['integral_rule_user_group']) and !empty($data['rule']['integral_rule_user_group'])) {
                    if(in_array($data['user_group'], $data['rule']['integral_rule_user_group'])) {
                        $costTotal = $costTotal + $cartValue['goods_shop_price'] * $cartValue['buy_num'];
                        continue;
                    }
                }
                //所有用户，商品分类
            } elseif ($data['rule']['integral_rule_user_type'] == 'all_user' and $data['rule']['integral_rule_goods_type'] == 'class_goods') {
                if(isset($data['rule']['integral_rule_goods_content']) and is_array($data['rule']['integral_rule_goods_content']) and !empty($data['rule']['integral_rule_goods_content'])) {
                    foreach ($cartValue['class_id_array'] as $class_id) {
                        if(in_array($class_id, $data['rule']['integral_rule_goods_content'])) {
                            $costTotal = $costTotal + $cartValue['goods_shop_price'] * $cartValue['buy_num'];
                            continue 2;
                        }
                    }
                }
                //客户组，商品分类
            } elseif ($data['rule']['integral_rule_user_type'] == 'user_group' and $data['rule']['integral_rule_goods_type'] == 'class_goods') {
                $user_group_state  = false;
                $goods_class_state = false;
                if(!empty($data['user_group']) and isset($data['rule']['integral_rule_user_group']) and is_array($data['rule']['integral_rule_user_group']) and !empty($data['rule']['integral_rule_user_group'])) {
                    if(in_array($data['user_group'], $data['rule']['integral_rule_user_group'])) {
                        $user_group_state = true;
                    }
                }
                if(isset($data['rule']['integral_rule_goods_content']) and is_array($data['rule']['integral_rule_goods_content']) and !empty($data['rule']['integral_rule_goods_content'])) {
                    foreach ($cartValue['class_id_array'] as $class_id) {
                        if(in_array($class_id, $data['rule']['integral_rule_goods_content'])) {
                            $goods_class_state = true;
                            break;
                        }
                    }
                }
                if($user_group_state and $goods_class_state) {
                    $costTotal = $costTotal + $cartValue['goods_shop_price'] * $cartValue['buy_num'];
                    continue;
                }
                //所有用户，商品品牌
            } elseif ($data['rule']['integral_rule_user_type'] == 'all_user' and $data['rule']['integral_rule_goods_type'] == 'brand_goods') {
                if(!empty($cartValue['brand_id']) and isset($data['rule']['integral_rule_goods_content']) and is_array($data['rule']['integral_rule_goods_content']) and !empty($data['rule']['integral_rule_goods_content'])) {
                    if(in_array($cartValue['brand_id'], $data['rule']['integral_rule_goods_content'])) {
                        $costTotal = $costTotal + $cartValue['goods_shop_price'] * $cartValue['buy_num'];
                        continue;
                    }
                }
                //客户组，商品品牌
            } elseif ($data['rule']['integral_rule_user_type'] == 'user_group' and $data['rule']['integral_rule_goods_type'] == 'brand_goods') {
                $user_group_state  = false;
                $goods_brand_state = false;
                if(!empty($data['user_group']) and isset($data['rule']['integral_rule_user_group']) and is_array($data['rule']['integral_rule_user_group']) and !empty($data['rule']['integral_rule_user_group'])) {
                    if(in_array($data['user_group'], $data['rule']['integral_rule_user_group'])) {
                        $user_group_state = true;
                    }
                }
                if(!empty($cartValue['brand_id']) and isset($data['rule']['integral_rule_goods_content']) and is_array($data['rule']['integral_rule_goods_content']) and !empty($data['rule']['integral_rule_goods_content'])) {
                    if(in_array($cartValue['brand_id'], $data['rule']['integral_rule_goods_content'])) $goods_brand_state = true;
                }
                if($user_group_state and $goods_brand_state) {
                    $costTotal = $costTotal + $cartValue['goods_shop_price'] * $cartValue['buy_num'];
                    continue;
                }
            }
        }
        return $costTotal;
    }
    /**
     * 获取积分规则设置信息
     * @return Ambigous <multitype:, multitype:multitype: >
     */
    private function getIntegralRuleIni()
    {
        $iniConfigReader= new \Zend\Config\Reader\Ini();
        if(file_exists(DBSHOP_PATH . '/data/moduledata/User/integral_rule.ini')) {
            $IntegralRule = $iniConfigReader->fromFile(DBSHOP_PATH . '/data/moduledata/User/integral_rule.ini');
            if(empty($IntegralRule)) $IntegralRule = array();
        } else {
            $IntegralRule = array();
        }
        return $IntegralRule;
    }
}