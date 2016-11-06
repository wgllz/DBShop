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
use Admin\Model\Admin as dbshopCheckInData;
use Zend\Db\Sql\Select;

class AdminTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_admin';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加管理员
     * @param array $data
     * @return null
     */
    public function addAdmin (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addAdminData($data));
        if($row) {
            $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 管理员更新
     * @param array $data
     * @param array $where
     * @return bool|null
     */
    public function updateAdmin (array $data,array $where)
    {
        $update = $this->update(dbshopCheckInData::updateAdminData($data),$where);
        if($update) {
            return true;
        }
        return null;
    }
    /**
     * 管理员列表
     * @param array $where
     * @return array|null
     */
    public function listAdmin (array $where=array())
    {
        $result = $this->select(function (Select $select) use ($where) {
           $select->join(array('g'=>'dbshop_admin_group_extend'), 'g.admin_group_id=dbshop_admin.admin_group_id')
           ->where($where); 
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 获得管理员信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoAdmin (array $where)
    {
        $row = $this->select(function (Select $select) use ($where) {
           $select->join(array('g'=>'dbshop_admin_group_extend'), 'g.admin_group_id=dbshop_admin.admin_group_id')
           ->where($where); 
        });
        if($row) {
            return $row->current();
        }
        return null;
    }
    /**
     * 管理员删除操作
     * @param array $where
     * @return bool|null
     */
    public function delAdmin (array $where)
    {
       $info = $this->select($where)->current();
       if($info->admin_id == 1) {//判断是否为创始人，如果是，无法删除
           return null;
       } 
       $del = $this->delete($where);
       if($del) {
           return true;
       }
       return null;
    }
}

?>