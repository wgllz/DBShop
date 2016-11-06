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

namespace Orders\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Orders\Model\OrderRefund as dbshopCheckInData;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class OrderRefundTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_order_refund';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加退货申请
     * @param array $data
     * @return int|null
     */
    public function addOrderRefund (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addOrderRefundData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 退货申请列表
     * @param array $pageArray
     * @param array $where
     * @param string $type
     * @return Paginator
     */
    public function listOrderRefund (array $pageArray, array $where=array(), $type='admin')
    {
        $select = new Select(array('refund' => $this->table));
        $where = ($type == 'admin' ? dbshopCheckInData::whereOrderRefundData($where) : dbshopCheckInData::frontWhereOrderRefundData($where));

        $select->where($where)->order('refund_id DESC');

        //实例化分页处理
        $pageAdapter = new DbSelect($select, $this->adapter);
        $paginator = new Paginator($pageAdapter);
        $paginator->setCurrentPageNumber($pageArray['page']);
        $paginator->setItemCountPerPage($pageArray['page_num']);

        return $paginator;
    }
    /**
     * 获取退货申请信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoOrderRefund(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 更新退货申请信息
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function updateOrderRefund(array $data, array $where)
    {
        $state = $this->update($data, $where);
        if($state) {
            return true;
        }
        return false;
    }
    /**
     * 删除退货申请信息
     * @param array $where
     * @return bool
     */
    public function delOrderRefund(array $where)
    {
        $state = $this->delete($where);
        if($state) {
            return true;
        }
        return false;
    }
}