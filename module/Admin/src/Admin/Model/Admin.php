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

namespace Admin\Model;

class Admin
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['admin_id']             = (isset($data['admin_id'])             and !empty($data['admin_id']))             ? intval($data['admin_id'])           : null;
        self::$dataArray['admin_group_id']       = (isset($data['admin_group_id'])       and !empty($data['admin_group_id']))       ? intval($data['admin_group_id'])     : null;
        self::$dataArray['admin_passwd']         = (isset($data['admin_password'])       and !empty($data['admin_password']))       ? trim($data['admin_password'])       : null;
        self::$dataArray['admin_name']           = (isset($data['admin_name'])           and !empty($data['admin_name']))           ? trim($data['admin_name'])           : null;
        self::$dataArray['admin_email']          = (isset($data['admin_email'])          and !empty($data['admin_email']))          ? trim($data['admin_email'])          : null;
        self::$dataArray['admin_add_time']       = (isset($data['admin_add_time'])       and !empty($data['admin_add_time']))       ? $data['admin_add_time']             : null;

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['admin_old_login_time'] = (isset($data['admin_old_login_time']) and !empty($data['admin_old_login_time'])) ? trim($data['admin_old_login_time']) : null;
        self::$dataArray['admin_new_login_time'] = (isset($data['admin_new_login_time']) and !empty($data['admin_new_login_time'])) ? trim($data['admin_new_login_time']) : null;
    
        return self::$dataArray;
    }
    /**
     * 添加管理员信息检测
     * @param array $data
     * @return array
     */
    public static function addAdminData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 更新管理员信息检测
     * @param array $data
     * @return array
     */
    public static function updateAdminData (array $data)
    {
        unset($data['admin_id']);
        return self::checkData($data);
    }
}

?>