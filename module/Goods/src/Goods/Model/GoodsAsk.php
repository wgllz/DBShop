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

class GoodsAsk
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['ask_id']            = (isset($data['ask_id'])            and !empty($data['ask_id']))            ? intval($data['ask_id'])          : null;
        self::$dataArray['ask_writer']        = (isset($data['ask_writer'])        and !empty($data['ask_writer']))        ? trim($data['ask_writer'])        : null;
        self::$dataArray['ask_content']       = (isset($data['ask_content'])       and !empty($data['ask_content']))       ? trim($data['ask_content'])       : null;
        self::$dataArray['ask_time']          = (isset($data['ask_time'])          and !empty($data['ask_time']))          ? trim($data['ask_time'])          : null;
        self::$dataArray['reply_ask_content'] = (isset($data['reply_ask_content']) and !empty($data['reply_ask_content'])) ? trim($data['reply_ask_content']) : null;
        self::$dataArray['reply_ask_time']    = (isset($data['reply_ask_time'])    and !empty($data['reply_ask_time']))    ? trim($data['reply_ask_time'])    : null;
        self::$dataArray['reply_ask_writer']  = (isset($data['reply_ask_writer'])  and !empty($data['reply_ask_writer']))  ? trim($data['reply_ask_writer'])  : null;
        self::$dataArray['goods_id']          = (isset($data['goods_id'])          and !empty($data['goods_id']))          ? intval($data['goods_id'])        : null;

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['ask_show_state']    = (isset($data['ask_show_state'])    and !empty($data['ask_show_state']))    ? intval($data['ask_show_state'])  : 0;

        return self::$dataArray;
    }
    /**
     * 商品咨询过滤
     * @param array $data
     * @return array
     */
    public static function addGoodsAskData(array $data)
    {
        return self::checkData($data);
    }
}

?>