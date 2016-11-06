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

namespace Goods\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Goods\Model\GoodsAsk as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class GoodsAskTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_ask';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 商品咨询添加
     * @param array $data
     * @return int|null
     */
    public function addGoodsAsk(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addGoodsAskData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 商品咨询列表
     * @param array $pageArray
     * @param array $where
     * @return Paginator
     */
    public function listGoodsAsk(array $pageArray, array $where=array())
    {
        $select = new Select(array('dbshop_goods_ask'=>$this->table));
        
        $select->join(array('e'=>'dbshop_goods_extend'), 'e.goods_id=dbshop_goods_ask.goods_id', array('goods_name'));
        $select->join(array('u'=>'dbshop_user'), 'u.user_name=dbshop_goods_ask.ask_writer', array('user_avatar'), 'left');

        $select->where($where);
        $select->order('dbshop_goods_ask.ask_time DESC');
        
        //实例化分页处理
        $pageAdapter = new DbSelect($select, $this->adapter);
        $paginator   = new Paginator($pageAdapter);
        $paginator->setCurrentPageNumber($pageArray['page']);
        $paginator->setItemCountPerPage($pageArray['page_num']);
        
        return $paginator;
    }
    /**
     * 获取指定数量的咨询信息
     * @param $num
     * @param array $where
     * @return array|null
     */
    public function numGoodsAsk($num, array $where=array())
    {
        $result = $this->select(function(Select $select) use ($num, $where) {
            $select->join(array('e'=>'dbshop_goods_extend'), 'e.goods_id=dbshop_goods_ask.goods_id', array('goods_name'));
            $select->where($where);
            $select->order('dbshop_goods_ask.ask_time DESC');
            $select->limit($num);
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 商品咨询信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoGoodsAsk(array $where)
    {
    	$result = $this->select($where);
    	if($result) {
    		return $result->current();
    	}
    	return null;
    }
    /**
     * 删除商品咨询信息
     * @param array $where
     * @return bool|null
     */
    public function delGoodsAsk(array $where)
    {
    	$del = $this->delete($where);
    	if($del) {
    		return true;
    	}
    	return null;
    }
    /**
     * 更新商品咨询信息
     * @param array $data
     * @param array $where
     * @return bool|null
     */
    public function updateGoodsAsk(array $data, array $where)
    {
    	$update = $this->update($data, $where);
    	if($update) {
    		return true;
    	}
    	return null;
    }
    /**
     * 商品咨询总数
     * @param array $where
     * @return int
     */
    public function countGoodsAsk(array $where=array())
    {
        return $this->select($where)->count();
    }
}

?>