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

class UserGroupExtend
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['group_id']       = (isset($data['group_id'])        and !empty($data['group_id']))        ? intval($data['group_id'])       : null;
        self::$dataArray['group_name']     = (isset($data['user_group_name']) and !empty($data['user_group_name'])) ? trim($data['user_group_name'])  : null;
        self::$dataArray['language']       = (isset($data['language'])        and !empty($data['language']))        ? trim($data['language'])         : null;
    
        return array_filter(self::$dataArray);
    }
    public static function addUserGroupExtendData (array $data)
    {
        return self::checkData($data);
    }
    public static function updateUserGroupExtendData (array $data)
    {
        unset($data['group_id']);

        return self::checkData($data);
    }
}

?>