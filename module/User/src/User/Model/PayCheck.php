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


class PayCheck
{
    private static $dataArray = array();
    /**
     *
     * @param array $data
     * @return multitype:
     */
    private static function checkData (array $data)
    {
        self::$dataArray['paycheck_id']          = (isset($data['paycheck_id'])          and !empty($data['paycheck_id']))          ? intval($data['paycheck_id'])        : null;
        self::$dataArray['user_id']              = (isset($data['user_id'])              and !empty($data['user_id']))              ? intval($data['user_id'])            : null;
        self::$dataArray['user_name']            = (isset($data['user_name'])            and !empty($data['user_name']))            ? trim($data['user_name'])            : null;
        self::$dataArray['money_change_num']     = (isset($data['money_change_num'])     and !empty($data['money_change_num']))     ? floatval($data['money_change_num']) : null;
        self::$dataArray['currency_code']        = (isset($data['currency_code'])        and !empty($data['currency_code']))        ? trim($data['currency_code'])        : null;
        self::$dataArray['paycheck_time']        = (isset($data['paycheck_time'])        and !empty($data['paycheck_time']))        ? trim($data['paycheck_time'])        : time();
        self::$dataArray['pay_state']            = (isset($data['pay_state'])            and !empty($data['pay_state']))            ? intval($data['pay_state'])          : null;
        self::$dataArray['pay_code']             = (isset($data['pay_code'])             and !empty($data['pay_code']))             ? trim($data['pay_code'])             : null;
        self::$dataArray['pay_name']             = (isset($data['pay_name'])             and !empty($data['pay_name']))             ? trim($data['pay_name'])             : null;
        self::$dataArray['admin_id']             = (isset($data['admin_id'])             and !empty($data['admin_id']))             ? intval($data['admin_id'])           : null;
        self::$dataArray['admin_name']           = (isset($data['admin_name'])           and !empty($data['admin_name']))           ? trim($data['admin_name'])           : null;
        self::$dataArray['paycheck_finish_time'] = (isset($data['paycheck_finish_time']) and !empty($data['paycheck_finish_time'])) ? trim($data['paycheck_finish_time']) : null;
        self::$dataArray['paycheck_info']        = (isset($data['paycheck_info'])        and !empty($data['paycheck_info']))        ? trim($data['paycheck_info'])        : null;

        self::$dataArray = array_filter(self::$dataArray);

        return self::$dataArray;
    }
    /**
     * 添加充值记录过滤
     * @param array $data
     * @return multitype
     */
    public static function addPayCheckData(array $data)
    {
        return self::checkData($data);
    }
    /**
     * 对于会员充值记录检索
     * @param array $data
     * @return array
     */
    public static function wherePayCheckData(array $data)
    {
        $searchArray = array();
        $searchArray[] = (isset($data['admin_name'])                 and !empty($data['admin_name']))                 ? 'paycheck.admin_name like \'%' . trim($data['admin_name']) . '%\''                   : '';
        $searchArray[] = (isset($data['money_change_num'])           and !empty($data['money_change_num']))           ? 'paycheck.money_change_num like \'%' . trim($data['money_change_num']) . '%\''       : '';
        $searchArray[] = (isset($data['user_name'])                  and !empty($data['user_name']))                  ? 'paycheck.user_name like \'%' . trim($data['user_name']) . '%\''                     : '';
        $searchArray[] = (isset($data['paycheck_start_time'])        and !empty($data['paycheck_start_time']))        ? 'paycheck.paycheck_time >= ' . strtotime($data['paycheck_start_time'])               : '';
        $searchArray[] = (isset($data['paycheck_end_time'])          and !empty($data['paycheck_end_time']))          ? 'paycheck.paycheck_time <= ' . strtotime($data['paycheck_end_time'])                 : '';
        $searchArray[] = (isset($data['paycheck_start_finish_time']) and !empty($data['paycheck_start_finish_time'])) ? 'paycheck.paycheck_finish_time >= ' . strtotime($data['paycheck_start_finish_time']) : '';
        $searchArray[] = (isset($data['paycheck_end_finish_time'])   and !empty($data['paycheck_end_finish_time']))   ? 'paycheck.paycheck_finish_time <= ' . strtotime($data['paycheck_end_finish_time'])   : '';
        $searchArray[] = (isset($data['pay_state'])                  and !empty($data['pay_state']))                  ? 'paycheck.pay_state = ' . intval($data['pay_state'])                                 : '';
        $searchArray[] = (isset($data['paycheck_info'])              and !empty($data['paycheck_info']))              ? 'paycheck.paycheck_info like \'%' . trim($data['paycheck_info']) . '%\''             : '';

        return array_filter($searchArray);
    }
    /**
     * 前台会员充值记录检索
     * @param array $data
     * @return array
     */
    public static function frontWherePayCheckData(array $data)
    {
        $searchArray = array();
        $searchArray[] = (isset($data['search_content']) and !empty($data['search_content'])) ? '(paycheck.money_change_num like \'%' . trim($data['search_content']) . '%\' or ' . 'paycheck.paycheck_info like \'%' . trim($data['search_content']) . '%\')' : '';
        $searchArray[] = (isset($data['user_id'])        and !empty($data['user_id']))        ? 'paycheck.user_id='.intval($data['user_id'])                    : '';

        return array_filter($searchArray);
    }
}