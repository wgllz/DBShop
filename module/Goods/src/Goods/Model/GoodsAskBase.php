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

//暂时不使用，以后需要时再次使用

namespace Goods\Model;

class GoodsAskBase
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['goods_id']        = (isset($data['goods_id'])        and !empty($data['goods_id']))        ? intval($data['goods_id'])      : null;
        self::$dataArray['ask_last_writer'] = (isset($data['ask_last_writer']) and !empty($data['ask_last_writer'])) ? trim($data['ask_last_writer']) : null;
        self::$dataArray['ask_last_time']   = (isset($data['ask_last_time'])   and !empty($data['ask_last_time']))   ? trim($data['ask_last_time'])   : null;
        self::$dataArray['ask_count']       = (isset($data['ask_count'])       and !empty($data['ask_count']))       ? intval($data['ask_count'])     : null;
        
        return array_filter(self::$dataArray);
    }

    /**
     * 客户咨询信息过滤
     * @param array $data
     * @return array
     */
    public static function addGoodsAskBaseData(array $data)
    {
        return self::checkData($data);
    }

    /**
     * 更新客户咨询信息过滤
     * @param array $data
     * @return array
     */
    public static function updateGoodsAskBaseData(array $data)
    {
        $data = self::checkData($data);
        if(isset($data['goods_id'])) unset($data['goods_id']);
        
        return $data;
    }
}

?>