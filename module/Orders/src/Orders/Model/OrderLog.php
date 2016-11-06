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

class OrderLog
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['order_log_id'] = (isset($data['order_log_id']) and !empty($data['order_log_id'])) ? intval($data['order_log_id']) : null;
        self::$dataArray['order_id']     = (isset($data['order_id'])     and !empty($data['order_id']))     ? intval($data['order_id'])     : null;
        self::$dataArray['state_info']   = (isset($data['state_info'])   and !empty($data['state_info']))   ? trim($data['state_info'])     : null;
        self::$dataArray['log_time']     = (isset($data['log_time'])     and !empty($data['log_time']))     ? trim($data['log_time'])       : null;
        self::$dataArray['log_user']     = (isset($data['log_user'])     and !empty($data['log_user']))     ? trim($data['log_user'])       : null;
    
        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['order_state']  = (isset($data['order_state'])  and !empty($data['order_state']))  ? trim($data['order_state'])    : 0;

        return self::$dataArray;
    }
    /**
     * 添加订单历史过滤
     * @param array $data
     * @return array
     */
    public static function addOrderLogData (array $data)
    {
        return self::checkData($data);
    }
}