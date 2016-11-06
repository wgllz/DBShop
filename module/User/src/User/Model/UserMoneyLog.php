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

namespace User\Model;


class UserMoneyLog
{
    private static $dataArray = array();
    /**
     *
     * @param array $data
     * @return multitype:
     */
    private static function checkData (array $data)
    {
        self::$dataArray['money_log_id']         = (isset($data['money_log_id'])         and !empty($data['money_log_id']))         ? intval($data['money_log_id'])           : null;
        self::$dataArray['money_change_num']     = (isset($data['money_change_num'])     and !empty($data['money_change_num']))     ? trim($data['money_change_num'])         : null;
        self::$dataArray['money_changed_amount'] = (isset($data['money_changed_amount']) and !empty($data['money_changed_amount'])) ? floatval($data['money_changed_amount']) : null;
        self::$dataArray['money_change_time']    = (isset($data['money_change_time'])    and !empty($data['money_change_time']))    ? trim($data['money_change_time'])        : time();
        self::$dataArray['money_pay_state']      = (isset($data['money_pay_state'])      and !empty($data['money_pay_state']))      ? trim($data['money_pay_state'])          : null;
        self::$dataArray['money_pay_info']       = (isset($data['money_pay_info'])       and !empty($data['money_pay_info']))       ? trim($data['money_pay_info'])           : null;
        self::$dataArray['payment_code']         = (isset($data['payment_code'])         and !empty($data['payment_code']))         ? trim($data['payment_code'])             : null;
        self::$dataArray['user_id']              = (isset($data['user_id'])              and !empty($data['user_id']))              ? intval($data['user_id'])                : null;
        self::$dataArray['user_name']            = (isset($data['user_name'])            and !empty($data['user_name']))            ? trim($data['user_name'])                : null;
        self::$dataArray['admin_id']             = (isset($data['admin_id'])             and !empty($data['admin_id']))             ? intval($data['admin_id'])               : null;
        self::$dataArray['admin_name']           = (isset($data['admin_name'])           and !empty($data['admin_name']))           ? trim($data['admin_name'])               : null;
        self::$dataArray['money_pay_type']       = (isset($data['money_pay_type'])       and !empty($data['money_pay_type']))       ? intval($data['money_pay_type'])         : null;

        self::$dataArray = array_filter(self::$dataArray);

        return self::$dataArray;
    }
    /**
     * 添加会员余额过滤
     * @param array $data
     * @return multitype
     */
    public static function addUserMoneyLogData(array $data)
    {
        return self::checkData($data);
    }
    /**
     * 对于会员余额记录检索
     * @param array $data
     * @return array
     */
    public static function whereUserMoneyLogData(array $data)
    {
        $searchArray = array();
        $searchArray[] = (isset($data['oper_user'])               and !empty($data['oper_user']))               ? '(moneylog.admin_name like \'%' . trim($data['oper_user']) . '%\' and moneylog.admin_id!=0) or (moneylog.user_name like \'%' . trim($data['oper_user']) . '%\' and moneylog.admin_id=0)' : '';
        $searchArray[] = (isset($data['money_change_num'])        and !empty($data['money_change_num']))        ? 'moneylog.money_change_num like \'%' . trim($data['money_change_num']) . '%\''         : '';
        $searchArray[] = (isset($data['user_name'])               and !empty($data['user_name']))               ? 'moneylog.user_name like \'%' . trim($data['user_name']) . '%\''                       : '';
        $searchArray[] = (isset($data['money_changed_amount'])    and !empty($data['money_changed_amount']))    ? 'moneylog.money_changed_amount like \'%' . trim($data['money_changed_amount']) . '%\'' : '';
        $searchArray[] = (isset($data['money_start_change_time']) and !empty($data['money_start_change_time'])) ? 'moneylog.money_change_time >= ' . strtotime($data['money_start_change_time'])         : '';
        $searchArray[] = (isset($data['money_end_change_time'])   and !empty($data['money_end_change_time']))   ? 'moneylog.money_change_time <= ' . strtotime($data['money_end_change_time'])           : '';
        $searchArray[] = (isset($data['money_pay_type'])          and !empty($data['money_pay_type']))          ? 'moneylog.money_pay_type = ' . intval($data['money_pay_type'])                         : '';
        $searchArray[] = (isset($data['money_pay_info'])          and !empty($data['money_pay_info']))          ? 'moneylog.money_pay_info like \'%' . trim($data['money_pay_info']) . '%\''             : '';

        return array_filter($searchArray);
    }
    /**
     * 前台会员余额记录检索
     * @param array $data
     * @return array
     */
    public static function frontWhereUserMoneyLogData(array $data)
    {
        $searchArray = array();
        $searchArray[] = (isset($data['search_content']) and !empty($data['search_content'])) ? '(moneylog.money_change_num like \'%' . trim($data['search_content']) . '%\' or ' . 'moneylog.money_pay_info like \'%' . trim($data['search_content']) . '%\')' : '';
        $searchArray[] = (isset($data['user_id'])        and !empty($data['user_id']))        ? 'moneylog.user_id='.intval($data['user_id'])                    : '';
        $searchArray[] = (isset($data['money_pay_type']) and !empty($data['money_pay_type'])) ? 'moneylog.money_pay_type = ' . intval($data['money_pay_type'])  : '';
        return array_filter($searchArray);
    }
}