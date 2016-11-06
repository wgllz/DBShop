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

class StockStateExtend
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['stock_state_id']    = (isset($data['stock_state_id'])   and !empty($data['stock_state_id']))    ? intval($data['stock_state_id'])   : null;
        self::$dataArray['stock_state_name']  = (isset($data['stock_state_name']) and !empty($data['stock_state_name']))  ? trim($data['stock_state_name'])   : null;
        self::$dataArray['language']          = (isset($data['language'])         and !empty($data['language']))          ? trim($data['language'])           : null;
    
        return array_filter(self::$dataArray);
    }
    public static function addStockStateExtendData (array $data)
    {
        return self::checkData($data);
    }
    public static function updateStockStateExtendData (array $data)
    {
        unset($data['stock_state_id']);
        return self::checkData($data);
    }
}

?>