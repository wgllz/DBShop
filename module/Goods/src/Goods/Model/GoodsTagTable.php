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
use Goods\Model\GoodsTag as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class GoodsTagTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_tag';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 标签添加
     * @param array $data
     * @return int|null
     */
    public function addGoodsTag (array $data)
    {
        $data = dbshopCheckInData::addGoodsTagData($data);
        $row = $this->insert($data);
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 获取标签单独信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoGoodsTag (array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /** 
     * 标签更新
     * @param array $data
     * @param array $where
     * @return void|boolean
     */
    public function updateGoodsTag (array $data, array $where)
    {
        $data = dbshopCheckInData::updateGoodsTagData($data);
        if(!$data) return ;

        $this->update($data, $where);
        return true;
    }
    /**
     * 标签列表
     * @param array $where
     * @param array $order
     * @return array|null
     */
    public function listGoodsTag (array $where=array(), array $order=array())
    {
        $result = $this->select(function (Select $select) use ($where, $order) {
           $select->join(array('e'=>'dbshop_goods_tag_extend'), 'e.tag_id=dbshop_goods_tag.tag_id')
           ->columns(array('*', new Expression('
           		(SELECT tg.tag_group_sort FROM dbshop_goods_tag_group as tg WHERE tg.tag_group_id=dbshop_goods_tag.tag_group_id) as tag_group_sort,
           		(SELECT te.tag_group_name FROM dbshop_goods_tag_group_extend as te WHERE te.tag_group_id=dbshop_goods_tag.tag_group_id) as tag_group_name,
           		(SELECT dt.tag_group_mark FROM dbshop_goods_tag_group_extend as dt WHERE dt.tag_group_id=dbshop_goods_tag.tag_group_id) as tag_group_mark,
           		(SELECT COUNT(i.goods_id) FROM dbshop_goods_tag_in_goods AS i WHERE i.tag_id=dbshop_goods_tag.tag_id) as tag_goods_num
           		')))
           ->where($where);
           if($order != '') $select->order($order);
           else $select->order('dbshop_goods_tag.tag_sort ASC');
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 获取标签多数组
     * @param array $where
     * @return array|null
     */
    public function goodsTagArray(array $where=array())
    {
        $result = $this->select($where);
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 删除标签
     * @param array $where
     * @return bool|null
     */
    public function delGoodsTag (array $where)
    {
        $del = $this->delete($where);
        if($del) {
            return true;
        }
        return null;
    }
    /**
     * 批量修改标签排序
     * @param array $data
     */
    public function updateGoodsTagArray(array $data)
    {
        if(is_array($data) and !empty($data)) {
            foreach($data as $key => $val) {
                if(intval($val) == 0) $val = 255;
                $this->update(array('tag_sort'=>$val), array('tag_id'=>$key));
            }
        }
    }
}

?>