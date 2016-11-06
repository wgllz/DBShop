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

class VirtualGoods
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['virtual_goods_id']        = (isset($data['virtual_goods_id'])       and !empty($data['virtual_goods_id']))        ? intval($data['virtual_goods_id'])     : null;
        self::$dataArray['virtual_goods_account']   = (isset($data['virtual_goods_account'])  and !empty($data['virtual_goods_account']))   ? trim($data['virtual_goods_account'])  : null;
        self::$dataArray['virtual_goods_account_type']   = (isset($data['virtual_goods_account_type'])  and !empty($data['virtual_goods_account_type']))   ? intval($data['virtual_goods_account_type'])  : null;
        self::$dataArray['virtual_goods_password']  = (isset($data['virtual_goods_password']) and !empty($data['virtual_goods_password']))  ? trim($data['virtual_goods_password']) : null;
        self::$dataArray['virtual_goods_password_type']  = (isset($data['virtual_goods_password_type']) and !empty($data['virtual_goods_password_type']))  ? intval($data['virtual_goods_password_type']) : null;
        self::$dataArray['virtual_goods_end_time']  = (isset($data['virtual_goods_end_time']) and !empty($data['virtual_goods_end_time']))  ? strtotime($data['virtual_goods_end_time']) : null;
        self::$dataArray['virtual_goods_state']     = (isset($data['virtual_goods_state'])    and !empty($data['virtual_goods_state']))     ? intval($data['virtual_goods_state'])  : null;
        self::$dataArray['order_sn']                = (isset($data['order_sn'])               and !empty($data['order_sn']))                ? trim($data['order_sn'])               : null;
        self::$dataArray['order_id']                = (isset($data['order_id'])               and !empty($data['order_id']))                ? intval($data['order_id'])             : null;
        self::$dataArray['goods_id']                = (isset($data['goods_id'])               and !empty($data['goods_id']))                ? intval($data['goods_id'])             : null;
        self::$dataArray['user_id']                 = (isset($data['user_id'])                and !empty($data['user_id']))                 ? intval($data['user_id'])              : null;
        self::$dataArray['user_name']               = (isset($data['user_name'])              and !empty($data['user_name']))               ? intval($data['user_name'])            : null;

        self::$dataArray = array_filter(self::$dataArray);


        return self::$dataArray;
    }
    /**
     * 虚拟商品过滤
     * @param array $data
     * @return array
     */
    public static function addVirtualGoodsData(array $data)
    {
        return self::checkData($data);
    }

    /**
     * 更新虚拟商品过滤
     * @param array $data
     * @return array
     */
    public static function updateVirtualGoodsData(array $data)
    {
        unset($data['virtual_goods_id']);
        $data = self::checkData($data);
        return $data;
    }
}

?>