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

/** 
 * 商品属性组扩展
 */
class GoodsAttributeGroupExtend
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['attribute_group_id']   = (isset($data['attribute_group_id'])   and !empty($data['attribute_group_id']))   ? intval($data['attribute_group_id'])  : null;
        self::$dataArray['attribute_group_name'] = (isset($data['attribute_group_name']) and !empty($data['attribute_group_name'])) ? trim($data['attribute_group_name'])  : null;
        self::$dataArray['language']             = (isset($data['language'])             and !empty($data['language']))             ? trim($data['language'])              : null;
        return array_filter(self::$dataArray);
    }
    /**
     * 添加属性扩展组过滤
     * @param array $data
     * @return array
     */
    public static function addAttributeGroupExtendData(array $data)
    {
        return self::checkData($data);
    }
    /**
     * 编辑更新属性扩展组过滤
     * @param array $data
     * @return array
     */
    public static function editAttributeGroupExtendData(array $data)
    {
        if(isset($data['attribute_group_id'])) unset($data['attribute_group_id']);
        return self::checkData($data);
    }
}

?>