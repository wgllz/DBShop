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

namespace User\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use User\Model\UserFavorites as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class UserFavoritesTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_user_favorites';

    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加收藏
     * @param array $data
     * @return int|null
     */
    public function addFavorites (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addFavoritesData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 获取收藏信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoFavorites (array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 删除收藏
     * @param array $where
     * @return bool|null
     */
    public function delFavorites (array $where)
    {
        $del = $this->delete($where);
        if($del) {
            return true;
        }
        return null;
    }
    /**
     * 收藏列表
     * @param array $pageArray
     * @param array $where
     * @return Paginator
     */
    public function listFavorites (array $pageArray, array $where=array())
    {
    	$select = new Select(array('dbshop_user_favorites'=>$this->table));

    	$select->columns(array('*', new Expression('(SELECT i.goods_thumbnail_image FROM dbshop_goods_image as i WHERE i.goods_image_id=e.goods_image_id) as goods_thumbnail_image,(SELECT i.goods_title_image FROM dbshop_goods_image as i WHERE i.goods_image_id=e.goods_image_id) as goods_title_image')));
    	$select->join(array('e'=>'dbshop_goods_extend'), 'e.goods_id=dbshop_user_favorites.goods_id', array('goods_name'));
    	$select->where($where)->order('dbshop_user_favorites.time DESC');
    	
    	//实例化分页处理
    	$pageAdapter = new DbSelect($select, $this->adapter);
    	$paginator   = new Paginator($pageAdapter);
    	$paginator->setCurrentPageNumber($pageArray['page']);
    	$paginator->setItemCountPerPage($pageArray['page_num']);
    	
    	return $paginator;
    }
    /**
     * 获取指定数量的收藏商品
     * @param $num
     * @param array $where
     * @return mixed
     */
    public function favoritesNum($num, array $where=array())
    {
        $result = $this->select(function(Select $select) use ($num, $where) {
            $select->join(array('e'=>'dbshop_goods_extend'), 'e.goods_id=dbshop_user_favorites.goods_id', array('goods_name'));
            $select->columns(array('*', new Expression('(SELECT i.goods_thumbnail_image FROM dbshop_goods_image as i WHERE i.goods_image_id=e.goods_image_id) as goods_thumbnail_image,(SELECT i.goods_title_image FROM dbshop_goods_image as i WHERE i.goods_image_id=e.goods_image_id) as goods_title_image')));
            $select->where($where)->order('dbshop_user_favorites.time DESC')->limit($num);
        });

        if($result) {
            return $result->toArray();
        }
        return null;
    }
}