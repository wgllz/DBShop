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

class GoodsCombination
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['combination_id']      = (isset($data['combination_id'])       and !empty($data['combination_id']))       ? intval($data['combination_id'])       : null;
        self::$dataArray['combination_goods_id']= (isset($data['combination_goods_id']) and !empty($data['combination_goods_id'])) ? intval($data['combination_goods_id']) : null;
        self::$dataArray['combination_sort']    = (isset($data['combination_sort'])     and !empty($data['combination_sort']))     ? intval($data['combination_sort'])     : null;
        self::$dataArray = array_filter(self::$dataArray);
        self::$dataArray['goods_id']        = (isset($data['goods_id'])         and !empty($data['goods_id']))         ? intval($data['goods_id'])         : 0;
    
        return self::$dataArray;
    }
    /**
     * 添加关联商品过滤
     * @param array $data
     * @return array
     */
    public static function addCombinationGoodsData(array $data)
    {
        return self::checkData($data);
    }
}
