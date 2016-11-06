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
use User\Model\UserAddress as dbshopCheckInData;

class UserAddressTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_user_address';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加收货地址
     * @param array $data
     * @return number|NULL
     */
    public function addAddress (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addAddressData($data));
        if($row) {
            $addressId = $this->getLastInsertValue();
            if(isset($data['addr_default']) and $data['addr_default'] == 1) {
                $this->update(array('addr_default'=>0),'address_id!='.$addressId);
            }
            return $addressId;
        }
        return null;
    }
    /**
     * 收货地址更新
     * @param array $data
     * @param array $where
     * @return boolean
     */
    public function updateAddress (array $data,array $where)
    {
        $update = $this->update(dbshopCheckInData::updateAddressData($data),$where);
        if(isset($data['addr_default']) and $data['addr_default'] == 1) {
            $this->update(array('addr_default'=>0),'address_id!='.$where['address_id']);
        }
        return true;
    }
    /**
     * 收货地址删除
     * @param array $where
     * @return boolean
     */
    public function delAddress (array $where)
    {
        if($this->delete($where)) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * 获取收货地址信息
     * @param array $where
     * @return null
     */
    public function infoAddress (array $where)
    {
        $result = $this->select($where);
        if($result) {
            $result = $result->toArray();
            return $result[0];
        }
        return null;
    }
    /**
     * 收货地址列表
     * @param array $where
     * @return array|null
     */
    public function listAddress (array $where=array())
    {
        $result = $this->select($where);
        if($result) {
            return $result->toArray();
        }
        return null;
    }
}

?>