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

namespace System\Model;

class Online
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['online_id']       = (isset($data['online_id'])       and !empty($data['online_id']))        ? intval($data['online_id'])       : null;
        self::$dataArray['online_name']     = (isset($data['online_name'])     and !empty($data['online_name']))      ? trim($data['online_name'])       : null;
        self::$dataArray['online_account']  = (isset($data['online_account'])  and !empty($data['online_account']))   ? trim($data['online_account'])    : null;
        self::$dataArray['online_type']     = (isset($data['online_type'])     and !empty($data['online_type']))      ? trim($data['online_type'])       : null;
        self::$dataArray['online_web_code'] = (isset($data['online_web_code']) and !empty($data['online_web_code']))  ? trim($data['online_web_code'])   : null;
        self::$dataArray['online_group_id'] = (isset($data['online_group_id']) and !empty($data['online_group_id']))  ? intval($data['online_group_id']) : null;
        self::$dataArray['online_sort']     = (isset($data['online_sort'])     and !empty($data['online_sort']))      ? intval($data['online_sort'])     : null;
        
        self::$dataArray = array_filter(self::$dataArray);
        
        self::$dataArray['online_state']    = (isset($data['online_state'])    and !empty($data['online_state']))     ? intval($data['online_state'])    : 0;
        
        return self::$dataArray;
    }
    /**
     * 添加在线客服过滤
     * @param array $data
     * @return array
     */
    public static function addOnlineData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 更新在线客服过滤
     * @param array $data
     * @return array
     */
    public static function updateOnlineData (array $data)
    {
        unset($data['online_id']);
        return self::checkData($data);
    }
}

?>