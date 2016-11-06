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

class Region
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['region_id']     = (isset($data['region_id'])     and !empty($data['region_id']))     ? intval($data['region_id'])     : null;
        self::$dataArray['region_top_id'] = (isset($data['region_top_id']) and !empty($data['region_top_id'])) ? intval($data['region_top_id']) : null;
        self::$dataArray['region_sort']   = (isset($data['region_sort'])   and !empty($data['region_sort']))   ? intval($data['region_sort'])   : null;
        self::$dataArray['region_path']   = (isset($data['region_path'])   and !empty($data['region_path']))   ? trim($data['region_path'])     : null;
    
        return array_filter(self::$dataArray);
    }
    public static function addRegionData (array $data)
    {
        return self::checkData($data);
    }
    public static function updateRegionData (array $data)
    {
        unset($data['region_id']);
        unset($data['region_top_id']);
        unset($data['region_path']);
        return self::checkData($data);
    }
}

?>