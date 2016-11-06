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

class GoodsRelated
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['related_id']      = (isset($data['related_id'])       and !empty($data['related_id']))       ? intval($data['related_id'])       : null;
        self::$dataArray['related_goods_id']= (isset($data['related_goods_id']) and !empty($data['related_goods_id'])) ? intval($data['related_goods_id']) : null;
        self::$dataArray['related_sort']    = (isset($data['related_sort'])     and !empty($data['related_sort']))     ? intval($data['related_sort'])     : null;
        self::$dataArray = array_filter(self::$dataArray);
        self::$dataArray['goods_id']        = (isset($data['goods_id'])         and !empty($data['goods_id']))         ? intval($data['goods_id'])         : 0;
        
        return self::$dataArray;
    }
    /**
     * 添加关联商品的过滤
     * @param array $data
     * @return array
     */
    public static function addRelatedGoodsData(array $data)
    {
        return self::checkData($data);
    }
}