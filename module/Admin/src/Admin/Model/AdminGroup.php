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

class AdminGroup
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['admin_group_id']      = (isset($data['admin_group_id'])       and !empty($data['admin_group_id']))        ? intval($data['admin_group_id'])    : null;
        self::$dataArray['admin_group_purview'] = (isset($data['admin_group_purview'])  and !empty($data['admin_group_purview']))   ? trim($data['admin_group_purview']) : null;
    
        return self::$dataArray;
    }
    /**
     * 添加管理员组检测
     * @param array $data
     * @return array
     */
    public static function addAdminGroupData (array $data)
    {
        return self::checkData($data);
    }
}

?>