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
use Zend\Db\Sql\Select;
use Goods\Model\GoodsTagInGoods as dbshopCheckInData;

class GoodsTagInGoodsTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_tag_in_goods';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 标签添加商品，批量添加商品
     * @param $goodsId
     * @param array $data
     */
    public function addTagInGoods ($goodsId, array $data)
    {
        if(isset($data) and is_array($data) and !empty($data)) {
            $tagIdStr = '';
            foreach ($data as $val) {
                $row = $this->select(array('goods_id'=>$goodsId, 'tag_id'=>$val))->current();
                if($row == null) {
                    $this->insert(array('goods_id'=>$goodsId, 'tag_id'=>$val));
                }
                $tagIdStr .= $val . ',';
            }
            $tagIdStr = substr($tagIdStr, 0, -1);
            $this->delete('goods_id='.$goodsId.' and tag_id NOT IN ('.$tagIdStr.')');
        }
    }
    /**
     * 添加单个标签商品
     * @param array $data
     * @return bool|null
     */
    public function addOneTagInGoods(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addTagGoodsData($data));
        if($row) {
            return true;
        }
        return null;
    }
    /**
     * 获取单个标签商品与商品的详细信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function getTagGoodsInfo(array $where)
    {
        $result = $this->select(function (Select $select)use($where){
            $select->join(array('e'=>'dbshop_goods_extend'), 'e.goods_id=dbshop_goods_tag_in_goods.goods_id');
            $select->join(array('g'=>'dbshop_goods'), 'g.goods_id=dbshop_goods_tag_in_goods.goods_id');
            $select->where($where);
        });
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 获取商品对应的标签
     * @param array $where
     * @return array
     */
    public function getInGoodsTag (array $where)
    {
        $result = $this->select($where)->toArray();
        $array  = array();
        if(is_array($result) and !empty($result)) {
            foreach ($result as $value) {
                $array[] = $value['tag_id'];
            }
        }
        return $array;
    }
    /**
     * 删除标签商品
     * @param array $where
     * @return bool|null
     */
    public function delTagInGoods (array $where)
    {
        $del = $this->delete($where);
        if($del) {
            return true;
        }
        return null;
    }
    /**
     * 更新标签插入的商品信息
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateTagInGoods (array $data, array $where)
    {
        return $this->update($data, $where);
    }
}

?>