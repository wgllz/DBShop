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

class OrderDeliveryAddress
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['order_id']            = (isset($data['order_id'])             and !empty($data['order_id']))              ? intval($data['order_id'])     : null;
        self::$dataArray['delivery_name']       = (isset($data['delivery_name'])        and !empty($data['delivery_name']))         ? trim($data['delivery_name'])  : null;
        self::$dataArray['region_id']           = (isset($data['region_id'])            and !empty($data['region_id']))             ? intval($data['region_id'])    : null;
        self::$dataArray['region_info']         = (isset($data['region_info'])          and !empty($data['region_info']))           ? trim($data['region_info'])    : null;
        self::$dataArray['region_address']      = (isset($data['region_address'])       and !empty($data['region_address']))        ? trim($data['region_address']) : null;
        self::$dataArray['zip_code']            = (isset($data['zip_code'])             and !empty($data['zip_code']))              ? trim($data['zip_code'])       : null;
        self::$dataArray['tel_phone']           = (isset($data['tel_phone'])            and !empty($data['tel_phone']))             ? trim($data['tel_phone'])      : null;
        self::$dataArray['mod_phone']           = (isset($data['mod_phone'])            and !empty($data['mod_phone']))             ? trim($data['mod_phone'])      : null;
        self::$dataArray['express_name']        = (isset($data['express_name'])         and !empty($data['express_name']))          ? trim($data['express_name'])   : null;
        self::$dataArray['express_fee']         = (isset($data['express_fee'])          and !empty($data['express_fee']))           ? trim($data['express_fee'])    : null;
        self::$dataArray['express_time_info']   = (isset($data['express_time_info'])    and !empty($data['express_time_info']))     ? trim($data['express_time_info'])  : null;
        self::$dataArray['express_number']      = (isset($data['express_number'])       and !empty($data['express_number']))        ? trim($data['express_number']) : null;
        self::$dataArray['express_id']          = (isset($data['express_id'])           and !empty($data['express_id']))            ? intval($data['express_id'])   : null;
    
        self::$dataArray = array_filter(self::$dataArray);
    
    
        return self::$dataArray;
    }
    public static function addDeliveryAddressData (array $data)
    {
        return self::checkData($data);
    }
}