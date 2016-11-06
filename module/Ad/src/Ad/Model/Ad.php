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

namespace Ad\Model;

class Ad
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['ad_id']           = (isset($data['ad_id'])           and !empty($data['ad_id']))           ? intval($data['ad_id'])       : null;
        self::$dataArray['ad_class']        = (isset($data['ad_class'])        and !empty($data['ad_class']))        ? trim($data['ad_class'])      : null;
        self::$dataArray['ad_name']         = (isset($data['ad_name'])         and !empty($data['ad_name']))         ? trim($data['ad_name'])       : null;
        self::$dataArray['ad_place']        = (isset($data['ad_place'])        and !empty($data['ad_place']))        ? trim($data['ad_place'])      : null;
        self::$dataArray['ad_type']         = (isset($data['ad_type'])         and !empty($data['ad_type']))         ? trim($data['ad_type'])       : null;
        self::$dataArray['ad_width']        = (isset($data['ad_width'])        and !empty($data['ad_width']))        ? trim($data['ad_width'])      : null;
        self::$dataArray['ad_height']       = (isset($data['ad_height'])       and !empty($data['ad_height']))       ? trim($data['ad_height'])     : null;
        self::$dataArray['ad_url']          = (isset($data['ad_url'])          and !empty($data['ad_url']))          ? trim($data['ad_url'])        : null;
        self::$dataArray['ad_body']         = (isset($data['ad_body'])         and !empty($data['ad_body']))         ? trim($data['ad_body'])       : null;
        self::$dataArray['ad_state']        = (isset($data['ad_state'])        and !empty($data['ad_state']))        ? $data['ad_state']            : null;
        self::$dataArray['template_ad']     = (isset($data['template_ad'])     and !empty($data['template_ad']))     ? trim($data['template_ad'])   : null;
        self::$dataArray['show_type']       = (isset($data['show_type'])       and !empty($data['show_type']))       ? trim($data['show_type'])     : 'pc';

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['ad_start_time']   = (isset($data['ad_start_time'])   and !empty($data['ad_start_time']))   ? strtotime($data['ad_start_time']) : '';
        self::$dataArray['ad_end_time']     = (isset($data['ad_end_time'])     and !empty($data['ad_end_time']))     ? strtotime($data['ad_end_time'])   : '';

        return self::$dataArray;
    }
    /**
     * 添加广告内容检查
     * @param array $data
     * @return array
     */
    public static function addAdData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 更新广告内容
     * @param array $data
     * @return array
     */
    public static function updateAdDate (array $data)
    {
        if(isset($data['ad_id'])) unset($data['ad_id']);
        
        return self::checkData($data);
    }
}