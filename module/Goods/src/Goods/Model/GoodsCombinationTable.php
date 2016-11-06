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
use Goods\Model\GoodsCombination as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class GoodsCombinationTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_combination';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加组合商品
     * @param array $data
     * @return int|null
     */
    public function addCombinationGoods(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addCombinationGoodsData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 单个组合商品内容
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoCombinationGoods(array $where)
    {
        $result = $this->select(function (Select $select)use($where){
            $select->join(array('e'=>'dbshop_goods_extend'), 'e.goods_id=dbshop_goods_combination.combination_goods_id');
            $select->join(array('g'=>'dbshop_goods'), 'g.goods_id=dbshop_goods_combination.combination_goods_id');
            $select->where($where);
        });
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 组合商品列表
     * @param array $where
     * @param array $order
     * @return array|null
     */
    public function listCombinationGoods(array $where, array $order=array())
    {
        $result = $this->select(function (Select $select)use($where, $order){
            $select->join(array('e'=>'dbshop_goods_extend'), 'e.goods_id=dbshop_goods_combination.combination_goods_id', array('goods_name'))
            ->columns(array('*', new Expression('
                    (SELECT i.goods_thumbnail_image FROM dbshop_goods_image as i WHERE i.goods_image_id=e.goods_image_id) as goods_thumbnail_image,
                    (SELECT i.goods_title_image FROM dbshop_goods_image as i WHERE i.goods_image_id=e.goods_image_id) as goods_title_image,
                    (SELECT in_c.class_id FROM dbshop_goods_in_class as in_c WHERE in_c.goods_id=dbshop_goods_combination.combination_goods_id and in_c.class_state=1 LIMIT 1) as one_class_id'
            )));
    
            $select->join(array('g'=>'dbshop_goods'), 'g.goods_id=dbshop_goods_combination.combination_goods_id');
            if(!empty($order)) $select->order($order);
            $select->where($where);
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 更新关联商品
     * @param array $data
     * @param array $where
     */
    public function updateCombinationGoods(array $data, array $where)
    {
        $this->update($data, $where);
    }
    /**
     * 删除关联商品
     * @param array $where
     */
    public function delCombinationGoods(array $where)
    {
        $this->delete($where);
    }
}