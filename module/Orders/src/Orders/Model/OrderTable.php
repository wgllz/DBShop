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
use Orders\Model\Order as dbshopCheckInData;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class OrderTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_order';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加保存订单
     * @param array $data
     * @return int|null
     */
    public function addOrder (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addOrderData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 订单信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoOrder (array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 订单列表用于管理后台
     * @param array $pageArray
     * @param array $where
     * @param array $whereArray
     * @return Paginator
     */
    public function listOrder (array $pageArray, array $where=array(), array $whereArray=array())
    {
    	$select = new Select(array('dbshop_order'=>$this->table));
        $where  = dbshopCheckInData::whereOrderData($where);
        
        $select->join(array('a'=>'dbshop_order_delivery_address'), 'a.order_id=dbshop_order.order_id',
        		array('delivery_name'), 'left');
        if(!empty($whereArray)) $select->where($whereArray);
        $select->where($where)->order('dbshop_order.order_id DESC');
        
        //实例化分页处理
        $pageAdapter = new DbSelect($select, $this->adapter);
        $paginator   = new Paginator($pageAdapter);
        $paginator->setCurrentPageNumber($pageArray['page']);
        $paginator->setItemCountPerPage($pageArray['page_num']);
         
        return $paginator;
    }
    /**
     * 订单信息获取
     * @param array $where
     * @param array $interTable
     * @param null $limit
     * @return array
     */
    public function allOrder (array $where=array(),array $interTable=array(), $limit=null)
    {
        $result = $this->select(function (Select $select) use ($where, $limit) {
           if(isset($where['search_order_sn']) and $where['search_order_sn'] != '') $select->where("order_sn like '%" . $where['search_order_sn'] . "%'");
           unset($where['search_order_sn']);
           
           $select->join(array('a'=>'dbshop_order_delivery_address'), 'a.order_id=dbshop_order.order_id',array('delivery_name'), 'left');
           $select->where($where);
           $select->order('dbshop_order.order_id DESC');
           if(isset($limit) and $limit > 0) $select->limit($limit); 
        });
        return $result->toArray();
    }
    /**
     * 前台订单列表
     * @param array $pageArray
     * @param array $where
     * @param array $interTable
     * @return Paginator
     */
    public function orderPageList (array $pageArray, array $where=array(), array $interTable=array())
    {
    	$select = new Select(array('dbshop_order'=>$this->table));
    	
    	if(isset($where['search_order_sn']) and $where['search_order_sn'] != '') $select->where("order_sn like '%" . $where['search_order_sn'] . "%'");
    	unset($where['search_order_sn']);
    	
    	$select->join(array('a'=>'dbshop_order_delivery_address'), 'a.order_id=dbshop_order.order_id', array('delivery_name'), 'left');
    	
    	$select->where($where);
    	$select->order('dbshop_order.order_id DESC');
    	
    	//实例化分页处理
    	$pageAdapter = new DbSelect($select, $this->adapter);
    	$paginator   = new Paginator($pageAdapter);
    	$paginator->setCurrentPageNumber($pageArray['page']);
    	$paginator->setItemCountPerPage($pageArray['page_num']);
    	 
    	return $paginator;
    }
    /**
     * 订单更新
     * @param array $data
     * @param array $where
     * @return bool|null
     */
    public function updateOrder (array $data, array $where)
    {
        $update = $this->update($data, $where);
        if($update) {
            return true;
        }
        return null;
    }
    /**
     * 订单删除
     * @param array $where
     */
    public function delOrder (array $where)
    {
        $this->delete($where);
        
        $sql = new Sql($this->adapter);
        $sql->prepareStatementForSqlObject($sql->delete('dbshop_order_delivery_address')->where($where))->execute();
        $sql->prepareStatementForSqlObject($sql->delete('dbshop_order_goods')->where($where))->execute();
        $sql->prepareStatementForSqlObject($sql->delete('dbshop_order_log')->where($where))->execute();
        $sql->prepareStatementForSqlObject($sql->delete('dbshop_order_amount_log')->where($where))->execute();
    }
    /**
     * 根据商品信息获取订单数量这里用于前台商品页面
     * @param array $where
     * @return int
     */
    public function countOrder (array $where)
    {
        $row = $this->select(function (Select $select) use ($where) {
            $select->join(array('goods'=>'dbshop_order_goods'), 'goods.order_id=dbshop_order.order_id')
            ->where($where)
            ->group('dbshop_order.buyer_id');
        });

        if($row) {
            return $row->count();
        }
        return 0;
    }
    /**
     * 获取不同订单状态总数
     * @param array $where
     * @return int
     */
    public function stateCountOrder (array $where)
    {
        return $this->select($where)->count();
    }
    /**
     * 获取会员订单不同状态的数量(用于前台，因为去掉了退货订单)
     * @param $userId
     * @return array
     */
    public function allStateNumOrder($userId)
    {
        $array = array();
        $array[0]  = $this->select(array('order_state'=>0, 'buyer_id'=>$userId))->count();
        $array[10] = $this->select(array('order_state'=>10, 'buyer_id'=>$userId))->count();
        $array[15] = $this->select(array('order_state'=>15, 'buyer_id'=>$userId))->count();
        $array[20] = $this->select(array('order_state'=>20, 'buyer_id'=>$userId))->count();
        $array[30] = $this->select(array('order_state'=>30, 'buyer_id'=>$userId))->count();
        $array[40] = $this->select(array('order_state'=>40, 'buyer_id'=>$userId, 'refund_state'=>'0'))->count();
        $array[60] = $this->select(array('order_state'=>60, 'buyer_id'=>$userId, 'refund_state'=>'0'))->count();
        $array[-40] = $this->select(array('buyer_id'=>$userId, 'refund_state'=>1))->count();

        return $array;
    }
}