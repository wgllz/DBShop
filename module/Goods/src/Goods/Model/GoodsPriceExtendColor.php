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
 * 销售属性 颜色 过滤
 */
class GoodsPriceExtendColor
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['color_value'] = (isset($data['color_value']) and !empty($data['color_value'])) ? trim($data['color_value']) : null;
        self::$dataArray['color_info']  = (isset($data['color_info'])  and !empty($data['color_info']))  ? trim($data['color_info'])  : null;
        self::$dataArray['color_image'] = (isset($data['color_image']) and !empty($data['color_image'])) ? trim($data['color_image']) : null;
        self::$dataArray['extend_id']   = (isset($data['extend_id'])   and !empty($data['extend_id']))   ? intval($data['extend_id']) : null;
        self::$dataArray['goods_id']    = (isset($data['goods_id'])    and !empty($data['goods_id']))    ? intval($data['goods_id'])  : null;
        return array_filter(self::$dataArray);
    }
    /**
     * 过滤商品扩展之销售属性：颜色
     * @param array $data
     * @return array
     */
    public static function addPriceExtendColorData(array $data)
    {
        return self::checkData($data);
    }
}

?>