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

namespace Goods\Model;

class PromotionsRule
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['promotions_id']            = (isset($data['promotions_id'])            and !empty($data['promotions_id']))            ? intval($data['promotions_id'])          : null;
        self::$dataArray['promotions_name']          = (isset($data['promotions_name'])          and !empty($data['promotions_name']))          ? trim($data['promotions_name'])          : null;
        self::$dataArray['promotions_info']          = (isset($data['promotions_info'])          and !empty($data['promotions_info']))          ? trim($data['promotions_info'])          : null;
        self::$dataArray['rule_state']               = (isset($data['rule_state'])               and !empty($data['rule_state']))               ? intval($data['rule_state'])             : null;
        self::$dataArray['discount_type']            = (isset($data['discount_type'])            and !empty($data['discount_type']))            ? intval($data['discount_type'])          : null;
        self::$dataArray['promotions_user_type']     = (isset($data['promotions_user_type'])     and !empty($data['promotions_user_type']))     ? trim($data['promotions_user_type'])     : null;
        self::$dataArray['promotions_goods_type']    = (isset($data['promotions_goods_type'])    and !empty($data['promotions_goods_type']))    ? trim($data['promotions_goods_type'])    : null;
        
        self::$dataArray = array_filter(self::$dataArray);
        
        self::$dataArray['shopping_amount']          = (isset($data['shopping_amount'])          and !empty($data['shopping_amount']))          ? intval($data['shopping_amount'])        : 0;
        self::$dataArray['shopping_discount']        = (isset($data['shopping_discount'])        and !empty($data['shopping_discount']))        ? intval($data['shopping_discount'])      : 0;
        self::$dataArray['promotions_start_time']    = (isset($data['promotions_start_time'])    and !empty($data['promotions_start_time']))    ? strtotime($data['promotions_start_time'])    : '';
        self::$dataArray['promotions_end_time']      = (isset($data['promotions_end_time'])      and !empty($data['promotions_end_time']))      ? strtotime($data['promotions_end_time'])      : '';
        self::$dataArray['promotions_user_group']    = (isset($data['promotions_user_group'])    and !empty($data['promotions_user_group']))    ? trim($data['promotions_user_group'])    : '';
        self::$dataArray['promotions_goods_content'] = (isset($data['promotions_goods_content']) and !empty($data['promotions_goods_content'])) ? trim($data['promotions_goods_content']) : '';
        
        return self::$dataArray;
    }
    /**
     * 过滤添加优惠规则信息
     * @param array $data
     * @return array
     */
    public static function addPromotionsRuleData(array $data)
    {
        return self::checkData($data);
    }
    /**
     * 过滤更新优惠规则信息
     * @param array $data
     * @return array
     */
    public static function updatePromotionsRuleData(array $data)
    {
        unset($data['promotions_id']);
        return self::checkData($data);
    }
}