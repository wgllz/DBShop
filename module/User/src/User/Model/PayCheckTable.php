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
use User\Model\PayCheck as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class PayCheckTable  extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_paycheck';

    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加充值记录
     * @param array $data
     * @return bool|null
     */
    public function addPayCheck(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addPayCheckData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 充值记录列表
     * @param array $pageArray
     * @param array $where
     * @param string $type
     * @return Paginator
     */
    public function listPayCheck(array $pageArray, array $where=array(), $type='admin')
    {
        $select = new Select(array('paycheck'=>$this->table));
        $where  = ($type == 'admin' ? dbshopCheckInData::wherePayCheckData($where) : dbshopCheckInData::frontWherePayCheckData($where));

        $select->where($where)->order('paycheck_id DESC');

        //实例化分页处理
        $pageAdapter = new DbSelect($select, $this->adapter);
        $paginator   = new Paginator($pageAdapter);
        $paginator->setCurrentPageNumber($pageArray['page']);
        $paginator->setItemCountPerPage($pageArray['page_num']);

        return $paginator;
    }
    /**
     * 获取充值信息
     * @param array $where
     * @return array|bool
     */
    public function infoPayCheck(array $where)
    {
        $row = $this->select($where);
        if($row) {
            return $row->current();
        }
        return false;
    }
    /**
     * 充值信息更新
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function updatePayCheck(array $data, array $where)
    {
        $state = $this->update($data, $where);
        if($state) {
            return true;
        }
        return false;
    }
    /**
     * 删除充值信息
     * @param array $where
     * @return bool
     */
    public function delPayCheck(array $where)
    {
        $state = $this->delete($where);
        if($state) {
            return true;
        }
        return false;
    }
}