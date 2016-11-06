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

namespace System\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use System\Model\OnlineGroup as dbshopCheckInData;
use Zend\Db\Sql\Select;

class OnlineGroupTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_online_group';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加客服组
     * @param array $data
     * @return bool|null
     */
    public function addOnlineGroup (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addOnlineGroupData($data));
        if($row) {
            return true;
        }
        return null;
    }
    /**
     * 客户组列表
     * @param array $where
     * @return array|null
     */
    public function listOnlineGroup (array $where=array())
    {
        $result = $this->select(function (Select $select) use ($where) {
            $select->where($where)
            ->order('dbshop_online_group.online_group_sort ASC');
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 客户组信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoOnlineGroup (array $where)
    {
        $row = $this->select($where);
        if($row) {
            return $row->current();
        }
        return null;
    }
    /**
     * 客户组信息更新
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function updateOnlineGroup (array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::updateOnlineGroupData($data), $where);
        return true;
    }
    /**
     * 客户组删除
     * @param array $where
     * @return bool|null
     */
    public function delOnlineGroup (array $where)
    {
        $del = $this->delete($where);
        if($del) {
            return true;
        }
        return null;
    }
}

?>