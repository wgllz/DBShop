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

namespace Orders\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Orders\Model\OrderDeliveryAddress as dbshopCheckInData;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\Expression;

class OrderDeliveryAddressTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_order_delivery_address';

    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加订单送货地址
     * @param array $data
     * @return int|null
     */
    public function addDeliveryAddress (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addDeliveryAddressData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 获取订单送货信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoDeliveryAddress (array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 发货信息列表
     * @param array $pageArray
     * @param array $where
     * @param array $order
     * @return Paginator
     */
    public function listDeliveryAddress (array $pageArray, array $where, array $order=array())
    {
        $select = new Select(array('dbshop_order_delivery_address'=>$this->table));
        $select->join(array('o'=>'dbshop_order'), 'o.order_id=dbshop_order_delivery_address.order_id', array('order_time', 'express_time', 'order_state', 'order_sn'));
        $select->where($where);
        $select->order($order);
        
        $pageAdapter = new DbSelect($select, $this->adapter);
        $paginator   = new Paginator($pageAdapter);
        $paginator->setCurrentPageNumber($pageArray['page']);
        $paginator->setItemCountPerPage($pageArray['page_num']);
         
        return $paginator;
    }
    /**
     * 获取发货信息内容，用于发货单导出使用
     * @param array $where
     * @return array|null
     */
    public function listExportDeliveryaddressArray(array $where)
    {
        $result = $this->select(function (Select $select) use($where) {
            $select->join(array('o'=>'dbshop_order'), 'o.order_id=dbshop_order_delivery_address.order_id');
            $select->where($where);
            $select->order('o.order_id DESC');
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 编辑订单送货信息
     * @param array $data
     * @param array $where
     * @return bool|null
     */
    public function updataDeliveryAddress (array $data, array $where)
    {
        $update = $this->update($data, $where);
        if($update) {
            return true;
        }
        return null;
    }
    /**
     * 统计分析配送地区
     * @param $where
     * @param string $order
     * @param string $group
     * @return array|null
     */
    public function statsDelivery($where, $order='', $group='region_name')
    {
        $result = $this->select(function (Select $select) use ($where,$order,$group) {
            $select->join(array('dborder'=>'dbshop_order'), 'dborder.order_id=dbshop_order_delivery_address.order_id');
            $select->columns(array(
                new Expression("substring_index(region_info, ' ', 1) AS region_name"),
                new Expression('count(dborder.order_id) AS d_count')
            ))->where($where);
            if(!empty($order)) $select->order($order);
            if(!empty($group)) $select->group($group);
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
}