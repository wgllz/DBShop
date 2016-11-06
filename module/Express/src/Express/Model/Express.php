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

class Express
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['express_id']   = (isset($data['express_id'])   and !empty($data['express_id']))   ? intval($data['express_id'])   : null;
        self::$dataArray['express_name'] = (isset($data['express_name']) and !empty($data['express_name'])) ? trim($data['express_name'])   : null;
        
        
        self::$dataArray['express_sort'] = (isset($data['express_sort']) and !empty($data['express_sort'])) ? intval($data['express_sort']) : null;
        self::$dataArray['express_set']  = (isset($data['express_set'])  and !empty($data['express_set']))  ? trim($data['express_set'])    : null;
        self::$dataArray['cash_on_delivery']  = (isset($data['cash_on_delivery'])  and !empty($data['cash_on_delivery']))  ? intval($data['cash_on_delivery'])    : null;
        
        self::$dataArray = array_filter(self::$dataArray);
        
        self::$dataArray['express_price']= (isset($data['express_price']) and !empty($data['express_price']) and $data['express_set']=='T') ? trim($data['express_price']) : 0;
        self::$dataArray['express_state']= (isset($data['express_state']) and !empty($data['express_state'])) ? intval($data['express_state']) : 0;
        self::$dataArray['express_info'] = (isset($data['express_info']) and !empty($data['express_info'])) ? trim($data['express_info'])   : '';
        self::$dataArray['express_url']  = (isset($data['express_url'])  and !empty($data['express_url']))  ? trim($data['express_url'])    : '';
        self::$dataArray['express_name_code'] = (isset($data['express_name_code'])  and !empty($data['express_name_code']))  ? trim($data['express_name_code'])    : '';
        
        return self::$dataArray;
    }
    /**
     * 添加配送信息过滤
     * @param array $data
     * @return array
     */
    public static function addExpressData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 更新配送信息过滤
     * @param array $data
     * @return array
     */
    public static function updateExpressData (array $data)
    {
        unset($data['express_id']);
        
        return self::checkData($data);
    }
}

?>