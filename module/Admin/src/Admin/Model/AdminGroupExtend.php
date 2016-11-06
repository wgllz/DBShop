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

class AdminGroupExtend
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['admin_group_id']   = (isset($data['admin_group_id'])   and !empty($data['admin_group_id']))   ? intval($data['admin_group_id']) : null;
        self::$dataArray['admin_group_name'] = (isset($data['admin_group_name']) and !empty($data['admin_group_name'])) ? trim($data['admin_group_name']) : null;
        self::$dataArray['language']         = (isset($data['language'])         and !empty($data['language']))         ? trim($data['language'])         : null;
    
        return array_filter(self::$dataArray);
    }
    /**
     * 添加管理员扩展信息检测
     * @param array $data
     * @return array
     */
    public static function addAdminGroupExtendData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 更新管理员扩展信息检测
     * @param array $data
     * @return array
     */
    public static function updateAdminGroupExtendData (array $data)
    {
        unset($data['admin_group_id']);
        return self::checkData($data);
    }
}

?>