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
class GoodsExtend
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['goods_id']          = (isset($data['goods_id'])          and !empty($data['goods_id']))          ? intval($data['goods_id'])        : null;
        self::$dataArray['goods_name']        = (isset($data['goods_name'])        and !empty($data['goods_name']))        ? trim($data['goods_name'])        : null;
        self::$dataArray['goods_image_id']    = (isset($data['default_image'])     and !empty($data['default_image']))     ? $data['default_image']           : ((isset($data['image_more']) and count($data['image_more']) > 0) ? $data['image_more'][0] : null);
        self::$dataArray['language']          = (isset($data['language'])          and !empty($data['language']))          ? $data['language']                : null;
        
        self::$dataArray = array_filter(self::$dataArray);
        
        self::$dataArray['goods_info']        = (isset($data['goods_info'])        and !empty($data['goods_info']))        ? trim($data['goods_info'])        : '';
        self::$dataArray['goods_body']        = (isset($data['goods_body'])        and !empty($data['goods_body']))        ? trim($data['goods_body'])        : '';
        self::$dataArray['goods_extend_name'] = (isset($data['goods_extend_name']) and !empty($data['goods_extend_name'])) ? trim($data['goods_extend_name']) : '';
        self::$dataArray['goods_keywords']    = (isset($data['goods_keywords'])    and !empty($data['goods_keywords']))    ? trim($data['goods_keywords'])    : '';
        self::$dataArray['goods_description'] = (isset($data['goods_description']) and !empty($data['goods_description'])) ? trim($data['goods_description']) : '';
        
        return self::$dataArray;
    }
    /**
     * 过滤添加商品扩展信息
     * @param array $data
     * @return array
     */
    public static function addGoodsExtendData(array $data)
    {
        return self::checkData($data);
    }
    /**
     * 过滤更新商品扩展信息
     * @param array $data
     * @return array
     */
    public static function updateGoodsExtendData(array $data)
    {
        unset($data['goods_id']);
        
        return self::checkData($data);
    }
}

?>