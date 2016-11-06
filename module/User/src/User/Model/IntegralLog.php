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

class IntegralLog
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['integral_log_id']  = (isset($data['integral_log_id'])         and !empty($data['integral_log_id']))         ? intval($data['integral_log_id'])      : null;
        self::$dataArray['user_id']          = (isset($data['user_id'])                 and !empty($data['user_id']))                 ? intval($data['user_id'])              : null;
        self::$dataArray['user_name']        = (isset($data['user_name'])               and !empty($data['user_name']))               ? trim($data['user_name'])              : null;
        self::$dataArray['integral_log_info']= (isset($data['integral_log_info'])       and !empty($data['integral_log_info']))       ? trim($data['integral_log_info'])      : null;
        self::$dataArray['integral_num_log'] = (isset($data['integral_num_log'])        and !empty($data['integral_num_log'])) ?      floatval($data['integral_num_log'])     : null;
        self::$dataArray['integral_type_id'] = (isset($data['integral_type_id'])        and !empty($data['integral_type_id'])) ?      intval($data['integral_type_id'])       : null;
        self::$dataArray['integral_log_time']= (isset($data['integral_log_time'])       and !empty($data['integral_log_time']))       ? trim($data['integral_log_time'])      : time();
    
        return array_filter(self::$dataArray);
    }
    /**
     * 添加积分记录过滤
     * @param array $data
     * @return array
     */
    public static function addIntegralLogData(array $data)
    {
        return self::checkData($data);
    }
}