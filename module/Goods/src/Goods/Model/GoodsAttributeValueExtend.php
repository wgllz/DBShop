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
 * 属性值扩展
 */
class GoodsAttributeValueExtend
{
    private static $dataArray = array();

    private static function checkData (array $data)
    {
        self::$dataArray['value_id']     = (isset($data['value_id'])     and !empty($data['value_id']))     ? intval($data['value_id'])     : null;
        self::$dataArray['attribute_id'] = (isset($data['attribute_id']) and !empty($data['attribute_id'])) ? intval($data['attribute_id']) : null;
        self::$dataArray['value_name']   = (isset($data['value_name'])   and !empty($data['value_name']))   ? trim($data['value_name'])     : null;
        self::$dataArray['language']     = (isset($data['language'])     and !empty($data['language']))     ? trim($data['language'])       : null;
        return array_filter(self::$dataArray);
    }
    /**
     * 添加属性值扩展过滤
     * @param array $data
     * @return array
     */
    public static function addAttributeValueExtendData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 编辑更新属性值扩展过滤
     * @param array $data
     * @return array
     */
    public static function updateAttributeValueExtendData (array $data)
    {
        if(isset($data['value_id'])) unset($data['value_id']);
        if(isset($data['language'])) unset($data['language']);
        return self::checkData($data);
    }
}

?>