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
use System\Model\Online as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class OnlineTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_online';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /** 
     * 添加客服信息
     * @param array $data
     * @return number|NULL
     */
    public function addOnline (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addOnlineData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /** 
     * 客服信息列表
     * @param array $where
     * @return Ambigous <multitype:, multitype:NULL multitype: Ambigous <\ArrayObject, unknown> >|NULL
     */
    public function listOnline (array $where=array())
    {
        $result = $this->select(function (Select $select) use ($where) {
            $select->columns(array('*', new Expression('(SELECT g.online_group_name FROM dbshop_online_group as g WHERE g.online_group_id=dbshop_online.online_group_id) as group_name')))
            ->where($where)
            ->order('dbshop_online.online_sort ASC');
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /** 
     * 客服信息
     * @param array $where
     * @return Ambigous <multitype:, ArrayObject, NULL, \ArrayObject, unknown>|NULL
     */
    public function infoOnline (array $where)
    {
        $row = $this->select($where);
        if($row) {
            return $row->current();
        }
        return null;
    }
    /** 
     * 更新客服信息
     * @param array $data
     * @param array $where
     * @return boolean
     */
    public function updateOnline (array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::updateOnlineData($data), $where);
        return true;
    }
    /** 
     * 删除客服信息
     * @param array $where
     * @return boolean|NULL
     */
    public function delOnline (array $where)
    {
        $del = $this->delete($where);
        if($del) {
            return true;
        }
        return null;
    }
}

?>