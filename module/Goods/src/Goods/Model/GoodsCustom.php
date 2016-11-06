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
 * 商品自定义过滤
 */
class GoodsCustom
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['goods_id']       = (isset($data['goods_id'])       and !empty($data['goods_id']))       ? intval($data['goods_id'])     : null;
        self::$dataArray['custom_title']   = (isset($data['custom_title'])   and !empty($data['custom_title']))   ? trim($data['custom_title'])   : null;
        self::$dataArray['custom_content'] = (isset($data['custom_content']) and !empty($data['custom_content'])) ? trim($data['custom_content']) : null;
        self::$dataArray['custom_key']     = (isset($data['custom_key'])     and !empty($data['custom_key']))     ? trim($data['custom_key'])     : null;
        return array_filter(self::$dataArray);
    }
    /**
     * 添加商品自定义过滤
     * @param array $data
     * @return array
     */
    public static function addGoodsCustomData(array $data)
    {
        return self::checkData($data);
    }
}

?>