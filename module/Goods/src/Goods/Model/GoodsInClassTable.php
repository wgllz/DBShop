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
use Goods\Model\GoodsInClass as dbshopCheckInData;
use Zend\Db\Sql\Select;

class GoodsInClassTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_in_class';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 商品加入分类
     * @param $goodsId
     * @param array $array
     */
    public function addGoodsInClass($goodsId,array $array=array())
    {
        if(is_array($array) and !empty($array)) {
            $goodsIdStr = '';
            foreach ($array as $key => $value) {
                $row = $this->select(array('goods_id'=>$goodsId,'class_id'=>$key))->current();
                if($row == null) {
                    $this->insert(array('goods_id'=>$goodsId,'class_id'=>$key,'class_state'=>$value));
                } else {
                    $this->update(array('class_state'=>$value), array('goods_id'=>$goodsId,'class_id'=>$key));
                }
                $goodsIdStr .= $key.',';
            }
            $goodsIdStr = substr($goodsIdStr,0,-1);
            $this->delete('goods_id='.$goodsId.' and class_id NOT IN ('.$goodsIdStr.')');
        } else {
        	if($goodsId != 0) $this->delete('goods_id='.$goodsId);
        }
    }
    /**
     * 商品所属的分类
     * @param array $where
     * @return array
     */
    public function listGoodsInClass(array $where=array())
    {
        $result     = $this->select($where)->toArray();
        $classArray = array();
        if($result) {
            foreach ($result as $value) {
                $classArray[] = $value['class_id'];
            }
        }
        return $classArray;
    }
    /**
     * 获取单个的分类
     * @param array $where
     * @return array|null
     */
    public function oneGoodsInClass(array $where)
    {
        $result = $this->select(function (Select $select) use ($where) {
            $select->join(array('c'=>'dbshop_goods_class'), 'c.class_id=dbshop_goods_in_class.class_id', '*', 'left')
            ->where($where)
            ->limit(1);
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 更新信息
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateGoodsInClass(array $data, array $where)
    {
        return $this->update($data, $where);
    }
    /**
     * 删除加入分类的商品
     * @param array $where
     * @return int|null
     */
    public function delGoodsInClass(array $where)
    {
        if(!empty($where)) {
            return $this->delete($where);
        }
        return null;
    }
}

?>