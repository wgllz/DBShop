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

class NavigationExtend
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['navigation_id']     = (isset($data['navigation_id'])    and !empty($data['navigation_id']))    ? intval($data['navigation_id'])   : null;
        self::$dataArray['navigation_title']  = (isset($data['navigation_title']) and !empty($data['navigation_title'])) ? trim($data['navigation_title'])  : null;
        self::$dataArray['language']          = (isset($data['language'])         and !empty($data['language']))         ? trim($data['language'])          : null;
    
        return array_filter(self::$dataArray);
    }
    public static function addNavigationExtendData(array $data)
    {
        return self::checkData($data);
    }
    public static function updateNavigationExtendData(array $data)
    {
        unset($data['navigation_id']);
        return self::checkData($data);
    }
}

?>