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

class OtherLogin
{
    private static $dataArray = array();

    /**
     *
     * @param array $data
     * @return multitype:
     */
    private static function checkData (array $data)
    {

        self::$dataArray['ol_id']         = (isset($data['ol_id'])       and !empty($data['ol_id']))      ? intval($data['ol_id'])      : null;
        self::$dataArray['user_id']       = (isset($data['user_id'])     and !empty($data['user_id']))    ? intval($data['user_id'])    : null;
        self::$dataArray['open_id']       = (isset($data['open_id'])     and !empty($data['open_id']))    ? trim($data['open_id'])      : null;
        self::$dataArray['ol_add_time']   = (isset($data['ol_add_time']) and !empty($data['ol_add_time']))? trim($data['ol_add_time'])  : null;
        self::$dataArray['login_type']    = (isset($data['login_type'])  and !empty($data['login_type'])) ? trim($data['login_type'])   : null;

        return array_filter(self::$dataArray);
    }
    /**
     * 过滤添加到第三方登录数据表的信息
     * @param array $data
     * @return multitype
     */
    public static function addOtherLoginData(array $data)
    {
        return self::checkData($data);
    }
}