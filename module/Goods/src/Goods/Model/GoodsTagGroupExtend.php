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

namespace Goods\Model;

class GoodsTagGroupExtend
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['tag_group_id']    = (isset($data['tag_group_id'])   and !empty($data['tag_group_id']))   ? intval($data['tag_group_id'])   : null;
        self::$dataArray['tag_group_name']  = (isset($data['tag_group_name']) and !empty($data['tag_group_name'])) ? trim($data['tag_group_name'])   : null;
        self::$dataArray['tag_group_mark']  = (isset($data['tag_group_mark']) and !empty($data['tag_group_mark'])) ? trim($data['tag_group_mark'])   : null;
        self::$dataArray['language']        = (isset($data['language'])       and !empty($data['language']))       ? trim($data['language'])         : null;
        return array_filter(self::$dataArray);
    }
    /**
     * 添加标签组扩展过滤
     * @param array $data
     * @return array
     */
    public static function addTagGroupExtendData(array $data)
    {
        return self::checkData($data);
    }
    /**
     * 编辑更新标签组扩展过滤
     * @param array $data
     * @return array
     */
    public static function editTagGroupExtendData(array $data)
    {
        if(isset($data['tag_group_id'])) unset($data['tag_group_id']);
        return self::checkData($data);
    }
}

?>