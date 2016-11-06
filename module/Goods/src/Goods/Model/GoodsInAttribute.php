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
 * 商品对应属性
 */
class GoodsInAttribute
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['goods_id']        = (isset($data['goods_id'])       and !empty($data['goods_id']))       ? intval($data['goods_id'])      : null;
        self::$dataArray['attribute_id']    = (isset($data['attribute_id'])   and !empty($data['attribute_id']))   ? intval($data['attribute_id'])  : null;
        self::$dataArray['attribute_body']  = (isset($data['attribute_body']) and !empty($data['attribute_body'])) ? trim($data['attribute_body'])  : null;
        return array_filter(self::$dataArray);
    }
    /**
     * 添加商品属性值到商品信息的过滤
     * @param array $data
     * @return array
     */
    public static function addGoodsInAttributeData (array $data)
    {
        return self::checkData($data);
    }
}

?>