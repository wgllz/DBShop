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

namespace User\Model;

class IntegralRule
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['integral_rule_id']            = (isset($data['integral_rule_id'])            and !empty($data['integral_rule_id']))            ? intval($data['integral_rule_id'])            : null;
        self::$dataArray['integral_type_id']            = (isset($data['integral_type_id'])            and !empty($data['integral_type_id']))            ? intval($data['integral_type_id'])            : null;
        self::$dataArray['integral_rule_name']          = (isset($data['integral_rule_name'])          and !empty($data['integral_rule_name']))          ? trim($data['integral_rule_name'])            : null;
        self::$dataArray['integral_rule_info']          = (isset($data['integral_rule_info'])          and !empty($data['integral_rule_info']))          ? trim($data['integral_rule_info'])            : null;
        self::$dataArray['integral_rule_state']         = (isset($data['integral_rule_state'])         and !empty($data['integral_rule_state']))         ? intval($data['integral_rule_state'])         : null;
        self::$dataArray['integral_rule_user_type']     = (isset($data['integral_rule_user_type'])     and !empty($data['integral_rule_user_type']))     ? trim($data['integral_rule_user_type'])       : null;
        self::$dataArray['integral_rule_goods_type']    = (isset($data['integral_rule_goods_type'])    and !empty($data['integral_rule_goods_type']))    ? trim($data['integral_rule_goods_type'])      : null;
    
        self::$dataArray = array_filter(self::$dataArray);
    
        self::$dataArray['shopping_type']               = (isset($data['shopping_type'])               and !empty($data['shopping_type']))               ? intval($data['shopping_type'])               : 1;
        self::$dataArray['shopping_amount']             = (isset($data['shopping_amount'])             and !empty($data['shopping_amount']))             ? intval($data['shopping_amount'])             : 0;
        self::$dataArray['integral_num']                = (isset($data['integral_num'])                and !empty($data['integral_num']))                ? intval($data['integral_num'])                : 0;
        self::$dataArray['integral_rule_start_time']    = (isset($data['integral_rule_start_time'])    and !empty($data['integral_rule_start_time']))    ? strtotime($data['integral_rule_start_time']) : '';
        self::$dataArray['integral_rule_end_time']      = (isset($data['integral_rule_end_time'])      and !empty($data['integral_rule_end_time']))      ? strtotime($data['integral_rule_end_time'])   : '';
        self::$dataArray['integral_rule_user_group']    = (isset($data['integral_rule_user_group'])    and !empty($data['integral_rule_user_group']))    ? trim($data['integral_rule_user_group'])      : '';
        self::$dataArray['integral_rule_goods_content'] = (isset($data['integral_rule_goods_content']) and !empty($data['integral_rule_goods_content'])) ? trim($data['integral_rule_goods_content'])   : '';
    
        return self::$dataArray;
    }
    /**
     * 添加积分规则过滤
     * @param array $data
     * @return array
     */
    public static function addIntegralRuleData(array $data)
    {
        return self::checkData($data);
    }
    /**
     * 过滤更新积分规则信息
     * @param array $data
     * @return array
     */
    public static function updateIntegralRuleData(array $data)
    {
        unset($data['integral_rule_id']);
        return self::checkData($data);
    }
}