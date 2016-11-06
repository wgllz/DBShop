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

namespace Orders\Model;

class OrderRefund
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['refund_id']          = (isset($data['refund_id'])          and !empty($data['refund_id']))          ? intval($data['refund_id'])        : null;
        self::$dataArray['order_sn']           = (isset($data['order_sn'])           and !empty($data['order_sn']))           ? trim($data['order_sn'])           : null;
        self::$dataArray['refund_price']       = (isset($data['refund_price'])       and !empty($data['refund_price']))       ? trim($data['refund_price'])       : null;
        self::$dataArray['goods_id_str']       = (isset($data['goods_id_str'])       and !empty($data['goods_id_str']))       ? trim($data['goods_id_str'])       : null;
        self::$dataArray['refund_type']        = (isset($data['refund_type'])        and !empty($data['refund_type']))        ? intval($data['refund_type'])      : null;
        self::$dataArray['refund_state']       = (isset($data['refund_state'])       and !empty($data['refund_state']))       ? intval($data['refund_state'])     : null;
        self::$dataArray['refund_time']        = (isset($data['refund_time'])        and !empty($data['refund_time']))        ? trim($data['refund_time'])        : time();
        self::$dataArray['finish_refund_time'] = (isset($data['finish_refund_time']) and !empty($data['finish_refund_time'])) ? trim($data['finish_refund_time']) : null;
        self::$dataArray['refund_info']        = (isset($data['refund_info'])        and !empty($data['refund_info']))        ? trim($data['refund_info'])        : null;
        self::$dataArray['re_refund_info']     = (isset($data['re_refund_info'])     and !empty($data['re_refund_info']))     ? trim($data['re_refund_info'])     : null;
        self::$dataArray['user_id']            = (isset($data['user_id'])            and !empty($data['user_id']))            ? intval($data['user_id'])          : null;
        self::$dataArray['user_name']          = (isset($data['user_name'])          and !empty($data['user_name']))          ? trim($data['user_name'])          : null;
        self::$dataArray['admin_id']           = (isset($data['admin_id'])           and !empty($data['admin_id']))           ? intval($data['admin_id'])         : null;
        self::$dataArray['admin_name']         = (isset($data['admin_name'])         and !empty($data['admin_name']))         ? trim($data['admin_name'])         : null;
        self::$dataArray['bank_name']          = (isset($data['bank_name'])          and !empty($data['bank_name']))          ? trim($data['bank_name'])          : null;
        self::$dataArray['bank_account']       = (isset($data['bank_account'])       and !empty($data['bank_account']))       ? trim($data['bank_account'])       : null;
        self::$dataArray['bank_card_number']   = (isset($data['bank_card_number'])   and !empty($data['bank_card_number']))   ? trim($data['bank_card_number'])   : null;

        self::$dataArray = array_filter(self::$dataArray);

        return self::$dataArray;
    }
    /**
     * 添加退货申请过滤
     * @param array $data
     * @return array
     */
    public static function addOrderRefundData (array $data)
    {
        return self::checkData($data);
    }
    public static function whereOrderRefundData (array $data)
    {
        $searchArray = array();
        $searchArray[] = (isset($data['search_content'])           and !empty($data['search_content']))           ? 'refund.order_sn like \'%' . trim($data['search_content']) . '%\''             : '';
        $searchArray[] = (isset($data['refund_type'])              and !empty($data['refund_type']))              ? 'refund.refund_type='.intval($data['refund_type'])                             : '';
        $searchArray[] = (isset($data['refund_state'])             and (!empty($data['refund_state']) or $data['refund_state'] == '0')) ? 'refund.refund_state='.intval($data['refund_state'])       : '';
        $searchArray[] = (isset($data['start_refund_time'])        and !empty($data['start_refund_time']))        ? 'refund.refund_time >= ' . strtotime($data['start_refund_time'])               : '';
        $searchArray[] = (isset($data['end_refund_time'])          and !empty($data['end_refund_time']))          ? 'refund.refund_time <= ' . strtotime($data['end_refund_time'])                 : '';
        $searchArray[] = (isset($data['start_finish_refund_time']) and !empty($data['start_finish_refund_time'])) ? 'refund.finish_refund_time >= ' . strtotime($data['start_finish_refund_time']) : '';
        $searchArray[] = (isset($data['end_finish_refund_time'])   and !empty($data['end_finish_refund_time']))   ? 'refund.finish_refund_time <= ' . strtotime($data['end_finish_refund_time'])   : '';
        $searchArray[] = (isset($data['user_id'])                  and !empty($data['user_id']))                  ? 'refund.user_id='.intval($data['user_id'])                                     : '';
        //print_r($searchArray);exit;
        return array_filter($searchArray);
    }
    public static function frontWhereOrderRefundData (array $data)
    {
        $searchArray = array();
        $searchArray[] = (isset($data['search_content']) and !empty($data['search_content'])) ? 'refund.order_sn like \'%' . trim($data['search_content']) . '%\'' : '';
        $searchArray[] = (isset($data['user_id'])        and !empty($data['user_id']))        ? 'refund.user_id='.intval($data['user_id'])                    : '';

        return array_filter($searchArray);
    }
}