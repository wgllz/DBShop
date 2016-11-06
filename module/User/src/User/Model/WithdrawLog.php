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


class WithdrawLog
{
    private static $dataArray = array();
    /**
     *
     * @param array $data
     * @return multitype:
     */
    private static function checkData (array $data)
    {
        self::$dataArray['withdraw_id']      = (isset($data['withdraw_id'])      and !empty($data['withdraw_id']))      ? intval($data['withdraw_id'])        : null;
        self::$dataArray['user_id']          = (isset($data['user_id'])          and !empty($data['user_id']))          ? intval($data['user_id'])            : null;
        self::$dataArray['user_name']        = (isset($data['user_name'])        and !empty($data['user_name']))        ? trim($data['user_name'])            : null;
        self::$dataArray['money_change_num'] = (isset($data['money_change_num']) and !empty($data['money_change_num'])) ? floatval($data['money_change_num']) : null;
        self::$dataArray['currency_code']    = (isset($data['currency_code'])    and !empty($data['currency_code']))    ? trim($data['currency_code'])        : null;
        self::$dataArray['bank_name']        = (isset($data['bank_name'])        and !empty($data['bank_name']))        ? trim($data['bank_name'])            : null;
        self::$dataArray['bank_account']     = (isset($data['bank_account'])     and !empty($data['bank_account']))     ? trim($data['bank_account'])         : null;
        self::$dataArray['bank_card_number'] = (isset($data['bank_card_number']) and !empty($data['bank_card_number'])) ? trim($data['bank_card_number'])     : null;
        self::$dataArray['withdraw_info']    = (isset($data['withdraw_info'])    and !empty($data['withdraw_info']))    ? trim($data['withdraw_info'])        : null;
        self::$dataArray['withdraw_time']    = (isset($data['withdraw_time'])    and !empty($data['withdraw_time']))    ? trim($data['withdraw_time'])        : time();
        self::$dataArray['withdraw_state']   = (isset($data['withdraw_state'])   and !empty($data['withdraw_state']))   ? intval($data['withdraw_state'])     : null;
        self::$dataArray['admin_id']         = (isset($data['admin_id'])         and !empty($data['admin_id']))         ? intval($data['admin_id'])           : null;
        self::$dataArray['admin_name']       = (isset($data['admin_name'])       and !empty($data['admin_name']))       ? trim($data['admin_name'])           : null;
        self::$dataArray['withdraw_finish_time']    = (isset($data['withdraw_finish_time']) and !empty($data['withdraw_finish_time'])) ? trim($data['withdraw_finish_time']) : null;

        self::$dataArray = array_filter(self::$dataArray);

        return self::$dataArray;
    }
    /**
     * 添加提现记录过滤
     * @param array $data
     * @return multitype
     */
    public static function addWithdrawLogData(array $data)
    {
        return self::checkData($data);
    }
    /**
     * 对于会员余额记录检索
     * @param array $data
     * @return array
     */
    public static function whereWithdrawLogData(array $data)
    {
        $searchArray = array();
        $searchArray[] = (isset($data['admin_name'])                 and !empty($data['admin_name']))                 ? 'withdrawlog.admin_name like \'%' . trim($data['admin_name']) . '%\'' : '';
        $searchArray[] = (isset($data['money_change_num'])           and !empty($data['money_change_num']))           ? 'withdrawlog.money_change_num like \'%' . trim($data['money_change_num']) . '%\''         : '';
        $searchArray[] = (isset($data['user_name'])                  and !empty($data['user_name']))                  ? 'withdrawlog.user_name like \'%' . trim($data['user_name']) . '%\''                       : '';
        $searchArray[] = (isset($data['withdraw_start_time'])        and !empty($data['withdraw_start_time']))        ? 'withdrawlog.withdraw_time >= ' . strtotime($data['withdraw_start_time'])               : '';
        $searchArray[] = (isset($data['withdraw_end_time'])          and !empty($data['withdraw_end_time']))          ? 'withdrawlog.withdraw_time <= ' . strtotime($data['withdraw_end_time'])                 : '';
        $searchArray[] = (isset($data['withdraw_start_finish_time']) and !empty($data['withdraw_start_finish_time'])) ? 'withdrawlog.withdraw_finish_time >= ' . strtotime($data['withdraw_start_finish_time']) : '';
        $searchArray[] = (isset($data['withdraw_end_finish_time'])   and !empty($data['withdraw_end_finish_time']))   ? 'withdrawlog.withdraw_finish_time <= ' . strtotime($data['withdraw_end_finish_time'])   : '';
        $searchArray[] = (isset($data['withdraw_state'])             and (!empty($data['withdraw_state']) or $data['withdraw_state'] == 0))             ? 'withdrawlog.withdraw_state = ' . intval($data['withdraw_state']) : '';
        $searchArray[] = (isset($data['withdraw_info'])              and !empty($data['withdraw_info']))              ? 'withdrawlog.withdraw_info like \'%' . trim($data['withdraw_info']) . '%\''             : '';

        return array_filter($searchArray);
    }
    /**
     * 前台会员余额记录检索
     * @param array $data
     * @return array
     */
    public static function frontWhereWithdrawLogData(array $data)
    {
        $searchArray = array();
        $searchArray[] = (isset($data['search_content']) and !empty($data['search_content'])) ? '(withdrawlog.money_change_num like \'%' . trim($data['search_content']) . '%\' or ' . 'withdrawlog.withdraw_info like \'%' . trim($data['search_content']) . '%\')' : '';
        $searchArray[] = (isset($data['user_id'])        and !empty($data['user_id']))        ? 'withdrawlog.user_id='.intval($data['user_id'])                    : '';

        return array_filter($searchArray);
    }
}