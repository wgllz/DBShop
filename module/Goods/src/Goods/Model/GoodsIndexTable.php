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

namespace Goods\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Goods\Model\GoodsIndex as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class GoodsIndexTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_index';

    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    /**
     * 添加索引内容
     * @param array $indexData
     * @return bool
     */
    public function addGoodsIndex(array $indexData)
    {
        $row = $this->insert(dbshopCheckInData::addGoodsIndexData($indexData));
        if($row) {
            return true;
        }
        return false;
    }
    /**
     * 索引更新
     * @param array $data
     * @param array $where
     */
    public function updateGoodsIndex(array $data, array $where)
    {
        $this->update($data, $where);
    }
    /**
     * 删除指定索引
     * @param array $where
     */
    public function delGoodsIndex(array $where)
    {
        $this->delete($where);
    }
    /**
     * 获取索引中的商品id
     * @param array $where
     * @return int
     */
    public function goodsIndexId(array $where)
    {
        $row = $this->select($where)->current();
        if($row) {
            return $row->goods_id;
        }
        return 0;
    }
    /**
     * 获取索引过的商品数量
     * @param array $where
     * @return int
     */
    public function countGoodsIndex(array $where)
    {
        return $this->select($where)->count();
    }
    /**
     * 商品搜索
     * @param array $pageArray
     * @param array $where
     * @param null $goodsSort
     * @return Paginator
     */
    public function searchGoods (array $pageArray, array $where=array(), $goodsSort=null)
    {
        $select		 = new Select(array('dbshop_goods'=>$this->table));
        $where  = dbshopCheckInData::whereGoodsData($where);

        $goodsSort = !empty($goodsSort) ? (strpos($goodsSort, 'dbshop_goods.goods_shop_price')===false ? $goodsSort : new Expression($goodsSort)) : 'dbshop_goods.goods_id DESC';
        $select->columns(array('*', new Expression('
        (SELECT SUM(buy_num) FROM dbshop_order_goods AS og INNER JOIN dbshop_order as do ON do.order_id=og.order_id WHERE og.goods_id=dbshop_goods.goods_id and do.order_state!=0) AS buy_num,
    	(SELECT count(favorites_id) FROM dbshop_user_favorites AS uf WHERE uf.goods_id=dbshop_goods.goods_id) AS favorites_num')));
        $select->where($where);
        $select->order($goodsSort);

        //实例化分页处理
        $pageAdapter = new DbSelect($select, $this->adapter);
        $paginator   = new Paginator($pageAdapter);
        $paginator->setCurrentPageNumber($pageArray['page']);
        $paginator->setItemCountPerPage($pageArray['page_num']);

        return $paginator;
    }
}