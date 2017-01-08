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
class User
{
    private static $dataArray = array();
    /**
     * 
     * @param array $data
     * @return multitype:
     */
    private static function checkData (array $data)
    {

        self::$dataArray['user_id']                 = (isset($data['user_id'])                  and !empty($data['user_id']))                   ? intval($data['user_id'])                  : null;
        self::$dataArray['group_id']                = (isset($data['group_id'])                 and !empty($data['group_id']))                  ? intval($data['group_id'])                 : null;
        self::$dataArray['user_name']               = (isset($data['user_name'])                and !empty($data['user_name']))                 ? trim($data['user_name'])                  : null;
        self::$dataArray['user_avatar']             = (isset($data['user_avatar'])              and !empty($data['user_avatar']))               ? trim($data['user_avatar'])                : null;
        self::$dataArray['user_password']           = (isset($data['user_password'])            and !empty($data['user_password']))             ? trim($data['user_password'])              : null;
        self::$dataArray['user_email']              = (isset($data['user_email'])               and !empty($data['user_email']))                ? trim($data['user_email'])                 : null;
        self::$dataArray['user_sex']                = (isset($data['user_sex'])                 and !empty($data['user_sex']))                  ? intval($data['user_sex'])                 : null;
        self::$dataArray['user_time']               = (isset($data['user_time'])                and !empty($data['user_time']))                 ? trim($data['user_time'])                  : null;
        self::$dataArray['user_state']              = (isset($data['user_state'])               and !empty($data['user_state']))                ? intval($data['user_state'])               : null;
        self::$dataArray['user_audit_code']         = (isset($data['user_audit_code'])          and !empty($data['user_audit_code']))           ? trim($data['user_audit_code'])            : null;
        self::$dataArray['user_forgot_passwd_code'] = (isset($data['user_forgot_passwd_code'])  and !empty($data['user_forgot_passwd_code']))   ? trim($data['user_forgot_passwd_code'])    : null;
        self::$dataArray['user_money']              = (isset($data['user_money'])               and !empty($data['user_money']))                ? floatval ($data['user_money'])            : null;

        self::$dataArray = array_filter(self::$dataArray);
        
        self::$dataArray['integral_type_2_num']     = (isset($data['integral_type_2_num'])      and !empty($data['integral_type_2_num']))       ? intval($data['integral_type_2_num'])      : 0;
        self::$dataArray['user_integral_num']       = (isset($data['user_integral_num'])        and !empty($data['user_integral_num']))         ? intval($data['user_integral_num'])        : 0;
        self::$dataArray['user_phone']              = isset($data['user_phone']) ? (!empty($data['user_phone']) ? trim($data['user_phone']) : '')          : 'no';
        self::$dataArray['user_birthday']           = isset($data['user_birthday']) ? (!empty($data['user_birthday']) ? trim($data['user_birthday']) : '') : 'no';

        return self::$dataArray;
    }
    /**
     * 添加会员过滤
     * @param array $data
     * @return multitype
     */
    public static function addUserData(array $data)
    {
        $data = self::checkData($data);

        if($data['user_phone'] == 'no') unset($data['user_phone']);
        if($data['user_birthday'] == 'no') unset($data['user_birthday']);

        return $data;
    }
    /**
     * 会员更新过滤
     * @param array $data
     * @return array|multitype
     */
    public static function updateUserData(array $data)
    {
        unset($data['user_id']);
        unset($data['user_time']);

        $data = self::checkData($data);

        if(isset($data['user_audit_code']) and $data['user_audit_code'] == 'no')         $data['user_audit_code'] = '';
        if(isset($data['user_forgot_passwd_code']) and $data['user_forgot_passwd_code'] == 'no') $data['user_forgot_passwd_code'] = '';
        //会员编辑中如果涉及到积分，则直接注销掉，不可以通过这里取得
        if(isset($data['user_integral_num'])) unset($data['user_integral_num']);
        if(isset($data['integral_type_2_num'])) unset($data['integral_type_2_num']);
        //在一些编辑会员特定信息的时候，下面两个内容，有可能会被清空，所以进行如下处理，防止被清空
        if($data['user_phone'] == 'no') unset($data['user_phone']);
        if($data['user_birthday'] == 'no') unset($data['user_birthday']);

        return $data;
    }
    /**
     * 会员检索过滤
     * @param array $data
     * @return array
     */
    public static function whereUserData (array $data=null)
    {
        $searchArray = array();
        $searchArray[] = (isset($data['user_start_id'])     and !empty($data['user_start_id']))     ? 'dbshop_user.user_id >= ' . intval($data['user_start_id'])            : '';
        $searchArray[] = (isset($data['user_end_id'])       and !empty($data['user_end_id']))       ? 'dbshop_user.user_id <= ' . intval($data['user_end_id'])              : '';
        $searchArray[] = (isset($data['user_name'])         and !empty($data['user_name']))         ? 'dbshop_user.user_name like \'%' . trim($data['user_name']) . '%\''   : '';
        $searchArray[] = (isset($data['user_email'])        and !empty($data['user_email']))        ? 'dbshop_user.user_email like \'%' . trim($data['user_email']) . '%\'' : '';
        $searchArray[] = (isset($data['search_start_time']) and !empty($data['search_start_time'])) ? 'dbshop_user.user_time >= ' . strtotime($data['search_start_time'])   : '';
        $searchArray[] = (isset($data['search_end_time'])   and !empty($data['search_end_time']))   ? 'dbshop_user.user_time <= ' . strtotime($data['search_end_time'])     : '';
        $searchArray[] = (isset($data['group_id'])          and !empty($data['group_id']))          ? 'dbshop_user.group_id = ' . intval($data['group_id'])                 : '';
        $searchArray[] = (isset($data['user_state'])        and !empty($data['user_state']))        ? 'dbshop_user.user_state = ' . intval($data['user_state'])             : '';
        $searchArray[] = (isset($data['user_money'])        and !empty($data['user_money']))        ? 'dbshop_user.user_money like \'%' . floatval($data['user_money']) . '%\'' : '';

        return array_filter($searchArray);
    }
    
}
