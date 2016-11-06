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

namespace User\Model;

class UserFavorites
{
    private static $dataArray = array();

    private static function checkData (array $data)
    {
        self::$dataArray['favorites_id']  = (isset($data['favorites_id'])  and !empty($data['favorites_id']))  ? intval($data['favorites_id']) : null;
        self::$dataArray['goods_id']      = (isset($data['goods_id'])      and !empty($data['goods_id']))      ? intval($data['goods_id'])     : null;
        self::$dataArray['class_id']      = (isset($data['class_id'])      and !empty($data['class_id']))      ? intval($data['class_id'])     : null;
        self::$dataArray['user_id']       = (isset($data['user_id'])       and !empty($data['user_id']))       ? intval($data['user_id'])      : null;
        self::$dataArray['time']          = time();

        self::$dataArray = array_filter(self::$dataArray);

        return self::$dataArray;
    }
    /**
     * 添加收藏过滤
     * @param $data
     * @return array
     */
    public static function addFavoritesData ($data)
    {
        return self::checkData($data);
    }
}