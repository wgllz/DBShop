<?php
/**
 * DBShop 电子商务系统
 *
 * ==========================================================================
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2016 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 * ==========================================================================
 *
 * @author    静静的风
 *
 */

namespace Goods\Model;

class GoodsIndex {

    private static $dataArray = array();

    private static function checkData (array $data)
    {
        self::$dataArray['index_id']    = (isset($data['index_id'])     and !empty($data['index_id']))      ? intval($data['index_id'])     : null;
        self::$dataArray['goods_id']    = (isset($data['goods_id'])     and !empty($data['goods_id']))      ? intval($data['goods_id'])     : null;
        self::$dataArray['one_class_id']= (isset($data['one_class_id'])     and !empty($data['one_class_id']))      ? intval($data['one_class_id']) : null;
        self::$dataArray['goods_state'] = (isset($data['goods_state'])  and !empty($data['goods_state']))   ? intval($data['goods_state'])  : 2;
        self::$dataArray['index_body']  = (isset($data['index_body'])   and !empty($data['index_body']))    ? trim($data['index_body'])     : '';

        self::$dataArray['goods_shop_price']    = (isset($data['goods_shop_price'])         and !empty($data['goods_shop_price']))          ? trim($data['goods_shop_price'])       : '0';
        self::$dataArray['goods_name']          = (isset($data['goods_name'])               and !empty($data['goods_name']))                ? trim($data['goods_name'])             : null;
        self::$dataArray['goods_extend_name']   = (isset($data['goods_extend_name'])        and !empty($data['goods_extend_name']))         ? trim($data['goods_extend_name'])      : null;
        self::$dataArray['goods_thumbnail_image']=(isset($data['goods_thumbnail_image'])    and !empty($data['goods_thumbnail_image']))     ? trim($data['goods_thumbnail_image'])  : '';
        self::$dataArray['goods_click']         = (isset($data['goods_click'])              and !empty($data['goods_click']))               ? intval($data['goods_click'])          : 0;
        self::$dataArray['virtual_sales']       = (isset($data['virtual_sales'])            and !empty($data['virtual_sales']))             ? intval($data['virtual_sales'])        : 0;
        self::$dataArray['goods_add_time']      = (isset($data['goods_add_time'])           and !empty($data['goods_add_time']))            ? trim($data['goods_add_time'])         : time();

        return self::$dataArray;
    }
    /**
     * 对商品索引添加进行过滤
     * @param array $data
     * @return array
     */
    public static function addGoodsIndexData(array $data)
    {
        return self::checkData($data);
    }

    /**
     * @param array $data
     */
    public static function whereGoodsData(array $data)
    {
        $filter = new \Zend\Filter\HtmlEntities();

        $searchArray = array();

        $searchArray[] = (isset($data['goods_name'])            and !empty($data['goods_name']))            ? self::checkGoodsNameData($filter->filter(trim($data['goods_name'])))      : '';

        return array_filter($searchArray);
    }
    /**
     * 对商品名称进行处理，如果有空格则进行or处理
     * @param $goodsName
     * @return string
     */
    public static function checkGoodsNameData($goodsName)
    {
        $goodsNameStr = '';
        $array        = explode(' ', $goodsName);
        foreach($array as $value) {
            $goodsNameStr .= 'dbshop_goods.index_body like \'%' . $value . '%\' and ';
        }
        if(!empty($goodsNameStr)) return substr($goodsNameStr, 0, -5);
    }
}