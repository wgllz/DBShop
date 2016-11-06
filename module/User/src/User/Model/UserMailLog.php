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

class UserMailLog
{
    private static $dataArray = array();
    
    private static function checkData (array $data)
    {
        self::$dataArray['mail_log_id']  = (isset($data['mail_log_id'])  and !empty($data['user_id']))      ? intval($data['mail_log_id']) : null;
        self::$dataArray['mail_subject'] = (isset($data['mail_subject']) and !empty($data['mail_subject'])) ? trim($data['mail_subject'])  : null;
        self::$dataArray['mail_body']    = (isset($data['mail_body'])    and !empty($data['mail_body']))    ? trim($data['mail_body'])     : null;
        self::$dataArray['send_time']    = (isset($data['send_time'])    and !empty($data['send_time']))    ? trim($data['send_time'])     : null;
        self::$dataArray['user_id']      = (isset($data['user_id'])      and !empty($data['user_id']))      ? intval($data['user_id'])     : null;
        self::$dataArray['send_state']   = (isset($data['send_state'])   and !empty($data['send_state']))   ? intval($data['send_state'])  : null;
    
        return array_filter(self::$dataArray);
    }
    /**
     * 会员邮件发送记录过滤
     * @param array $data
     * @return array
     */
    public static function addUserMailLogData (array $data)
    {
        return self::checkData($data);
    }
}

?>