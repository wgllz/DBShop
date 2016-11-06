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

class GoodsClassShow
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['class_id']    = (isset($data['class_id'])   and !empty($data['class_id']))   ? intval($data['class_id'])     : null;
        self::$dataArray['show_body']   = (isset($data['show_body'])  and !empty($data['show_body']))  ? trim($data['show_body'])      : null;
        return array_filter(self::$dataArray);
    }
    /**
     * 添加商品分类标签过滤
     * @param array $data
     * @return array
     */
    public static function addGoodsClassTagGroupData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 编辑更新商品分类标签过滤
     * @param array $data
     * @return array
     */
    public static function editGoodsClassTagGroupData (array $data)
    {
        if(isset($data['class_id'])) unset($data['class_id']);
        return self::checkData($data);
    }
}

?>