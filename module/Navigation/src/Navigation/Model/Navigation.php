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

namespace Navigation\Model;

class Navigation
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['navigation_id']         = (isset($data['navigation_id'])          and !empty($data['navigation_id']))         ? intval($data['navigation_id'])         : null;
        self::$dataArray['navigation_url']        = (isset($data['navigation_url'])         and !empty($data['navigation_url']))        ? trim($data['navigation_url'])          : null;
        self::$dataArray['navigation_type']       = (isset($data['navigation_type'])        and !empty($data['navigation_type']))       ? intval($data['navigation_type'])       : null;
        self::$dataArray['navigation_sort']       = (isset($data['navigation_sort'])        and !empty($data['navigation_sort']))       ? intval($data['navigation_sort'])       : null;
    
        self::$dataArray = array_filter(self::$dataArray);
        
        self::$dataArray['goods_class_id']        = (isset($data['goods_class_id'])         and !empty($data['goods_class_id']))        ? intval($data['goods_class_id'])        : 0;
        self::$dataArray['navigation_new_window'] = (isset($data['navigation_new_window'])  and !empty($data['navigation_new_window'])) ? intval($data['navigation_new_window']) : 0;

        return self::$dataArray;
    }
    public static function addNavigationData (array $data)
    {
        return self::checkData($data);
    }
    public static function updateNavigationData (array $data)
    {
        unset($data['navigation_id']);
        return self::checkData($data);
    }
}

?>