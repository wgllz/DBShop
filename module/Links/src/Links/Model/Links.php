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

namespace Links\Model;

class Links
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['links_id']      = (isset($data['links_id'])      and !empty($data['links_id']))      ? intval($data['links_id'])    : null;
        self::$dataArray['links_webname'] = (isset($data['links_webname']) and !empty($data['links_webname'])) ? trim($data['links_webname']) : null;
        self::$dataArray['links_url']     = (isset($data['links_url'])     and !empty($data['links_url']))     ? trim($data['links_url'])     : null;
        self::$dataArray['links_logo']    = (isset($data['links_logo'])    and !empty($data['links_logo']))    ? trim($data['links_logo'])    : null;
        self::$dataArray['links_sort']    = (isset($data['links_sort'])    and !empty($data['links_sort']))    ? intval($data['links_sort'])  : null;
    
        self::$dataArray = array_filter(self::$dataArray);
    
        self::$dataArray['links_state'] = (isset($data['links_state']) and !empty($data['links_state'])) ? intval($data['links_state']) : 0;
    
        return self::$dataArray;
    }

    /**
     * 添加友情链接过滤
     * @param array $data
     * @return array
     */
    public static function addLinksData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 更新友情链接过滤
     * @param array $data
     * @return array
     */
    public static function updataLinksData (array $data)
    {
        unset($data['links_id']);
        return self::checkData($data);
    }
}

?>