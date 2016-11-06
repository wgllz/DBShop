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

class GoodsComment
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['comment_id']          = (isset($data['comment_id'])         and !empty($data['comment_id']))         ? intval($data['comment_id'])         : null;
        self::$dataArray['comment_writer']      = (isset($data['comment_writer'])     and !empty($data['comment_writer']))     ? trim($data['comment_writer'])       : null;
        self::$dataArray['comment_body']        = (isset($data['comment_body'])       and !empty($data['comment_body']))       ? trim($data['comment_body'])         : null;
        self::$dataArray['goods_evaluation']    = (isset($data['goods_evaluation'])   and !empty($data['goods_evaluation']))   ? intval($data['goods_evaluation'])   : null;
        self::$dataArray['comment_time']        = (isset($data['comment_time'])       and !empty($data['comment_time']))       ? trim($data['comment_time'])         : null;
        self::$dataArray['comment_show_state']  = (isset($data['comment_show_state']) and !empty($data['comment_show_state'])) ? intval($data['comment_show_state']) : null;
        self::$dataArray['reply_body']          = (isset($data['reply_body'])         and !empty($data['reply_body']))         ? trim($data['reply_body'])           : null;
        self::$dataArray['reply_time']          = (isset($data['reply_time'])         and !empty($data['reply_time']))         ? trim($data['reply_time'])           : null;
        self::$dataArray['reply_writer']        = (isset($data['reply_writer'])       and !empty($data['reply_writer']))       ? trim($data['reply_writer'])         : null;
        self::$dataArray['goods_id']            = (isset($data['goods_id'])           and !empty($data['goods_id']))           ? intval($data['goods_id'])           : null;
        self::$dataArray['order_goods_id']      = (isset($data['order_goods_id'])     and !empty($data['order_goods_id']))     ? intval($data['order_goods_id'])     : null;
        return array_filter(self::$dataArray);
    }
    /**
     * 添加商品评价过滤
     * @param array $data
     * @return array
     */
    public static function addGoodsCommentData (array $data)
    {
        return self::checkData($data);
    }
}

?>