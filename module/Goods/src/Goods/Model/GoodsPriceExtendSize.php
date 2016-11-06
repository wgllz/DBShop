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
 * 销售属性 尺寸 过滤
 */
class GoodsPriceExtendSize
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['size_value'] = (isset($data['size_value']) and !empty($data['size_value'])) ? trim($data['size_value']) : null;
        self::$dataArray['size_info']  = (isset($data['size_info'])  and !empty($data['size_info']))  ? trim($data['size_info'])  : null;
        self::$dataArray['extend_id']  = (isset($data['extend_id'])  and !empty($data['extend_id']))  ? intval($data['extend_id']): null;
        self::$dataArray['goods_id']   = (isset($data['goods_id'])   and !empty($data['goods_id']))   ? intval($data['goods_id']) : null;
        return array_filter(self::$dataArray);
    }
    /**
     * 过滤商品销售属性 尺寸
     * @param array $data
     * @return array
     */
    public static function addGoodsPriceExtendSizeData(array $data)
    {
        return self::checkData($data);
    }
}

?>