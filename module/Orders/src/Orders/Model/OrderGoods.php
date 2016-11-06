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

namespace Orders\Model;

class OrderGoods
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['order_goods_id']    = (isset($data['order_goods_id'])    and !empty($data['order_goods_id']))     ? intval($data['order_goods_id'])   : null;
        self::$dataArray['order_id']          = (isset($data['order_id'])          and !empty($data['order_id']))           ? intval($data['order_id'])         : null;
        self::$dataArray['goods_id']          = (isset($data['goods_id'])          and !empty($data['goods_id']))           ? intval($data['goods_id'])         : null;
        self::$dataArray['class_id']          = (isset($data['class_id'])          and !empty($data['class_id']))           ? intval($data['class_id'])         : null;
        self::$dataArray['goods_item']        = (isset($data['goods_item'])        and !empty($data['goods_item']))         ? trim($data['goods_item'])         : null;
        self::$dataArray['goods_name']        = (isset($data['goods_name'])        and !empty($data['goods_name']))         ? trim($data['goods_name'])         : null;
        self::$dataArray['goods_extend_info'] = (isset($data['goods_extend_info']) and !empty($data['goods_extend_info']))  ? trim($data['goods_extend_info'])  : null;
        self::$dataArray['goods_color']       = (isset($data['goods_color'])       and !empty($data['goods_color']))        ? trim($data['goods_color'])        : null;
        self::$dataArray['goods_size']        = (isset($data['goods_size'])        and !empty($data['goods_size']))         ? trim($data['goods_size'])         : null;
        self::$dataArray['goods_shop_price']  = (isset($data['goods_shop_price'])  and !empty($data['goods_shop_price']))   ? trim($data['goods_shop_price'])   : null;
        self::$dataArray['buy_num']           = (isset($data['buy_num'])           and !empty($data['buy_num']))            ? intval($data['buy_num'])          : null;
        self::$dataArray['goods_image']       = (isset($data['goods_image'])       and !empty($data['goods_image']))        ? trim($data['goods_image'])        : null;
        self::$dataArray['goods_amount']      = (isset($data['goods_amount'])      and !empty($data['goods_amount']))       ? trim($data['goods_amount'])       : null;
        self::$dataArray['buyer_id']          = (isset($data['buyer_id'])          and !empty($data['buyer_id']))           ? trim($data['buyer_id'])           : null;
        self::$dataArray['comment_state']     = (isset($data['comment_state'])     and !empty($data['comment_state']))      ? trim($data['comment_state'])      : null;
        self::$dataArray['goods_type']        = (isset($data['goods_type'])        and !empty($data['goods_type']))         ? intval($data['goods_type'])       : null;

        self::$dataArray = array_filter(self::$dataArray);
    
        self::$dataArray['goods_count_weight']= (isset($data['goods_count_weight'])and !empty($data['goods_count_weight'])) ? trim($data['goods_count_weight']) : 0;
        
        return self::$dataArray;
    }
    /**
     * 添加订单商品过滤
     * @param array $data
     * @return array
     */
    public static function addOrderGoodsData (array $data)
    {
        return self::checkData($data);
    }
}