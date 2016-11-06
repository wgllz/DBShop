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

namespace Goods\Service;

class PromotionsRuleService
{
    /** 
     * 计算最终的优惠数额
     * @param array $cartGoods
     * @return number
     */
    public function promotionsRuleCalculation(array $promotionsData)
    {
        $promotionsRule = $this->getPromotionsRuleIni();
        //如果规则为空，则返回无优惠0
        if(empty($promotionsRule)) return 0;
        //判断购物车中是否有商品，如果没有返回无优惠0
        if(is_array($promotionsData['cartGoods']) and !empty($promotionsData['cartGoods'])) {
            //定义优惠数额数组
            $discountCost = array();
            //定义优惠名称数组
            $discountName = array();
            //在规则中匹配符合要求的优惠方式
            foreach ($promotionsRule as $ruleKey => $ruleValue) {
                $costTotal = 0;
                if($ruleValue['rule_state'] == '2') continue;
                if(!empty($ruleValue['promotions_start_time']) and !empty($ruleValue['promotions_end_time']) and $ruleValue['promotions_start_time']>$ruleValue['promotions_end_time']) continue;
                if(!empty($ruleValue['promotions_end_time']) and $ruleValue['promotions_end_time'] < time()) continue;
                
                //获取符合条件的商品价格总和，并与优惠规则中的各个规则进行匹配
                $costTotal = $this->cartCostTotal(array('cartGoods'=>$promotionsData['cartGoods'], 'rule'=>$ruleValue, 'user_group'=>$promotionsData['user_group']));
                
                //符合条件的优惠数额，存入数组中
                $shoppingAmount = (int) $ruleValue['shopping_amount'];
                if($costTotal >= $shoppingAmount) {//判断是否达到可优惠的数额
                    $shoppingDiscount = (int) $ruleValue['shopping_discount'];
                    if($ruleValue['discount_type'] == '1') {//优惠方式是 元
                        $discountCost[$ruleKey] = $shoppingDiscount;
                    } elseif ($ruleValue['discount_type'] == '2') {//优惠方式百分比
                        $discountCost[$ruleKey] = $costTotal * $shoppingDiscount / 100;
                    }
                    //使用优惠金额最为键名，优惠名称作为键值，可能会出现的问题，就是当有多个优惠金额相同的时候，优惠名称可能会对应不上
                    $discountName[$discountCost[$ruleKey]] = $ruleValue['promotions_info'];
                }
            }
            if(empty($discountCost)) return 0;
            
            $discountCostPrice = max($discountCost);
            return array('discountCost'=>$discountCostPrice, 'discountName'=>$discountName[$discountCostPrice]);
        }
        return 0;
    }
    /**
     * 获取符合条件的商品价格总和，并与优惠规则中的各个规则进行匹配
     * @param array $data
     * @return number
     */
    private function cartCostTotal(array $data)
    {
        $costTotal = 0;
        foreach ($data['cartGoods'] as $cartValue) {
            //所有用户所有商品
            if($data['rule']['promotions_user_type'] == 'all_user' and $data['rule']['promotions_goods_type'] == 'all_goods') {
                $costTotal = $costTotal + $cartValue['goods_shop_price'] * $cartValue['buy_num'];
                continue;
            //客户组，所有商品
            } elseif ($data['rule']['promotions_user_type'] == 'user_group' and $data['rule']['promotions_goods_type'] == 'all_goods') {
                if(!empty($data['user_group']) and isset($data['rule']['promotions_user_group']) and is_array($data['rule']['promotions_user_group']) and !empty($data['rule']['promotions_user_group'])) {
                    if(in_array($data['user_group'], $data['rule']['promotions_user_group'])) {
                        $costTotal = $costTotal + $cartValue['goods_shop_price'] * $cartValue['buy_num'];
                        continue;
                    }
                }
            //所有用户，商品分类    
            } elseif ($data['rule']['promotions_user_type'] == 'all_user' and $data['rule']['promotions_goods_type'] == 'class_goods') {
                if(isset($data['rule']['promotions_goods_content']) and is_array($data['rule']['promotions_goods_content']) and !empty($data['rule']['promotions_goods_content'])) {
                    foreach ($cartValue['class_id_array'] as $class_id) {
                        if(in_array($class_id, $data['rule']['promotions_goods_content'])) {
                            $costTotal = $costTotal + $cartValue['goods_shop_price'] * $cartValue['buy_num'];
                            continue 2;
                        }
                    }
                }
            //客户组，商品分类
            } elseif ($data['rule']['promotions_user_type'] == 'user_group' and $data['rule']['promotions_goods_type'] == 'class_goods') {
                $user_group_state  = false;
                $goods_class_state = false;
                if(!empty($data['user_group']) and isset($data['rule']['promotions_user_group']) and is_array($data['rule']['promotions_user_group']) and !empty($data['rule']['promotions_user_group'])) {
                    if(in_array($data['user_group'], $data['rule']['promotions_user_group'])) {
                        $user_group_state = true;
                    }
                }
                if(isset($data['rule']['promotions_goods_content']) and is_array($data['rule']['promotions_goods_content']) and !empty($data['rule']['promotions_goods_content'])) {
                    foreach ($cartValue['class_id_array'] as $class_id) {
                        if(in_array($class_id, $data['rule']['promotions_goods_content'])) {
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
            } elseif ($data['rule']['promotions_user_type'] == 'all_user' and $data['rule']['promotions_goods_type'] == 'brand_goods') {
                if(!empty($cartValue['brand_id']) and isset($data['rule']['promotions_goods_content']) and is_array($data['rule']['promotions_goods_content']) and !empty($data['rule']['promotions_goods_content'])) {
                    if(in_array($cartValue['brand_id'], $data['rule']['promotions_goods_content'])) {
                        $costTotal = $costTotal + $cartValue['goods_shop_price'] * $cartValue['buy_num'];
                        continue;
                    }
                }
            //客户组，商品品牌
            } elseif ($data['rule']['promotions_user_type'] == 'user_group' and $data['rule']['promotions_goods_type'] == 'brand_goods') {
                $user_group_state  = false;
                $goods_brand_state = false;
                if(!empty($data['user_group']) and isset($data['rule']['promotions_user_group']) and is_array($data['rule']['promotions_user_group']) and !empty($data['rule']['promotions_user_group'])) {
                    if(in_array($data['user_group'], $data['rule']['promotions_user_group'])) {
                        $user_group_state = true;
                    }
                }
                if(!empty($cartValue['brand_id']) and isset($data['rule']['promotions_goods_content']) and is_array($data['rule']['promotions_goods_content']) and !empty($data['rule']['promotions_goods_content'])) {
                    if(in_array($cartValue['brand_id'], $data['rule']['promotions_goods_content'])) $goods_brand_state = true;
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
     * 获取优惠规则设置信息
     * @return Ambigous <multitype:, multitype:multitype: >
     */
    private function getPromotionsRuleIni()
    {
        $iniConfigReader= new \Zend\Config\Reader\Ini();
        if(file_exists(DBSHOP_PATH . '/data/moduledata/System/promotions_rule.ini')) {
            $promotionsRule = $iniConfigReader->fromFile(DBSHOP_PATH . '/data/moduledata/System/promotions_rule.ini');
            if(empty($promotionsRule)) $promotionsRule = array();
        } else {
            $promotionsRule = array();
        }
        return $promotionsRule;        
    }
}

?>