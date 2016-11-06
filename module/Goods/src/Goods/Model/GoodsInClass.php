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
 * 商品扩展分类过滤
 */
class GoodsInClass
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['goods_id']         = (isset($data['goods_id'])            and !empty($data['goods_id']))          ? intval($data['goods_id'])         : null;
        self::$dataArray['class_id']         = (isset($data['class_id'])            and !empty($data['class_id']))          ? intval($data['class_id'])         : null;
        self::$dataArray['class_goods_sort'] = (isset($data['class_goods_sort'])    and !empty($data['class_goods_sort']))  ? intval($data['class_goods_sort']) : null;
        self::$dataArray = array_filter(self::$dataArray);
        self::$dataArray['class_state']      = (isset($data['class_state'])         and !empty($data['class_state']))       ? intval($data['class_state'])      : 0;
        self::$dataArray['class_recommend']  = (isset($data['class_recommend'])     and !empty($data['class_recommend']))   ? intval($data['class_recommend'])  : 0;
        
        return self::$dataArray;
    }
    /**
     * 过滤添加商品加入分类
     * @param array $data
     * @return array
     */
    public static function addGoodsInClassData(array $data)
    {
        return self::checkData($data);
    }
    
}

?>