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

class AdSlide
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['ad_id']          = (isset($data['ad_id'])          and !empty($data['ad_id']))          ? intval($data['ad_id'])         : null;
        self::$dataArray['ad_slide_info']  = (isset($data['ad_slide_info'])  and !empty($data['ad_slide_info']))  ? trim($data['ad_slide_info'])   : null;
        self::$dataArray['ad_slide_image'] = (isset($data['ad_slide_image']) and !empty($data['ad_slide_image'])) ? trim($data['ad_slide_image'])  : null;
        self::$dataArray['ad_slide_url']   = (isset($data['ad_slide_url'])   and !empty($data['ad_slide_url']))   ? trim($data['ad_slide_url'])    : null;
        self::$dataArray['ad_slide_sort']  = (isset($data['ad_slide_sort'])  and !empty($data['ad_slide_sort']))  ? intval($data['ad_slide_sort']) : null;

        self::$dataArray = array_filter(self::$dataArray);
    
        return self::$dataArray;
    }
    /**
     * 添加幻灯片广告过滤
     * @param array $data
     * @return array
     */
    public static function addAdSlideData (array $data)
    {
        return self::checkData($data);
    }
}