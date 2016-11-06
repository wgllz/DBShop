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
use Goods\Model\Goods as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class GoodsTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        //$this->table       = $this->adapter->dbConfig['dbprefix'] . $this->table;数据表前缀
        $this->initialize();
    }
    /**
     * 商品基本信息
     * @param array $array
     * @return array|\ArrayObject|null|\Zend\Db\ResultSet\ResultSet
     */
    public function infoGoods(array $array)
    {
        $result = $this->select(function (Select $select) use ($array) {
            $select->join(array('e'=>'dbshop_goods_extend'), 'e.goods_id=dbshop_goods.goods_id')
            ->columns(array('*', new Expression('(SELECT i.goods_thumbnail_image FROM dbshop_goods_image as i WHERE i.goods_image_id=e.goods_image_id) as goods_thumbnail_image,(SELECT i.goods_title_image FROM dbshop_goods_image as i WHERE i.goods_image_id=e.goods_image_id) as goods_title_image')));
            //$select->join(array('goods_image'=>'dbshop_goods_image'), 'goods_image.goods_image_id=e.goods_image_id', array('*'), 'left');
            $select->where($array);
        });
        $result = $result->current();
        if($result) {
            return $result;
        }
        return null;
    }
    /**
     * 单独商品表信息，不涉及连表操作
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function oneGoodsInfo(array $where)
    {
        $info = $this->select($where)->current();
        if($info->goods_id) {
            return $info;
        }
        return null;
    }
    /**
     * 商品添加
     * @param array $array
     * @return int|null
     */
    public function addGoods(array $array)
    {
        $row = $this->insert(dbshopCheckInData::addGoodsData($array));
        if ($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 商品更新
     * @param array $array
     * @param array $where
     * @return bool
     */
    public function updateGoods(array $array, array $where)
    {
        $update = $this->update(dbshopCheckInData::updateGoodsData($array),$where);
        if($update) {
            return true;
        }
        return false;
    }
    /**
     * 商品信息更新指定字段
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function oneUpdateGoods(array $data, array $where)
    {
        $update = $this->update($data, $where);
        if($update) {
            return true;
        }
        return false;
    }
    /**
     * 商品数组信息获取
     * @param array $where
     * @param array $interTable
     * @param null $goodsSort
     * @return array
     */
    public function allGoods(array $where=array(), array $interTable=array(), $goodsSort=null, $goodsLimit=null)
    {
        $where  = dbshopCheckInData::whereGoodsData($where);
        $result = $this->select(function (Select $select) use ($where,$interTable,$goodsSort, $goodsLimit) {
        	if(!is_array($goodsSort)) {
                $goodsSort = (!empty($goodsSort) and $goodsSort!='dbshop_goods.goods_id DESC') ? array((strpos($goodsSort, 'dbshop_goods.goods_shop_price')===false ? $goodsSort : new Expression($goodsSort)), 'dbshop_goods.goods_id DESC') : 'dbshop_goods.goods_id DESC';
            }
        
        	$select->join(array('e'=>'dbshop_goods_extend'), 'e.goods_id=dbshop_goods.goods_id')
        	->columns(array('*', new Expression('(SELECT i.goods_thumbnail_image FROM dbshop_goods_image as i WHERE i.goods_image_id=e.goods_image_id) as goods_thumbnail_image, (SELECT in_c.class_id FROM dbshop_goods_in_class as in_c WHERE in_c.goods_id=dbshop_goods.goods_id and in_c.class_state=1 LIMIT 1) as one_class_id')));
        
        	if(isset($interTable['goods_in_class'])) {
        		$select->join(array('goods_in'=>'dbshop_goods_in_class'), 'goods_in.goods_id=dbshop_goods.goods_id');
        	}
        	if(isset($interTable['goods_tag_in_goods'])) {
        		$select->join(array('goods_tag_in'=>'dbshop_goods_tag_in_goods'), 'goods_tag_in.goods_id=dbshop_goods.goods_id');
        	}
        	if(!empty($where)) $select->where($where);
            if(!empty($goodsLimit)) $select->limit($goodsLimit);

        	if(!empty($goodsSort)) {
                if(isset($goodsSort[0]) and $goodsSort[0] == 'rand()')//随机排序，这里有些影响效率，前台相关商品使用
                    $select->order(new Expression($goodsSort[0]));
                else
                    $select->order($goodsSort);
            }
        });
        return $result->toArray();
    }
    /**
     * 商品分页
     * @param array $pageArray
     * @param array $where
     * @param array $interTable
     * @param null $goodsSort
     * @return Paginator
     */
    public function goodsPageList(array $pageArray, array $where=array(), array $interTable=array(), $goodsSort=null)
    {
    	//声明select实例及当前数据表
    	$select = new Select(array('dbshop_goods'=>$this->table));
    	//对$where进行过滤处理
    	$where  = dbshopCheckInData::whereGoodsData($where);
    	//排序处理
        if(!is_array($goodsSort)) {
            $goodsSort = (!empty($goodsSort) and $goodsSort!='dbshop_goods.goods_id DESC') ? array((strpos($goodsSort, 'dbshop_goods.goods_shop_price')===false ? $goodsSort : new Expression($goodsSort)), 'dbshop_goods.goods_id DESC') : 'dbshop_goods.goods_id DESC';
        }

    	//子查询设定处理
    	$select->columns(array('*', new Expression('
    	(SELECT i.goods_thumbnail_image FROM dbshop_goods_image as i WHERE i.goods_image_id=e.goods_image_id) as goods_thumbnail_image,
    	(SELECT in_c.class_id FROM dbshop_goods_in_class as in_c WHERE in_c.goods_id=dbshop_goods.goods_id and in_c.class_state=1 LIMIT 1) as one_class_id,
    	(SELECT SUM(buy_num) FROM dbshop_order_goods AS og INNER JOIN dbshop_order as do ON do.order_id=og.order_id WHERE og.goods_id=dbshop_goods.goods_id and do.order_state!=0) AS buy_num,
    	(SELECT count(favorites_id) FROM dbshop_user_favorites AS uf WHERE uf.goods_id=dbshop_goods.goods_id) AS favorites_num')));
    	//连接表 dbshop_goods_extend 及显示的取出的字段内容，之所以这样处理，因为下面分页处理时，如有两个表有同一个字段信息会报错
    	$select->join(array('e'=>'dbshop_goods_extend'), 'dbshop_goods.goods_id=e.goods_id',
    			array(
    				'goods_name',
    				'goods_info',
    				'goods_body',
    				'goods_extend_name',
    				'goods_keywords',
    				'goods_description',
    				'goods_image_id'
    			)
    	);
    	//连接表 dbshop_goods_in_class
    	if(isset($interTable['goods_in_class'])) {
    		$select->join(array('goods_in'=>'dbshop_goods_in_class'), 'goods_in.goods_id=dbshop_goods.goods_id',
    				array(
    					'class_id',
    					'class_goods_sort',
    					'class_state',
    					'class_recommend'
    			)
    		);
    	}
    	//连接表 dbshop_goods_tag_in_goods
    	if(isset($interTable['goods_tag_in_goods'])) {
    		$select->join(array('goods_tag_in'=>'dbshop_goods_tag_in_goods'), 'goods_tag_in.goods_id=dbshop_goods.goods_id',
    				array(
    					'tag_id',
    					'tag_goods_sort'
    			)
    		);
    	}
    	if(!empty($where)) $select->where($where);
    	if(!empty($goodsSort)) $select->order($goodsSort);

    	//实例化分页处理
    	$pageAdapter = new DbSelect($select, $this->adapter);
    	$paginator   = new Paginator($pageAdapter);
    	$paginator->setCurrentPageNumber($pageArray['page']);
    	$paginator->setItemCountPerPage($pageArray['page_num']);

    	return $paginator;
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
        (SELECT i.goods_thumbnail_image FROM dbshop_goods_image as i WHERE i.goods_image_id=e.goods_image_id) as goods_thumbnail_image,
        (SELECT in_c.class_id FROM dbshop_goods_in_class as in_c WHERE in_c.goods_id=dbshop_goods.goods_id and in_c.class_state=1 LIMIT 1) as one_class_id,
        (SELECT SUM(buy_num) FROM dbshop_order_goods AS og INNER JOIN dbshop_order as do ON do.order_id=og.order_id WHERE og.goods_id=dbshop_goods.goods_id and do.order_state!=0) AS buy_num,
    	(SELECT count(favorites_id) FROM dbshop_user_favorites AS uf WHERE uf.goods_id=dbshop_goods.goods_id) AS favorites_num')));
        $select->join(array('e'=>'dbshop_goods_extend'), 'e.goods_id=dbshop_goods.goods_id',
            	array(
    				'goods_name',
    				'goods_info',
    				'goods_body',
    				'goods_extend_name',
    				'goods_keywords',
    				'goods_description',
    				'goods_image_id'
    			)
        );
        $select->where($where)->where('dbshop_goods.goods_class_have_true=1');
        $select->order($goodsSort);
		
        //实例化分页处理
        $pageAdapter = new DbSelect($select, $this->adapter);
        $paginator   = new Paginator($pageAdapter);
        $paginator->setCurrentPageNumber($pageArray['page']);
        $paginator->setItemCountPerPage($pageArray['page_num']);
        
        return $paginator;
    }
    /**
     * 商品检索
     * @param array $where
     * @param null $goodsSort
     * @param null $limitStr
     * @return array
     */
    public function listGoods (array $where=array(), $goodsSort=null, $limitStr=null)
    {
        $result = $this->select(function (Select $select) use ($where, $goodsSort, $limitStr) {
            if(!is_array($goodsSort)) {
                $goodsSort = !empty($goodsSort) ? (strpos($goodsSort, 'dbshop_goods.goods_shop_price')===false ? $goodsSort : new Expression($goodsSort)) : 'dbshop_goods.goods_id DESC';
            }

    
            $select->join(array('e'=>'dbshop_goods_extend'), 'e.goods_id=dbshop_goods.goods_id');
            $select->where($where);
            if(!empty($goodsSort)) $select->order($goodsSort);
            if(!empty($limitStr)) $select->limit($limitStr);
        });
        return $result->toArray();
    }
    /**
     * 商品删除
     * @param $goodsId
     * @return array|null
     */
    public function delGoods($goodsId)
    {
        if(!is_array($goodsId) and is_numeric($goodsId)) $goodsId = array($goodsId);
        if(is_array($goodsId) and !empty($goodsId)) {
            $where = 'goods_id IN (' . implode(',', $goodsId) . ')';
            //删除数据
            $this->delete($where);
            
            $sql = new Sql($this->adapter);
            $sql->prepareStatementForSqlObject($sql->delete('dbshop_goods_in_class')->where($where))->execute();
            $sql->prepareStatementForSqlObject($sql->delete('dbshop_goods_price_extend_color')->where($where))->execute();
            $sql->prepareStatementForSqlObject($sql->delete('dbshop_goods_price_extend_goods')->where($where))->execute();
            $sql->prepareStatementForSqlObject($sql->delete('dbshop_goods_price_extend_size')->where($where))->execute();
            $sql->prepareStatementForSqlObject($sql->delete('dbshop_goods_price_extend')->where($where))->execute();
            $sql->prepareStatementForSqlObject($sql->delete('dbshop_goods_tag_in_goods')->where($where))->execute();
            $sql->prepareStatementForSqlObject($sql->delete('dbshop_user_favorites')->where($where))->execute();
            $sql->prepareStatementForSqlObject($sql->delete('dbshop_goods_in_attribute')->where($where))->execute();

            $sql->prepareStatementForSqlObject($sql->delete('dbshop_virtual_goods')->where($where))->execute();

            $sql->prepareStatementForSqlObject($sql->delete('dbshop_goods_related')->where($where))->execute();
            $sql->prepareStatementForSqlObject($sql->delete('dbshop_goods_related')->where('related_'.$where))->execute();

            $sql->prepareStatementForSqlObject($sql->delete('dbshop_goods_combination')->where($where))->execute();
            $sql->prepareStatementForSqlObject($sql->delete('dbshop_goods_combination')->where('combination_'.$where))->execute();
            
            return $goodsId;
        }
        return null;
    }

    /**
     * 商品货号自动生成，当$goods_id 存在时，是编辑商品
     * @param $snPrefix
     * @param string $goodsId
     * @return string
     */
    public function autoCreateGoodsItem($snPrefix, $goodsId='') {
        if(empty($goodsId)) {
            $result = $this->select(function  (Select $select)
            {
                $select->columns(array('*', new Expression('MAX(goods_id)+1 as max_goods_id')));
            });
            $s = $result->toArray();
            $maxGoodsId = $s[0]['max_goods_id'];
        } else {
            $maxGoodsId = $goodsId;
        }
        $goodsSn = $snPrefix . str_repeat('0', 6 - strlen($maxGoodsId)) . $maxGoodsId;
        return $goodsSn;
    }
    /**
     * 根据条件获取商品总数
     * @param array $where
     * @return int
     */
    public function countGoods(array $where)
    {
        return $this->select($where)->count();
    }
    /*======================================================下面为mobile中使用的方法语句=============================================*/
    public function mobileGoodsArray(array $where=array(), $goodsSort=null, $goodsLimit=null)
    {
        $result = $this->select(function (Select $select) use ($where, $goodsSort, $goodsLimit) {
            if(!is_array($goodsSort)) {
                $goodsSort = (!empty($goodsSort) and $goodsSort!='dbshop_goods.goods_id DESC') ? array((strpos($goodsSort, 'dbshop_goods.goods_shop_price')===false ? $goodsSort : new Expression($goodsSort)), 'dbshop_goods.goods_id DESC') : 'dbshop_goods.goods_id DESC';
            }

            $select->join(array('e'=>'dbshop_goods_extend'), 'e.goods_id=dbshop_goods.goods_id')
                ->columns(array('*',
                    new Expression('
                    (SELECT i.goods_thumbnail_image FROM dbshop_goods_image as i WHERE i.goods_image_id=e.goods_image_id) as goods_thumbnail_image,
                    (SELECT in_c.class_id FROM dbshop_goods_in_class as in_c WHERE in_c.goods_id=dbshop_goods.goods_id and in_c.class_state=1 LIMIT 1) as one_class_id,
                    (SELECT SUM(buy_num) FROM dbshop_order_goods AS og INNER JOIN dbshop_order as do ON do.order_id=og.order_id WHERE og.goods_id=dbshop_goods.goods_id and do.order_state!=0) AS buy_num,
                    (SELECT count(favorites_id) FROM dbshop_user_favorites AS uf WHERE uf.goods_id=dbshop_goods.goods_id) AS favorites_num')));

            if(!empty($where)) $select->where($where)->where('dbshop_goods.goods_state=1');
            if(!empty($goodsLimit)) $select->limit($goodsLimit);

            if(!empty($goodsSort)) {
                if(isset($goodsSort[0]) and $goodsSort[0] == 'rand()')//随机排序，这里有些影响效率，前台相关商品使用
                    $select->order(new Expression($goodsSort[0]));
                else
                    $select->order($goodsSort);
            }
        });
        return $result->toArray();
    }
}

?>