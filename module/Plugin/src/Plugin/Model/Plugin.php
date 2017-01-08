<?php
/**
 * DBShop 电子商务系统
 *
 * ==========================================================================
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2016 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 * ==========================================================================
 *
 * @author    静静的风
 *
 */

namespace Plugin\Model;


class Plugin
{
    private static $dataArray = array();

    private static function checkData (array $data)
    {
        self::$dataArray['plugin_id']               = (isset($data['plugin_id'])                and !empty($data['plugin_id']))                 ? intval($data['plugin_id'])            : null;
        self::$dataArray['plugin_name']             = (isset($data['plugin_name'])              and !empty($data['plugin_name']))               ? trim($data['plugin_name'])            : null;
        self::$dataArray['plugin_author']           = (isset($data['plugin_author'])            and !empty($data['plugin_author']))             ? trim($data['plugin_author'])          : null;
        self::$dataArray['plugin_author_url']       = (isset($data['plugin_author_url'])        and !empty($data['plugin_author_url']))         ? trim($data['plugin_author_url'])      : '';
        self::$dataArray['plugin_info']             = (isset($data['plugin_info'])              and !empty($data['plugin_info']))               ? trim($data['plugin_info'])            : '';
        self::$dataArray['plugin_version']          = (isset($data['plugin_version'])           and !empty($data['plugin_version']))            ? trim($data['plugin_version'])         : '';
        self::$dataArray['plugin_version_num']      = (isset($data['plugin_version_num'])       and !empty($data['plugin_version_num']))        ? trim($data['plugin_version_num'])     : '';
        self::$dataArray['plugin_code']             = (isset($data['plugin_code'])              and !empty($data['plugin_code']))               ? trim($data['plugin_code'])            : '';
        self::$dataArray['plugin_state']            = (isset($data['plugin_state'])             and !empty($data['plugin_state']))              ? intval($data['plugin_state'])         : 2;
        self::$dataArray['plugin_support_url']      = (isset($data['plugin_support_url'])       and !empty($data['plugin_support_url']))        ? trim($data['plugin_support_url'])     : '';
        self::$dataArray['plugin_admin_path']       = (isset($data['plugin_admin_path'])        and !empty($data['plugin_admin_path']))         ? trim($data['plugin_admin_path'])      : '';
        self::$dataArray['plugin_update_time']      = (isset($data['plugin_update_time'])       and !empty($data['plugin_update_time']))        ? trim($data['plugin_update_time'])     : time();
        self::$dataArray['plugin_support_version']  = (isset($data['plugin_support_version'])   and !empty($data['plugin_support_version']))    ? trim($data['plugin_support_version']) : '';

        return array_filter(self::$dataArray);
    }
    public static function addPluginData (array $data)
    {
        return self::checkData($data);
    }
}