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

class OnlineGroup
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['online_group_id']     = (isset($data['online_group_id'])    and !empty($data['online_group_id']))    ? intval($data['online_group_id'])    : null;
        self::$dataArray['online_group_name']   = (isset($data['online_group_name'])  and !empty($data['online_group_name']))  ? trim($data['online_group_name'])    : null;
        self::$dataArray['online_group_sort']   = (isset($data['online_group_sort'])  and !empty($data['online_group_sort']))  ? intval($data['online_group_sort'])  : null;

        self::$dataArray = array_filter(self::$dataArray);
        
        self::$dataArray['index_show']          = (isset($data['index_show'])         and !empty($data['index_show']))         ? trim($data['index_show'])           : '';
        self::$dataArray['class_show']          = (isset($data['class_show'])         and !empty($data['class_show']))         ? trim($data['class_show'])           : '';
        self::$dataArray['goods_show']          = (isset($data['goods_show'])         and !empty($data['goods_show']))         ? trim($data['goods_show'])           : '';
        self::$dataArray['online_group_state']  = (isset($data['online_group_state']) and !empty($data['online_group_state'])) ? intval($data['online_group_state']) : 0;
    
        return self::$dataArray;
    }
    /**
     * 添加在线客服组过滤
     * @param array $data
     * @return array
     */
    public static function addOnlineGroupData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 更新在线客服组过滤
     * @param array $data
     * @return array
     */
    public static function updateOnlineGroupData (array $data)
    {
        unset($data['online_group_id']);
        return self::checkData($data);
    }
}

?>