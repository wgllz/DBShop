<?php
/**
 * DBShop 电子商务系统
 *
 * ==========================================================================
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2016 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 * ==========================================================================
 *
 * @author    静静的风
 *
 */

namespace User\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use User\Model\WithdrawLog as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class WithdrawLogTable  extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_withdraw_log';

    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加提现记录
     * @param array $data
     * @return bool|null
     */
    public function addWithdrawLog(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addWithdrawLogData($data));
        if($row) {
            return true;
        }
        return null;
    }
    /**
     * 提现记录列表
     * @param array $pageArray
     * @param array $where
     * @param string $type
     * @return Paginator
     */
    public function listWithdrawLog(array $pageArray, array $where=array(), $type='admin')
    {
        $select = new Select(array('withdrawlog'=>$this->table));
        $where  = ($type == 'admin' ? dbshopCheckInData::whereWithdrawLogData($where) : dbshopCheckInData::frontWhereWithdrawLogData($where));

        $select->where($where)->order('withdraw_id DESC');

        //实例化分页处理
        $pageAdapter = new DbSelect($select, $this->adapter);
        $paginator   = new Paginator($pageAdapter);
        $paginator->setCurrentPageNumber($pageArray['page']);
        $paginator->setItemCountPerPage($pageArray['page_num']);

        return $paginator;
    }
    /**
     * 获取提现信息
     * @param array $where
     * @return array|bool
     */
    public function infoWithdrawLog(array $where)
    {
        $row = $this->select($where);
        if($row) {
            return $row->toArray();
        }
        return false;
    }
    /**
     * 提现信息更新
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function updateWithdrawLog(array $data, array $where)
    {
        $state = $this->update($data, $where);
        if($state) {
            return true;
        }
        return false;
    }
    /**
     * 删除提现信息
     * @param array $where
     * @return bool
     */
    public function delWithdrawLog(array $where)
    {
        $state = $this->delete($where);
        if($state) {
            return true;
        }
        return false;
    }
}