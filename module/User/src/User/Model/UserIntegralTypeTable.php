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

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use User\Model\UserIntegralType as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

class UserIntegralTypeTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_user_integral_type';

    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    /**
     * 添加积分类型信息
     * @param array $data
     * @return int|null
     */
    public function addUserIntegralType (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addUserIntegralTypeData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 积分类型列表
     * @param array $where
     * @return array|null
     */
    public function listUserIntegralType (array $where=array())
    {
        $result = $this->select(function (Select $select) use ($where) {
            $select->join(array('e'=>'dbshop_user_integral_type_extend'), 'e.integral_type_id=dbshop_user_integral_type.integral_type_id',array('*'))
                ->where($where);
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }

    /**
     * 积分类型信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function userIntegralTypeInfo(array $where)
    {
        $result = $this->select(function (Select $select) use ($where) {
            $select->join(array('e'=>'dbshop_user_integral_type_extend'), 'e.integral_type_id=dbshop_user_integral_type.integral_type_id',array('*'))
                ->where($where);
        });
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 单独获取积分类型基础表信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function userIntegarlTypeOneInfo(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 积分类型信息更新
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function updateUserIntegralType (array $data,array $where)
    {
        $update = $this->update(dbshopCheckInData::updateUserIntegralTypeData($data),$where);
        return true;
    }
}