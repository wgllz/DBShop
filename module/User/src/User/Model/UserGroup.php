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

class UserGroup
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['group_id']            = (isset($data['group_id'])           and !empty($data['group_id']))           ? intval($data['group_id'])           : null;
        self::$dataArray['integral_num_start']  = (isset($data['integral_num_start']) and !empty($data['integral_num_start'])) ? intval($data['integral_num_start']) : null;
        self::$dataArray['integral_num_end']    = (isset($data['integral_num_end'])   and !empty($data['integral_num_end']))   ? intval($data['integral_num_end'])   : null;

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['integral_num_state']  = (isset($data['integral_num_state']) and !empty($data['integral_num_state'])) ? intval($data['integral_num_state']) : 0;

        return self::$dataArray;
    }
    /**
     * 添加会员组过滤
     * @param array $data
     * @return array
     */
    public static function addUserGroupData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 更新会员组过滤
     * @param array $data
     * @return array
     */
    public static function updateUserGroupData (array $data)
    {
        if(isset($data['group_id'])) unset($data['group_id']);
        $data = self::checkData($data);
        return $data;
    }
}
