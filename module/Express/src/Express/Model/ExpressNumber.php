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

namespace Express\Model;

class ExpressNumber {
    private static $dataArray = array();

    private static function checkData (array $data)
    {
        self::$dataArray['express_number_id']       = (isset($data['express_number_id'])        and !empty($data['express_number_id']))        ? intval($data['express_number_id'])     : null;
        self::$dataArray['express_number']          = (isset($data['express_number'])           and !empty($data['express_number']))           ? trim($data['express_number'])          : null;
        self::$dataArray['order_id']                = (isset($data['order_id'])                 and !empty($data['order_id']))                 ? intval($data['order_id'])              : null;
        self::$dataArray['order_sn']                = (isset($data['order_sn'])                 and !empty($data['order_sn']))                 ? trim($data['order_sn'])                : null;
        self::$dataArray['express_number_state']    = (isset($data['express_number_state'])     and !empty($data['express_number_state']))     ? intval($data['express_number_state'])  : null;
        self::$dataArray['express_number_use_time'] = (isset($data['express_number_use_time'])  and !empty($data['express_number_use_time']))  ? trim($data['express_number_use_time']) : null;
        self::$dataArray['express_id']              = (isset($data['express_id'])               and !empty($data['express_id']))               ? intval($data['express_id'])            : null;

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['express_number_state']    = (isset($data['express_number_state'])     and !empty($data['express_number_state']))     ? intval($data['express_number_state'])  : 0;

        return self::$dataArray;
    }
    /**
     * 过滤添加快递单号
     * @param array $data
     * @return array
     */
    public static function addExpressNumberData(array $data)
    {
        return self::checkData($data);
    }
} 