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
 * 标签对应商品
 */
class GoodsTagInGoods
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['tag_id']          = (isset($data['tag_id'])           and !empty($data['tag_id']))        ? intval($data['tag_id'])         : null;
        self::$dataArray['goods_id']        = (isset($data['goods_id'])         and !empty($data['goods_id']))      ? intval($data['goods_id'])       : null;
        self::$dataArray['tag_goods_sort']  = (isset($data['tag_goods_sort'])   and !empty($data['tag_goods_sort']))? intval($data['tag_goods_sort']) : null;
        
        return array_filter(self::$dataArray);
    }
    /**
     * 添加标签商品过滤
     * @param array $data
     * @return array
     */
    public static function addTagGoodsData (array $data)
    {
        return self::checkData($data);
    }
}

?>