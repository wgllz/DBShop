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

namespace Express\Model;

class ExpressIndividuation
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['indiv_id']        = (isset($data['indiv_id'])      and !empty($data['indiv_id']))      ? intval($data['indiv_id'])      : null;
        self::$dataArray['express_id']      = (isset($data['express_id'])    and !empty($data['express_id']))    ? intval($data['express_id'])    : null;
        self::$dataArray['express_area']    = (isset($data['express_area'])  and !empty($data['express_area']))  ? trim($data['express_area'])    : null;
    
        self::$dataArray = array_filter(self::$dataArray);
        
        self::$dataArray['express_price']   = (isset($data['express_price']) and !empty($data['express_price'])) ? trim($data['express_price'])   : 0;
    
        return self::$dataArray;
    }
    /**
     * 添加配送地区过滤
     * @param array $data
     * @return array
     */
    public static function addExpressIndividuationData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 添加更新配送地区过滤
     * @param array $data
     * @return array
     */
    public static function updateExpressIndividuationData (array $data)
    {
        unset($data['indiv_id']);
        return self::checkData($data);
    }
}

?>