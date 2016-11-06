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
 * 属性扩展信息
 */
class GoodsAttributeExtend
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['attribute_id']   = (isset($data['attribute_id'])       and !empty($data['attribute_id']))       ? intval($data['attribute_id'])      : null;
        self::$dataArray['attribute_name'] = (isset($data['attribute_name'])     and !empty($data['attribute_name']))     ? trim($data['attribute_name'])      : null;
        self::$dataArray['language']       = (isset($data['language'])           and !empty($data['language']))           ? trim($data['language'])            : null;
        return array_filter(self::$dataArray);
    }
    /**
     * 添加属性扩展过滤
     * @param array $data
     * @return array
     */
    public static function addAttributeExtendData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 更新属性扩展过滤
     * @param array $data
     * @return array
     */
    public static function updateAttributeExtendData (array $data)
    {
        if(isset($data['attribute_id'])) unset($data['attribute_id']);
        if(isset($data['language']))     unset($data['language']);
        return self::checkData($data);
    }
}

?>