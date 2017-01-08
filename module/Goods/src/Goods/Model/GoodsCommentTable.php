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
use Goods\Model\GoodsComment as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class GoodsCommentTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_comment';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 商品评价添加
     * @param array $data
     * @return int|null
     */
    public function addGoodsComment (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addGoodsCommentData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 商品评价列表，对应商品id
     * @param array $pageArray
     * @param array $where
     * @param bool $userAvatar
     * @return Paginator
     */
    public function listGoodsComment (array $pageArray, array $where, $userAvatar=false)
    {
    	$select = new Select(array('dbshop_goods_comment'=>$this->table));
    	if($userAvatar) {
    		$select->columns(array('*', new Expression('
    		(SELECT u.user_avatar FROM dbshop_user as u WHERE u.user_name=dbshop_goods_comment.comment_writer) AS user_avatar
    		')));
    	} else {
            $select->columns(array('*', new Expression('
    		(SELECT e.goods_name FROM dbshop_goods_extend as e WHERE e.goods_id=dbshop_goods_comment.goods_id) AS goods_name
    		')));
        }
    	$select->where($where)->order('dbshop_goods_comment.comment_time DESC');
    	
        //实例化分页处理
        $pageAdapter = new DbSelect($select, $this->adapter);
        $paginator   = new Paginator($pageAdapter);
        $paginator->setCurrentPageNumber($pageArray['page']);
        $paginator->setItemCountPerPage($pageArray['page_num']);
        
        return $paginator;
    }
    /**
     * 获取商品评价数
     * @param array $where
     * @param bool $userAvatar
     * @return array|null
     */
    public function allGoodsComment(array $where, $userAvatar=false)
    {
    	$result = $this->select(function (Select $select) use ($where, $userAvatar) {
    		if($userAvatar) {
    			$select->columns(array('*', new Expression('(SELECT u.user_avatar FROM dbshop_user as u WHERE u.user_name=dbshop_goods_comment.comment_writer) AS user_avatar')));
    		}
    		$select->where($where)->order('comment_time DESC');
    	});
    	
    	if($result) {
    		return $result->toArray();
    	}
    	return null;
    }
    /**
     * 商品评价删除单个评价
     * @param array $where
     * @return bool|null
     */
    public function delGoodsComment (array $where)
    {
        $del = $this->delete($where);
        if($del) {
            return true;
        }
        return null;
    }
    /**
     * 商品评价信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoGoodsComment (array $where)
    {
        $row = $this->select($where);
        if($row) {
            return $row->current();
        }
        return null;
    }
    /**
     * 更新商品评价信息
     * @param array $data
     * @param array $where
     * @return bool|null
     */
    public function updateGoodsComment(array $data, array $where)
    {
        $update = $this->update($data, $where);
        if($update) {
            return true;
        }
        return null;
    }
    /**
     * 计算商品评分
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function calculateGoodsRating(array $where)
    {
        $result = $this->select(function (Select $select) use ($where) {
            $select->columns(array('*',new Expression('SUM(dbshop_goods_comment.goods_evaluation)/count(dbshop_goods_comment.comment_id) as rating')));
            $select->where($where);
            $select->group('dbshop_goods_comment.goods_id');
        });
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 获得商品评价总数
     * @return int
     */
    public function goodsCommentCount()
    {
        $count = $this->select()->count();

        return $count;
    }
}

?>