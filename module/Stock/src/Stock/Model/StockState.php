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

namespace Stock\Model;

class StockState
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['stock_state_id']  = (isset($data['stock_state_id'])   and !empty($data['stock_state_id']))    ? intval($data['stock_state_id'])   : null;
        self::$dataArray['state_sort']      = (isset($data['state_sort'])       and !empty($data['state_sort']))        ? intval($data['state_sort'])       : null;
        self::$dataArray['state_type']      = (isset($data['state_type'])       and !empty($data['state_type']))        ? intval($data['state_type'])       : null;
        self::$dataArray['stock_type_state']= (isset($data['stock_type_state']) and !empty($data['stock_type_state']))  ? intval($data['stock_type_state']) : null;
    
        return array_filter(self::$dataArray);
    }
    public static function addStockStateData (array $data)
    {
        return self::checkData($data);
    }
    public static function updateStockStateData (array $data)
    {
        unset($data['stock_state_id']);
        return self::checkData($data);
    }
}

?>