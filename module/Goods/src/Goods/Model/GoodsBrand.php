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
 * 商品品牌过滤
 */
class GoodsBrand
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['brand_id']   = (isset($data['brand_id'])   and !empty($data['brand_id']))   ? intval($data['brand_id'])  : null;
        self::$dataArray['brand_sort'] = (isset($data['brand_sort']) and !empty($data['brand_sort'])) ? intval($data['brand_sort']): null;
        return array_filter(self::$dataArray);
    }
    /**
     * 过滤添加商品品牌
     * @param array $data
     * @return array
     */
    public static function addGoodsBrandData(array $data)
    {
        return self::checkData($data);
    }
    /**
     * 过滤更新商品品牌
     * @param array $data
     * @return array
     */
    public static function updateGoodsBrandData (array $data)
    {
        unset($data['brand_id']);
        return self::checkData($data);
    }
}

?>