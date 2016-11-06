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
 * 商品图片过滤
 */
class GoodsImage
{

    private static $dataArray = array();

    private static function checkData (array $data)
    {
        self::$dataArray['goods_image_id']        = (isset($data['goods_image_id'])        and !empty($data['goods_image_id']))        ? intval($data['goods_image_id'])     : null;
        self::$dataArray['goods_id']              = (isset($data['goods_id'])              and !empty($data['goods_id']))              ? intval($data['goods_id'])           : 0;
        self::$dataArray['goods_title_image']     = (isset($data['goods_title_image'])     and !empty($data['goods_title_image']))     ? trim($data['goods_title_image'])    : null;
        self::$dataArray['goods_thumbnail_image'] = (isset($data['goods_thumbnail_image']) and !empty($data['goods_thumbnail_image'])) ? trim($data['goods_thumbnail_image']): null;
        self::$dataArray['goods_watermark_image'] = (isset($data['goods_watermark_image']) and !empty($data['goods_watermark_image'])) ? trim($data['goods_watermark_image']): null;
        self::$dataArray['goods_source_image']    = (isset($data['goods_source_image'])    and !empty($data['goods_source_image']))    ? trim($data['goods_source_image'])   : null;
        self::$dataArray['image_sort']            = (isset($data['image_sort'])            and !empty($data['image_sort']))            ? intval($data['image_sort'])         : 0;
        self::$dataArray['language']              = (isset($data['language'])              and !empty($data['language']))              ? trim($data['language'])             : null;
        self::$dataArray['image_slide']           = (isset($data['image_slide'])           and !empty($data['image_slide']))           ? intval($data['image_slide'])        : 0;

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['editor_session_str']    = (isset($data['editor_session_str'])    and !empty($data['editor_session_str']))    ? trim($data['editor_session_str'])   : null;

        return self::$dataArray;
    }
    /**
     * 过滤添加商品图片
     * @param array $data
     * @return array
     */
    public static function addGoodsImageData(array $data)
    {
        return self::checkData($data);
    }
    /**
     * 过滤更新商品图片
     * @param array $data
     * @return array
     */
    public static function updateGoodsImageData(array $data)
    {
        return self::checkData($data);
    }
    
}

?>