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

class GoodsAttributeValue
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['value_id']           = (isset($data['value_id'])           and !empty($data['value_id']))           ? intval($data['value_id'])           : null;
        self::$dataArray['attribute_id']       = (isset($data['attribute_id'])       and !empty($data['attribute_id']))       ? intval($data['attribute_id'])       : null;
        self::$dataArray['attribute_group_id'] = (isset($data['attribute_group_id']) and !empty($data['attribute_group_id'])) ? intval($data['attribute_group_id']) : null;
        self::$dataArray['value_sort']         = (isset($data['value_sort'])         and !empty($data['value_sort']))         ? intval($data['value_sort'])         : null;
        return array_filter(self::$dataArray);
    }
    /**
     * 添加属性值过滤
     * @param array $data
     * @return array
     */
    public static function addAttributeValueData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 更新编辑属性值过滤
     * @param array $data
     * @return array
     */
    public static function updateAttributeValueData (array $data)
    {
        if(isset($data['value_id'])) unset($data['value_id']);
        return self::checkData($data);
    }
}

?>