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

namespace Currency\Model;

class Currency
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['currency_id']      = (isset($data['currency_id'])      and !empty($data['currency_id']))      ? intval($data['currency_id'])      : null;
        self::$dataArray['currency_name']    = (isset($data['currency_name'])    and !empty($data['currency_name']))    ? trim($data['currency_name'])      : null;
        self::$dataArray['currency_code']    = (isset($data['currency_code'])    and !empty($data['currency_code']))    ? trim($data['currency_code'])      : null;
        self::$dataArray['currency_symbol']  = (isset($data['currency_symbol'])  and !empty($data['currency_symbol']))  ? trim($data['currency_symbol'])    : null;
        self::$dataArray['currency_type']    = (isset($data['currency_type'])    and !empty($data['currency_type']))    ? intval($data['currency_type'])    : null;
        self::$dataArray['currency_rate']    = (isset($data['currency_rate'])    and !empty($data['currency_rate']))    ? trim($data['currency_rate'])      : null;

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['currency_state']   = (isset($data['currency_state'])   and !empty($data['currency_state']))   ? intval($data['currency_state'])   : 0;
        self::$dataArray['currency_decimal'] = (isset($data['currency_decimal']) and !empty($data['currency_decimal'])) ? intval($data['currency_decimal']) : 0;
        self::$dataArray['currency_unit']    = (isset($data['currency_unit'])    and !empty($data['currency_unit']))    ? trim($data['currency_unit'])      : '';
        
        return self::$dataArray;
    }
    /**
     * 添加货币过滤
     * @param array $data
     * @return array
     */
    public static function addCurrencyData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 更新货币过滤
     * @param array $data
     * @return array
     */
    public static function updateCurrencyData (array $data)
    {
        $data = self::checkData($data);
        if(!isset($data['currency_state']) and isset($data['currency_id']) and $data['currency_id'] != 1) $data['currency_state'] = 0;
        
        unset($data['currency_id']);
        
        return $data;
    }
}

?>