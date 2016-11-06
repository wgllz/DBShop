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

class GoodsClass
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['class_id']           = (isset($data['class_id'])           and !empty($data['class_id']))           ? intval($data['class_id'])         : null;
        self::$dataArray['class_name']         = (isset($data['class_name'])         and !empty($data['class_name']))         ? trim($data['class_name'])         : null;
        self::$dataArray['class_path']         = (isset($data['class_path'])         and !empty($data['class_path']))         ? trim($data['class_path'])         : null;
        self::$dataArray['class_sort']         = (isset($data['class_sort'])         and !empty($data['class_sort']))         ? intval($data['class_sort'])       : null;
        self::$dataArray['class_icon']         = (isset($data['class_icon'])         and !empty($data['class_icon']))         ? trim($data['class_icon'])         : null;
        self::$dataArray['class_image']        = (isset($data['class_image'])        and !empty($data['class_image']))        ? trim($data['class_image'])        : null;
        
        self::$dataArray = array_filter(self::$dataArray);
        
        self::$dataArray['class_top_id']       = (isset($data['class_top_id'])       and !empty($data['class_top_id']))       ? intval($data['class_top_id'])     : 0;
        self::$dataArray['class_state']        = (isset($data['class_state'])        and !empty($data['class_state']))        ? intval($data['class_state'])      : 0;
        self::$dataArray['class_info']         = (isset($data['class_info'])         and !empty($data['class_info']))         ? trim($data['class_info'])         : '';
        self::$dataArray['class_title_extend'] = (isset($data['class_title_extend']) and !empty($data['class_title_extend'])) ? trim($data['class_title_extend']) : '';
        self::$dataArray['class_keywords']     = (isset($data['class_keywords'])     and !empty($data['class_keywords']))     ? trim($data['class_keywords'])     : '';
        self::$dataArray['class_description']  = (isset($data['class_description'])  and !empty($data['class_description']))  ? trim($data['class_description'])  : '';
        
        return self::$dataArray;
    }
    /**
     * 添加商品分类过滤
     * @param array $data
     * @return array
     */
    public static function addGoodsClassData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 编辑更新商品分类过滤
     * @param array $data
     * @return array
     */
    public static function updateGoodsClassData (array $data)
    {
        unset($data['class_id']);
        $data = self::checkData($data);
        if(isset($data['class_icon']) and $data['class_icon'] == 'del')   $data['class_icon'] = '';
        if(isset($data['class_image']) and $data['class_image'] == 'del') $data['class_image'] = '';
        
        return $data;       
    }
}

?>