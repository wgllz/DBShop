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

class GoodsCommentBase
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['goods_id']            = (isset($data['goods_id'])       and !empty($data['goods_id']))       ? intval($data['goods_id'])      : null;
        self::$dataArray['comment_last_writer'] = (isset($data['comment_writer']) and !empty($data['comment_writer'])) ? trim($data['comment_writer'])  : null;
        self::$dataArray['comment_last_time']   = (isset($data['comment_time'])   and !empty($data['comment_time']))   ? trim($data['comment_time'])    : null;
        self::$dataArray['comment_count']       = (isset($data['comment_count'])  and !empty($data['comment_count']))  ? intval($data['comment_count']) : null;
        return array_filter(self::$dataArray);
    }
    /**
     * 添加基础商品评价过滤
     * @param array $data
     * @return array
     */
    public static function addGoodsCommentBaseData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 编辑更新基础商品评价过滤
     * @param array $data
     * @return array
     */
    public static function updateGoodscommentBaseData (array $data)
    {
        return self::checkData($data);
    }
}

?>