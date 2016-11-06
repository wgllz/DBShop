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
 * 商品属性组
 */
class GoodsAttributeGroup
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['attribute_group_id']   = (isset($data['attribute_group_id'])   and !empty($data['attribute_group_id']))   ? intval($data['attribute_group_id'])  : null;
        self::$dataArray['attribute_group_sort'] = (isset($data['attribute_group_sort']) and !empty($data['attribute_group_sort'])) ? intval($data['attribute_group_sort']): null;
        return array_filter(self::$dataArray);
    }
    /**
     * 添加属性组过滤
     * @param array $data
     * @return array
     */
    public static function addAttributeGroupData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 更新编辑属性组过滤
     * @param array $data
     * @return array
     */
    public static function editAttributeGroupData (array $data)
    {
        if(isset($data['attribute_group_id'])) unset($data['attribute_group_id']);
        return self::checkData($data);
    }
}

?>