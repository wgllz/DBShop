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
use Orders\Model\OrderGoods as dbshopCheckInData;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class OrderGoodsTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_order_goods';
    private   $checkInData;
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加订单商品
     * @param array $data
     * @return int|null
     */
    public function addOrderGoods (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addOrderGoodsData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 订单商品列表
     * @param array $where
     * @return array|null
     */
    public function listOrderGoods (array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->toArray();
        }
         return null;
    }
    /**
     * 订单商品分页列表
     * @param array $pageArray
     * @param array $where
     * @return Paginator
     */
    public function pageListOrderGoods(array $pageArray, array $where=array())
    {
        $select = new Select(array('dbshop_order_goods'=>$this->table));
        $select->join(array('dbshop_order'=>'dbshop_order'), 'dbshop_order.order_id=dbshop_order_goods.order_id', array('order_sn', 'order_state'));
        $select->where($where);
        $select->order('dbshop_order_goods.order_goods_id DESC');
        //实例化分页处理
        $pageAdapter = new DbSelect($select, $this->adapter);
        $paginator   = new Paginator($pageAdapter);
        $paginator->setCurrentPageNumber($pageArray['page']);
        $paginator->setItemCountPerPage($pageArray['page_num']);

        return $paginator;
    }
    /**
     * 订单商品信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function InfoOrderGoods (array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 订单商品信息更新
     * @param array $data
     * @param array $where
     * @return bool|null
     */
    public function updateOrderGoods (array $data, array $where)
    {
        $update = $this->update($data, $where);
        if($update) {
            return true;
        }
        return null;
    }
    /**
     * 获取销售商品数量(商品数量，不是件数)
     * @param array $where
     * @return int
     */
    public function countOrderGoods (array $where=array())
    {
        $result = $this->select(function (Select $select) use ($where) {
            $select->join(array('o'=>'dbshop_order'), 'dbshop_order_goods.order_id=o.order_id', '*')
            ->where($where)
            ->where('o.order_state > 0');
        });
        if($result) {
            return $result->count();
        }
        return 0;
    }
    /**
     * 获取商品销售的数量（件数）
     * @param array $where
     * @return int
     */
    public function countOrderGoodsNum (array $where)
    {
        $row = $this->select(function (Select $select) use ($where) {
            $select->join(array('o'=>'dbshop_order'), 'dbshop_order_goods.order_id=o.order_id')
                ->columns(array(new Expression('SUM(dbshop_order_goods.buy_num) as total_buy_num')))
                ->where($where)
                ->where('o.order_state > 0')
                ->group('dbshop_order_goods.goods_id');
        })->current();

        if($row) {
            return $row->total_buy_num;
        }
        return 0;
    }
    /**
     * 会员购买商品的总数
     * @param array $where
     * @return int
     */
    public function buyGoodsUserCount (array $where=array())
    {
        $result = $this->select(function (Select $select) use ($where) {
            $select->join(array('o'=>'dbshop_order'), 'dbshop_order_goods.order_id=o.order_id', '*')
            ->where($where)
            ->where('o.order_state > 0')
            ->group('dbshop_order_goods.buyer_id');
        });
        if($result) {
            return $result->count();
        }
        return 0;
    }
}