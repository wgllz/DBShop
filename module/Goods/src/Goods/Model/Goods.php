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
 * 商品信息过滤
 */
class Goods
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['goods_id']                      = (isset($data['goods_id'])                      and !empty($data['goods_id']))                      ? intval($data['goods_id'])                         : null;
        self::$dataArray['goods_item']                    = (isset($data['goods_item'])                    and !empty($data['goods_item']))                    ? trim($data['goods_item'])                         : null;
        self::$dataArray['goods_state']                   = (isset($data['goods_state'])                   and !empty($data['goods_state']))                   ? intval($data['goods_state'])                      : null;
        self::$dataArray['brand_id']                      = (isset($data['brand_id'])                      and !empty($data['brand_id']))                      ? trim($data['brand_id'])                           : null;
        self::$dataArray['goods_stock_state']             = (isset($data['goods_stock_state'])             and !empty($data['goods_stock_state']))             ? intval($data['goods_stock_state'])                : null;
        self::$dataArray['goods_add_time']                = (isset($data['goods_add_time'])                and !empty($data['goods_add_time']))                ? $data['goods_add_time']                           : time();
        self::$dataArray['goods_sort']                    = (isset($data['goods_sort'])                    and !empty($data['goods_sort']))                    ? intval($data['goods_sort'])                       : 255;
        self::$dataArray['goods_out_stock_state']         = (isset($data['goods_out_stock_state'])         and !empty($data['goods_out_stock_state']))         ? intval($data['goods_out_stock_state'])            : null;
        self::$dataArray['goods_tag_str']                 = (isset($data['goods_tag_str'])                 and !empty($data['goods_tag_str']))                 ? trim($data['goods_tag_str'])                      : null;
        self::$dataArray['goods_click']                   = (isset($data['goods_click'])                   and !empty($data['goods_click']))                   ? intval($data['goods_click'])                      : null;
        self::$dataArray['goods_type']                    = (isset($data['goods_type'])                    and !empty($data['goods_type']))                    ? intval($data['goods_type'])                       : null;
        self::$dataArray['virtual_goods_add_state']       = (isset($data['virtual_goods_add_state'])       and !empty($data['virtual_goods_add_state']))       ? intval($data['virtual_goods_add_state'])          : null;

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['attribute_group_id']            = (isset($data['attribute_group_id'])            and !empty($data['attribute_group_id']))            ? intval($data['attribute_group_id'])               : 0;
        self::$dataArray['goods_shop_price']              = (isset($data['goods_shop_price'])              and !empty($data['goods_shop_price']))              ? trim($data['goods_shop_price'])                   : 0;
        self::$dataArray['goods_price']                   = (isset($data['goods_price'])                   and !empty($data['goods_price']))                   ? trim($data['goods_price'])                        : 0;
        self::$dataArray['goods_weight']                  = (isset($data['goods_weight'])                  and !empty($data['goods_weight']))                  ? trim($data['goods_weight'])                       : 0;
        self::$dataArray['goods_start_time']              = (isset($data['goods_start_time'])              and !empty($data['goods_start_time']))              ? strtotime($data['goods_start_time'])              : '';
        self::$dataArray['goods_end_time']                = (isset($data['goods_end_time'])                and !empty($data['goods_end_time']))                ? strtotime($data['goods_end_time'])                : '';
        self::$dataArray['goods_preferential_price']      = (isset($data['goods_preferential_price'])      and !empty($data['goods_preferential_price']))      ? trim($data['goods_preferential_price'])           : 0;
        self::$dataArray['goods_preferential_start_time'] = (isset($data['goods_preferential_start_time']) and !empty($data['goods_preferential_start_time'])) ? strtotime($data['goods_preferential_start_time']) : '';
        self::$dataArray['goods_preferential_end_time']   = (isset($data['goods_preferential_end_time'])   and !empty($data['goods_preferential_end_time']))   ? strtotime($data['goods_preferential_end_time'])   : '';
        self::$dataArray['goods_stock']                   = (isset($data['goods_stock'])                   and !empty($data['goods_stock']))                   ? intval($data['goods_stock'])                      : 0;
        self::$dataArray['goods_out_of_stock_set']        = (isset($data['goods_out_of_stock_set'])        and !empty($data['goods_out_of_stock_set']))        ? intval($data['goods_out_of_stock_set'])           : 0;
        self::$dataArray['goods_cart_buy_min_num']        = (isset($data['goods_cart_buy_min_num'])        and !empty($data['goods_cart_buy_min_num']))        ? intval($data['goods_cart_buy_min_num'])           : 0;
        self::$dataArray['goods_cart_buy_max_num']        = (isset($data['goods_cart_buy_max_num'])        and !empty($data['goods_cart_buy_max_num']))        ? intval($data['goods_cart_buy_max_num'])           : 0;
        self::$dataArray['goods_stock_state_open']        = (isset($data['goods_stock_state_open'])        and !empty($data['goods_stock_state_open']))        ? intval($data['goods_stock_state_open'])           : 0;
        self::$dataArray['goods_class_have_true']         = (isset($data['goods_class_have_true'])         and !empty($data['goods_class_have_true']))         ? intval($data['goods_class_have_true'])            : 0;
        self::$dataArray['virtual_sales']                 = (isset($data['virtual_sales'])                 and !empty($data['virtual_sales']))                 ? intval($data['virtual_sales'])                    : 0;
        self::$dataArray['goods_integral_num']            = (isset($data['goods_integral_num'])            and !empty($data['goods_integral_num']))            ? intval($data['goods_integral_num'])               : 0;

        return self::$dataArray;
    }
    /**
     * 过滤添加商品基本信息
     * @param array $data
     * @return array
     */
    public static function addGoodsData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 过滤更新商品基本信息
     * @param array $data
     * @return array
     */
    public static function updateGoodsData (array $data)
    {
        unset($data['goods_id']);
        unset($data['goods_add_time']);
        
        $data = self::checkData($data);
        
        if(isset($data['brand_id']) and $data['brand_id'] == 'del') $data['brand_id'] = 0;
        
        return $data;
    }
    /**
     * 过滤查询商品信息
     * @param array $data
     * @return array
     */
    public static function whereGoodsData (array $data=array())
    {
        $filter = new \Zend\Filter\HtmlEntities();

        $searchArray = array();
        $searchArray[] = (isset($data['start_goods_id'])        and !empty($data['start_goods_id']))        ? 'dbshop_goods.goods_id >= ' . intval($data['start_goods_id'])            : '';
        $searchArray[] = (isset($data['end_goods_id'])          and !empty($data['end_goods_id']))          ? 'dbshop_goods.goods_id <= ' . intval($data['end_goods_id'])              : '';
        $searchArray[] = (isset($data['goods_item'])            and !empty($data['goods_item']))            ? 'dbshop_goods.goods_item like \'%' . trim($data['goods_item']) . '%\''   : '';
        $searchArray[] = (isset($data['start_goods_price'])     and !empty($data['start_goods_price']))     ? 'dbshop_goods.goods_shop_price >= ' . intval($data['start_goods_price']) : '';
        $searchArray[] = (isset($data['end_goods_price'])       and !empty($data['end_goods_price']))       ? 'dbshop_goods.goods_shop_price <= ' . intval($data['end_goods_price'])   : '';
        $searchArray[] = (isset($data['goods_preferential'])    and !empty($data['goods_preferential']))    ? 'dbshop_goods.goods_preferential_price > 0'                              : '';
        //当goods_state 等于-1时，是检查缺货
        $searchArray[] = (isset($data['goods_state'])           and !empty($data['goods_state']))           ? ($data['goods_state'] == -1 ? 'dbshop_goods.goods_stock=0' : 'dbshop_goods.goods_state = ' . intval($data['goods_state'])) : '';
        $searchArray[] = (isset($data['brand_id'])              and !empty($data['brand_id']))              ? 'dbshop_goods.brand_id = ' . intval($data['brand_id'])                   : '';
        $searchArray[] = (isset($data['attribute_group_id'])    and !empty($data['attribute_group_id']))    ? 'dbshop_goods.attribute_group_id = ' . intval($data['attribute_group_id']): '';
        $searchArray[] = (isset($data['goods_type'])            and !empty($data['goods_type']))            ? 'dbshop_goods.goods_type = ' . intval($data['goods_type'])                : '';
        $searchArray[] = (isset($data['goods_integral'])        and !empty($data['goods_integral']))        ? 'dbshop_goods.goods_integral_num>0'                                       : '';
        $searchArray[] = (isset($data['goods_tag_str'])         and !empty($data['goods_tag_str']))         ? $data['goods_tag_str']                                                    : '';

        $searchArray[] = (isset($data['goods_name'])            and !empty($data['goods_name']))            ? self::checkGoodsNameData($filter->filter(trim($data['goods_name'])))      : '';
        $searchArray[] = (isset($data['class_id'])              and !empty($data['class_id']))              ? 'goods_in.class_id = '. intval($data['class_id'])                         : '';
        $searchArray[] = (isset($data['class_recommend'])       and !empty($data['class_recommend']))       ? 'goods_in.class_recommend = '. intval($data['class_recommend'])           : '';
        
        $searchArray[] = (isset($data['tag_id'])                and !empty($data['tag_id']))                ? 'goods_tag_in.tag_id = '. $data['tag_id']                                 : '';

        $searchArray[] = (isset($data['rand_related_goods'])    and !empty($data['rand_related_goods']))    ? trim($data['rand_related_goods'])                                         : '';//用于前台随机显示相关商品

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
            $goodsNameStr .= 'e.goods_name like \'%' . $value . '%\' and ';
        }
        if(!empty($goodsNameStr)) return substr($goodsNameStr, 0, -5);
    }
}

?>