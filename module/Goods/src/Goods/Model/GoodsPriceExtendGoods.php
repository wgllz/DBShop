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
 * 商品扩展信息过滤（相当于规格商品包含价格）
 */
class GoodsPriceExtendGoods
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['goods_color']        = (isset($data['goods_color'])        and !empty($data['goods_color']))        ? trim($data['goods_color'])          : null;
        self::$dataArray['goods_size']         = (isset($data['goods_size'])         and !empty($data['goods_size']))         ? trim($data['goods_size'])           : null;
        self::$dataArray['goods_extend_item']  = (isset($data['goods_extend_item'])  and !empty($data['goods_extend_item']))  ? trim($data['goods_extend_item'])    : null;
        self::$dataArray['goods_id']           = (isset($data['goods_id'])           and !empty($data['goods_id']))           ? intval($data['goods_id'])           : null;
        
        self::$dataArray = array_filter(self::$dataArray);
        
        self::$dataArray['goods_extend_price'] = (isset($data['goods_extend_price']) and !empty($data['goods_extend_price'])) ? trim($data['goods_extend_price'])   : 0;
        self::$dataArray['goods_extend_stock'] = (isset($data['goods_extend_stock']) and !empty($data['goods_extend_stock'])) ? intval($data['goods_extend_stock']) : 0;
        
        return self::$dataArray;
    }
    /**
     * 过滤商品扩展信息，（颜色|尺寸|价格）
     * @param array $data
     * @return array
     */
    public static function addGoodsPriceExtendGoodsData(array $data)
    {
        return self::checkData($data);
    }
}

?>