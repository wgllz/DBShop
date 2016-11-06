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
 * 商品标签扩展
 */
class GoodsTagExtend
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['tag_id']   = (isset($data['tag_id'])   and !empty($data['tag_id']))   ? intval($data['tag_id']) : null;
        self::$dataArray['tag_name'] = (isset($data['tag_name']) and !empty($data['tag_name'])) ? trim($data['tag_name']) : null;
        self::$dataArray['language'] = (isset($data['language']) and !empty($data['language'])) ? trim($data['language']) : null;
        
        return array_filter(self::$dataArray);
    }
    /**
     * 添加标签扩展过滤
     * @param array $data
     * @return array
     */
    public static function addGoodsExtendData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 编辑更新标签扩展过滤
     * @param array $data
     * @return array
     */
    public static function updateGoodsExtendData (array $data)
    {
        return self::checkData($data);
    }
}

?>