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
 * 商品扩展信息过滤
 */
class GoodsPriceExtend
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['extend_id']   = (isset($data['extend_id'])   and !empty($data['extend_id']))   ? intval($data['extend_id']) : null;
        self::$dataArray['extend_name'] = (isset($data['extend_name']) and !empty($data['extend_name'])) ? trim($data['extend_name']) : null;
        self::$dataArray['goods_id']    = (isset($data['goods_id'])    and !empty($data['goods_id']))    ? intval($data['goods_id'])  : null;
        self::$dataArray['extend_type'] = (isset($data['extend_type']) and !empty($data['extend_type'])) ? trim($data['extend_type']) : null;
        self::$dataArray['extend_show_type'] = (isset($data['extend_show_type']) and !empty($data['extend_show_type'])) ? intval($data['extend_show_type']) : null;
        self::$dataArray['language']    = (isset($data['language'])    and !empty($data['language']))    ? trim($data['language'])    : null;
        
        self::$dataArray = array_filter(self::$dataArray);
        
        return self::$dataArray;
    }
    /**
     * 过滤添加商品扩展信息
     * @param array $data
     * @return array
     */
    public static function addGoodsPriceExtendData(array $data)
    {
        return self::checkData($data);
    }
    /**
     * 过滤更新商品扩展信息
     * @param array $data
     * @return array
     */
    public static function updateGoodsPriceExtendData(array $data)
    {
        unset($data['extend_id']);
        unset($data['extend_type']);
        return self::checkData($data);
    }
    
}

?>