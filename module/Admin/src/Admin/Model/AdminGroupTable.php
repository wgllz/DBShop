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

namespace Admin\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Admin\Model\AdminGroup as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

class AdminGroupTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_admin_group';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 管理员组添加
     * @param array $data
     * @return int|null
     */
    public function addAdminGroup(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addAdminGroupData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 管理员组列表
     * @param array $where
     * @return array|null
     */
    public function listAdminGroup (array $where=array())
    {
        $result = $this->select(function (Select $select) use ($where) {
            $select->join(array('e'=>'dbshop_admin_group_extend'), 'e.admin_group_id=dbshop_admin_group.admin_group_id')
            ->where($where);
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 管理员组信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoAdminGroup (array $where)
    {
        $row = $this->select(function (Select $select) use ($where) {
            $select->join(array('e'=>'dbshop_admin_group_extend'), 'e.admin_group_id=dbshop_admin_group.admin_group_id')
            ->where($where);
        });
        if($row) {
            return $row->current();
        }
        return null;
    }
    /**
     * 管理员组删除
     * @param array $where
     * @return bool|null
     */
    public function delAdminGroup (array $where)
    {
        $del = $this->delete($where);
        if($del) {
            $sql = new Sql($this->adapter);
            $sql->prepareStatementForSqlObject($sql->delete('dbshop_admin_group_extend')->where($where))->execute();
            return true;
        }
        return null;
    }
    /**
     * 编辑管理组
     * @param array $data
     * @param array $where
     * @return bool|null
     */
    public function updateAdminGroup(array $data, array $where)
    {
        $update = $this->update($data, $where);
        if($update) {
            return true;
        }
        return null;
    }
}

?>