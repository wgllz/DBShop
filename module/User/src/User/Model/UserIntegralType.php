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

class UserIntegralType {
    private static $dataArray = array();

    /**
     *
     * @param array $data
     * @return multitype:
     */
    private static function checkData (array $data)
    {

        self::$dataArray['integral_type_id']     = (isset($data['integral_type_id'])   and !empty($data['integral_type_id']))   ? intval($data['integral_type_id']) : null;
        self::$dataArray['integral_type_mark']   = (isset($data['integral_type_mark']) and !empty($data['integral_type_mark'])) ? trim($data['integral_type_mark']) : null;

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['integral_currency_con'] = (isset($data['integral_currency_con']) and intval($data['integral_currency_con'])>0) ? intval($data['integral_currency_con']) : 0;
        self::$dataArray['default_integral_num'] = (isset($data['default_integral_num']) and !empty($data['default_integral_num'])) ? intval($data['default_integral_num']) : 0;

        return self::$dataArray;
    }

    /**
     * 添加积分类型过滤
     * @param array $data
     * @return multitype
     */
    public static function addUserIntegralTypeData(array $data)
    {
        return self::checkData($data);
    }

    /**
     * 编辑积分类型过滤
     * @param array $data
     * @return multitype
     */
    public static function updateUserIntegralTypeData(array $data)
    {
        unset($data['integral_type_id']);
        $data = self::checkData($data);

        return $data;
    }
}