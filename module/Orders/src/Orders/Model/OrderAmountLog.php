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


class OrderAmountLog
{
    private static $dataArray = array();

    private static function checkData ($data)
    {
        self::$dataArray['order_amount_log_id']     = (isset($data['order_amount_log_id'])      and !empty($data['order_amount_log_id']))       ? intval($data['order_amount_log_id'])  : null;
        self::$dataArray['order_edit_amount']       = (isset($data['order_edit_amount'])        and !empty($data['order_edit_amount']))         ? trim($data['order_edit_amount'])      : null;
        self::$dataArray['order_original_amount']   = (isset($data['order_original_amount'])    and !empty($data['order_original_amount']))     ? trim($data['order_original_amount'])  : null;
        self::$dataArray['order_edit_amount_type']  = (isset($data['order_edit_amount_type'])   and !empty($data['order_edit_amount_type']))    ? trim($data['order_edit_amount_type']) : null;
        self::$dataArray['order_edit_number']       = (isset($data['order_edit_number'])        and !empty($data['order_edit_number']))         ? trim($data['order_edit_number'])      : null;
        self::$dataArray['order_edit_amount_time']  = (isset($data['order_edit_amount_time'])   and !empty($data['order_edit_amount_time']))    ? trim($data['order_edit_amount_time']) : time();
        self::$dataArray['order_edit_amount_user']  = (isset($data['order_edit_amount_user'])   and !empty($data['order_edit_amount_user']))    ? trim($data['order_edit_amount_user']) : null;
        self::$dataArray['order_id']                = (isset($data['order_id'])                 and !empty($data['order_id']))                  ? intval($data['order_id'])             : null;

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['order_edit_amount_info']  = (isset($data['order_edit_amount_info'])   and !empty($data['order_edit_amount_info']))    ? trim($data['order_edit_amount_info']) : '';

        return self::$dataArray;
    }
    /**
     * 订单金额修改历史过滤
     * @param array $data
     * @return array
     */
    public static function addOrderAmountLogData(array $data)
    {
        return self::checkData($data);
    }
} 