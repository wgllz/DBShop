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
use User\Model\UserGroupExtend as dbshopCheckInData;

class UserGroupExtendTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_user_group_extend';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加客户组扩展信息
     * @param array $data
     * @return int|null
     */
    public function addUserGroupExtend (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addUserGroupExtendData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 客户组扩展信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoUserGroupExtend (array $where=array())
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 客户扩展信息列表
     * @param array $where
     * @return array|null
     */
    public function listUserGroupExtend (array $where=array())
    {
        $result = $this->select($where);
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 客户组扩展信息更新
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function updateUserGroupExtend (array $data,array $where)
    {
        $update = $this->update(dbshopCheckInData::updateUserGroupExtendData($data),$where);
        return true;
    }
}

?>