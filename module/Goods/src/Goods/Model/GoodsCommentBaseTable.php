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
use Goods\Model\GoodsCommentBase as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class GoodsCommentBaseTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_comment_base';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加商品评价基础信息
     * @param array $data
     * @return int|null
     */
    public function addGoodsCommentBase (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addGoodsCommentBaseData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 更新商品评价基础信息
     * @param array $data
     * @param array $where
     * @return bool|null
     */
    public function updataGoodsCommentBase (array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::updateGoodscommentBaseData($data), $where);
        if($update) {
            return true;
        }
        return null;
    }
    /**
     * 商品评价基础信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function InfoGoodsCommentBase (array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 商品评价列表
     * @param array $pageArray
     * @param array $where
     * @return Paginator
     */
    public function listGoodsCommentBase (array $pageArray, array $where=array())
    {
    	$select = new Select(array('dbshop_goods_comment_base'=>$this->table));
    	
    	$select->join(array('e'=>'dbshop_goods_extend'), 'e.goods_id=dbshop_goods_comment_base.goods_id', array('goods_name'));
    	
    	$select->where($where);
    	$select->order('dbshop_goods_comment_base.comment_last_time DESC');
    	
        //实例化分页处理
        $pageAdapter = new DbSelect($select, $this->adapter);
        $paginator   = new Paginator($pageAdapter);
        $paginator->setCurrentPageNumber($pageArray['page']);
        $paginator->setItemCountPerPage($pageArray['page_num']);
        
        return $paginator;
    }
    /**
     * 删除商品评价
     * @param array $where
     * @return bool|null
     */
    public function delGoodsCommentBase (array $where)
    {
        $del = $this->delete($where);
        if($del) {
            return true;
        }
        return null;
    }
}

?>