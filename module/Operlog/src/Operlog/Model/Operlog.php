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

namespace Operlog\Model;

class Operlog
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['log_id']             = (isset($data['log_id'])             and !empty($data['log_id']))             ? intval($data['log_id'])           : null;
        self::$dataArray['log_oper_user']      = (isset($data['log_oper_user'])      and !empty($data['log_oper_user']))      ? trim($data['log_oper_user'])      : null;
        self::$dataArray['log_oper_usergroup'] = (isset($data['log_oper_usergroup']) and !empty($data['log_oper_usergroup'])) ? trim($data['log_oper_usergroup']) : null;
        self::$dataArray['log_ip']             = (isset($data['log_ip'])             and !empty($data['log_ip']))             ? trim($data['log_ip'])             : null;
        self::$dataArray['log_time']           = (isset($data['log_time'])           and !empty($data['log_time']))           ? trim($data['log_time'])           : null;
        self::$dataArray['log_content']        = (isset($data['log_content'])        and !empty($data['log_content']))        ? trim($data['log_content'])        : null;
    
        self::$dataArray = array_filter(self::$dataArray);
    
        return self::$dataArray;
    }
    /**
     * 添加操作日志过滤
     * @param array $data
     * @return array
     */
    public static function addOperlogData(array $data)
    {
        return self::checkData($data);
    }
    /**
     * 查询过滤
     * @param array $data
     * @return array
     */
    public static function whereOperData(array $data=array())
    {
        $filter = new \Zend\Filter\HtmlEntities();

        $searchArray = array();
        $searchArray[] = (isset($data['log_oper_user'])      and !empty($data['log_oper_user']))      ? 'log_oper_user like \'%' . $filter->filter(trim($data['log_oper_user'])) . '%\''           : '';
        $searchArray[] = (isset($data['log_oper_usergroup']) and !empty($data['log_oper_usergroup'])) ? 'log_oper_usergroup like \'%' . $filter->filter(trim($data['log_oper_usergroup'])) . '%\'' : '';
        $searchArray[] = (isset($data['start_log_time'])     and !empty($data['start_log_time']))     ? 'log_time >= ' . strtotime($data['start_log_time'])                                        : '';
        $searchArray[] = (isset($data['end_log_time'])       and !empty($data['end_log_time']))       ? 'log_time <= ' . strtotime($data['end_log_time'])                                          : '';
        $searchArray[] = (isset($data['log_ip'])             and !empty($data['log_ip']))             ? 'log_ip like \'%' . $filter->filter(trim($data['log_ip'])) . '%\''                         : '';
        $searchArray[] = (isset($data['log_content'])        and !empty($data['log_content']))        ? 'log_content like \'%' . $filter->filter(trim($data['log_content'])) . '%\''               : '';

        return array_filter($searchArray);
    }
}
