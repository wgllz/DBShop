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

namespace Region\Model;

class RegionExtend
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['region_id']   = (isset($data['region_id'])   and !empty($data['region_id']))  ? intval($data['region_id']) : null;
        self::$dataArray['region_name'] = (isset($data['region_name']) and !empty($data['region_name']))? trim($data['region_name']) : null;
        self::$dataArray['language']    = (isset($data['language'])    and !empty($data['language']))   ? trim($data['language'])    : null;
    
        return array_filter(self::$dataArray);
    }
    public static function addRegionExtendData (array $data)
    {
        return self::checkData($data);
    }
    public static function updateRegionExtendData (array $data)
    {
        unset($data['region_id']);
        return self::checkData($data);
    }
}

?>