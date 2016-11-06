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

namespace Orders\Model;

class Order 
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['order_id']            = (isset($data['order_id'])             and !empty($data['order_id']))            ? intval($data['order_id'])          : null;
        self::$dataArray['order_sn']            = (isset($data['order_sn'])             and !empty($data['order_sn']))            ? trim($data['order_sn'])            : null;
        self::$dataArray['order_out_sn']        = (isset($data['order_out_sn'])         and !empty($data['order_out_sn']))        ? trim($data['order_out_sn'])        : null;
        self::$dataArray['goods_serialize']     = (isset($data['goods_serialize'])      and !empty($data['goods_serialize']))     ? trim($data['goods_serialize'])     : null;
        self::$dataArray['goods_amount']        = (isset($data['goods_amount'])         and !empty($data['goods_amount']))        ? trim($data['goods_amount'])        : null;
        self::$dataArray['order_amount']        = (isset($data['order_amount'])         and !empty($data['order_amount']))        ? trim($data['order_amount'])        : null;
        self::$dataArray['user_pre_info']       = (isset($data['user_pre_info'])        and !empty($data['user_pre_info']))       ? trim($data['user_pre_info'])       : null;
        self::$dataArray['order_state']         = (isset($data['order_state'])          and !empty($data['order_state']))         ? trim($data['order_state'])         : null;
        self::$dataArray['ot_order_state']      = (isset($data['ot_order_state'])       and !empty($data['ot_order_state']))      ? trim($data['ot_order_state'])      : null;
        self::$dataArray['pay_code']            = (isset($data['pay_code'])             and !empty($data['pay_code']))            ? trim($data['pay_code'])            : null;
        self::$dataArray['pay_name']            = (isset($data['pay_name'])             and !empty($data['pay_name']))            ? trim($data['pay_name'])            : null;
        self::$dataArray['pay_time']            = (isset($data['pay_time'])             and !empty($data['pay_time']))            ? trim($data['pay_time'])            : null;
        self::$dataArray['pay_certification']   = (isset($data['pay_certification'])    and !empty($data['pay_certification']))   ? trim($data['pay_certification'])   : null;
        self::$dataArray['express_id']          = (isset($data['express_id'])           and !empty($data['express_id']))          ? intval($data['express_id'])        : null;
        self::$dataArray['express_name']        = (isset($data['express_name'])         and !empty($data['express_name']))        ? trim($data['express_name'])        : null;
        self::$dataArray['express_time']        = (isset($data['express_time'])         and !empty($data['express_time']))        ? trim($data['express_time'])        : null;
        self::$dataArray['buyer_id']            = (isset($data['buyer_id'])             and !empty($data['buyer_id']))            ? trim($data['buyer_id'])            : null;
        self::$dataArray['buyer_name']          = (isset($data['buyer_name'])           and !empty($data['buyer_name']))          ? trim($data['buyer_name'])          : null;
        self::$dataArray['buyer_email']         = (isset($data['buyer_email'])          and !empty($data['buyer_email']))         ? trim($data['buyer_email'])         : null;
        self::$dataArray['order_time']          = (isset($data['order_time'])           and !empty($data['order_time']))          ? trim($data['order_time'])          : null;
        self::$dataArray['finish_time']         = (isset($data['finish_time'])          and !empty($data['finish_time']))         ? trim($data['finish_time'])         : null;
        self::$dataArray['currency']            = (isset($data['currency'])             and !empty($data['currency']))            ? trim($data['currency'])            : null;
        self::$dataArray['currency_symbol']     = (isset($data['currency_symbol'])      and !empty($data['currency_symbol']))     ? trim($data['currency_symbol'])     : null;
        self::$dataArray['currency_unit']       = (isset($data['currency_unit'])        and !empty($data['currency_unit']))       ? trim($data['currency_unit'])       : null;
        self::$dataArray['order_message']       = (isset($data['order_message'])        and !empty($data['order_message']))       ? trim($data['order_message'])       : null;
        self::$dataArray['integral_rule_info']  = (isset($data['integral_rule_info'])   and !empty($data['integral_rule_info']))  ? trim($data['integral_rule_info'])  : null;
        self::$dataArray['integral_type_2_num_rule_info']  = (isset($data['integral_type_2_num_rule_info'])   and !empty($data['integral_type_2_num_rule_info']))  ? trim($data['integral_type_2_num_rule_info'])  : null;
        self::$dataArray['invoice_content']     = (isset($data['invoice_content'])      and !empty($data['invoice_content']))     ? trim($data['invoice_content'])     : null;
        self::$dataArray['refund_state']        = (isset($data['refund_state'])         and !empty($data['refund_state']))        ? intval($data['refund_state'])      : null;

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['integral_num']        = (isset($data['integral_num'])         and !empty($data['integral_num']))        ? intval($data['integral_num'])      : 0;
        self::$dataArray['integral_type_2_num'] = (isset($data['integral_type_2_num'])  and !empty($data['integral_type_2_num'])) ? intval($data['integral_type_2_num']): 0;
        self::$dataArray['pay_fee']             = (isset($data['pay_fee'])              and !empty($data['pay_fee']))             ? trim($data['pay_fee'])             : 0;
        self::$dataArray['express_fee']         = (isset($data['express_fee'])          and !empty($data['express_fee']))         ? trim($data['express_fee'])         : 0;
        self::$dataArray['user_pre_fee']        = (isset($data['user_pre_fee'])         and !empty($data['user_pre_fee']))        ? trim($data['user_pre_fee'])        : 0;
        self::$dataArray['buy_pre_fee']         = (isset($data['buy_pre_fee'])          and !empty($data['buy_pre_fee']))         ? trim($data['buy_pre_fee'])         : 0;
        self::$dataArray['integral_buy_num']    = (isset($data['integral_buy_num'])     and !empty($data['integral_buy_num']))    ? intval($data['integral_buy_num'])  : 0;
        self::$dataArray['integral_buy_price']  = (isset($data['integral_buy_price'])   and !empty($data['integral_buy_price']))  ? trim($data['integral_buy_price'])  : 0;
        self::$dataArray['goods_weight_amount'] = (isset($data['goods_weight_amount'])  and !empty($data['goods_weight_amount'])) ? trim($data['goods_weight_amount']) : 0;
        
        return self::$dataArray;
    }
    /**
     * 订单添加过滤
     * @param array $data
     * @return array
     */
    public static function addOrderData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 订单查询过滤
     * @param array $data
     * @return array
     */
    public static function whereOrderData (array $data)
    {
        $searchArray = array();
        
        $searchArray[] = (isset($data['order_state'])        and $data['order_state'] != '')            ? 'dbshop_order.order_state = ' . intval($data['order_state'])          : '';
        $searchArray[] = (isset($data['order_sn'])           and $data['order_sn'] != '')               ? 'dbshop_order.order_sn like \'%' . trim($data['order_sn']) . '%\''    : '';
        $searchArray[] = (isset($data['buyer_name'])         and $data['buyer_name'] != '')             ? 'dbshop_order.buyer_name like \'%' . trim($data['buyer_name']) .'%\'' : '';
        $searchArray[] = (isset($data['delivery_name'])      and $data['delivery_name'] != '')          ? 'a.delivery_name like \'%' . trim($data['delivery_name']) . '%\''     : '';
        $searchArray[] = (isset($data['order_start_price'])  and is_numeric($data['order_start_price']))? 'dbshop_order.order_amount >= '. trim($data['order_start_price'])     : '';
        $searchArray[] = (isset($data['order_end_price'])    and is_numeric($data['order_end_price'] != '')) ? 'dbshop_order.order_amount <= ' . trim($data['order_end_price']) : '';
        $searchArray[] = (isset($data['search_start_time'])  and $data['search_start_time'] != '')      ? 'dbshop_order.order_time >= ' . strtotime($data['search_start_time']) : '';
        $searchArray[] = (isset($data['search_end_time'])    and $data['search_end_time'] != '')        ? 'dbshop_order.order_time <= ' . strtotime($data['search_end_time'])   : '';
        $searchArray[] = (isset($data['buyer_id'])           and $data['buyer_id'] != '')               ? 'dbshop_order.buyer_id = ' . $data['buyer_id']                        : '';

        return array_filter($searchArray);
    }
}