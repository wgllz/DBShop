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

class UserIntegralTypeExtend {
    private static $dataArray = array();

    /**
     *
     * @param array $data
     * @return multitype:
     */
    private static function checkData (array $data)
    {

        self::$dataArray['integral_type_id']   = (isset($data['integral_type_id'])   and !empty($data['integral_type_id']))   ? intval($data['integral_type_id']) : null;
        self::$dataArray['integral_type_name'] = (isset($data['integral_type_name']) and !empty($data['integral_type_name'])) ? trim($data['integral_type_name']) : null;
        self::$dataArray['language']           = (isset($data['language'])           and !empty($data['language']))           ? trim($data['language'])           : null;

        self::$dataArray = array_filter(self::$dataArray);

        return self::$dataArray;
    }

    /**
     * 添加积分类型扩展信息过滤
     * @param array $data
     * @return multitype
     */
    public static function addUserIntegralTypeExtendData(array $data)
    {
        return self::checkData($data);
    }

    /**
     * 编辑积分类型扩展信息过滤
     * @param array $data
     * @return multitype
     */
    public static function updateUserIntegralTypeExtendData(array $data)
    {
        unset($data['integral_type_id']);
        $data = self::checkData($data);

        return $data;
    }
}