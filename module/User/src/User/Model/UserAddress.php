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

namespace User\Model;

class UserAddress
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['address_id']    = (isset($data['address_id'])    and !empty($data['address_id']))    ? intval($data['address_id']): null;
        self::$dataArray['true_name']     = (isset($data['true_name'])     and !empty($data['true_name']))     ? trim($data['true_name'])   : null;
        self::$dataArray['region_id']     = (isset($data['region_id'])     and !empty($data['region_id']))     ? intval($data['region_id']) : null;
        self::$dataArray['region_value']  = (isset($data['region_value'])  and !empty($data['region_value']))  ? trim($data['region_value']): null;
        self::$dataArray['address']       = (isset($data['address'])       and !empty($data['address']))       ? trim($data['address'])     : null;
        self::$dataArray['mod_phone']     = (isset($data['mod_phone'])     and !empty($data['mod_phone']))     ? trim($data['mod_phone'])   : null;
        self::$dataArray['user_id']       = (isset($data['user_id'])       and !empty($data['user_id']))       ? intval($data['user_id'])   : null;
    
        self::$dataArray = array_filter(self::$dataArray);
        
        self::$dataArray['addr_default']  = (isset($data['addr_default'])  and !empty($data['addr_default']))  ? intval($data['addr_default']) : 0;
        self::$dataArray['zip_code']      = (isset($data['zip_code'])      and !empty($data['zip_code']))      ? trim($data['zip_code'])    : '';
        self::$dataArray['tel_area_code'] = (isset($data['tel_area_code']) and !empty($data['tel_area_code'])) ? trim($data['tel_area_code']): '';
        self::$dataArray['tel_phone']     = (isset($data['tel_phone'])     and !empty($data['tel_phone']))     ? trim($data['tel_phone'])   : '';
        self::$dataArray['tel_ext']       = (isset($data['tel_ext'])       and !empty($data['tel_ext']))       ? trim($data['tel_ext'])     : '';
        
        return self::$dataArray;
    }
    /**
     *添加收货地址过滤
     * @param array $data
     * @return array
     */
    public static function addAddressData (array $data)
    {
        return self::checkData($data);
    }
    /**
     *更新收货地址过滤
     * @param array $data
     * @return array
     */
    public static function updateAddressData (array $data)
    {
        unset($data['address_id']);
        unset($data['user_id']);

        return self::checkData($data);
    }
}

?>