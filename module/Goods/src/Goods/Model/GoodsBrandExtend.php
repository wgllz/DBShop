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
 * 商品品牌扩展信息过滤
 */
class GoodsBrandExtend
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['brand_id']           = (isset($data['brand_id'])           and !empty($data['brand_id']))           ? intval($data['brand_id'])         : null;
        self::$dataArray['brand_name']         = (isset($data['brand_name'])         and !empty($data['brand_name']))         ? trim($data['brand_name'])         : null;
        self::$dataArray['brand_logo']         = (isset($data['brand_logo'])         and !empty($data['brand_logo']))         ? trim($data['brand_logo'])         : null;
        self::$dataArray['language']           = (isset($data['language'])           and !empty($data['language']))           ? trim($data['language'])           : null;
        
        self::$dataArray = array_filter(self::$dataArray);
        
        self::$dataArray['brand_info']         = (isset($data['brand_info'])         and !empty($data['brand_info']))         ? trim($data['brand_info'])         : '';
        self::$dataArray['brand_title_extend'] = (isset($data['brand_title_extend']) and !empty($data['brand_title_extend'])) ? trim($data['brand_title_extend']) : '';
        self::$dataArray['brand_keywords']     = (isset($data['brand_keywords'])     and !empty($data['brand_keywords']))     ? trim($data['brand_keywords'])     : '';
        self::$dataArray['brand_description']  = (isset($data['brand_description'])  and !empty($data['brand_description']))  ? intval($data['brand_description']): '';
        
        return self::$dataArray;
    }
    /**
     * 过滤添加商品品牌扩展信息
     * @param array $data
     * @return array
     */
    public static function addGoodsBrandExtendData(array $data)
    {
        return self::checkData($data);
    }
    /**
     * 过滤更新商品品牌扩展信息
     * @param array $data
     * @return array
     */
    public static function updateGoodsBrandExtendData (array $data)
    {
        unset($data['brand_id']);
        $data = self::checkData($data);
        if(isset($data['brand_logo']) and $data['brand_logo'] == 'del') $data['brand_logo'] = '';
        
        return $data;
    }
}

?>