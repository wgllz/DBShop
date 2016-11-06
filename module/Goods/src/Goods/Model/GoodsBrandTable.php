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
use Goods\Model\GoodsBrand as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class GoodsBrandTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_brand';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 商品品牌添加
     * @param array $array
     * @return int|null
     */
    public function addBrand (array $array)
    {
        $row = $this->insert(dbshopCheckInData::addGoodsBrandData($array));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null; 
    }
    /**
     * 商品品牌信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoBrand (array $where=array())
    {
       $result = $this->select(function (Select $select) use ($where) {
           $select->join(array('e'=>'dbshop_goods_brand_extend'), 'e.brand_id=dbshop_goods_brand.brand_id')
           ->where($where);
       });
       
       if($result) {
           return $result->current();
       }
       return null;
    }
    /**
     * 商品品牌删除
     * @param array $where
     * @return bool|null
     */
    public function delBrand (array $where)
    {
        $del = $this->delete($where);
        if($del) {
            return true;
        }
        return null;
    }
    /**
     * 商品品牌列表
     * @param array $where
     * @param int $limit
     * @return array|null
     */
    public function listGoodsBrand (array $where=array(), $limit=0)
    {
        $result = $this->select(function (Select $select) use ($where,$limit) {
           $select->join(array('e'=>'dbshop_goods_brand_extend'), 'e.brand_id=dbshop_goods_brand.brand_id',array('*'))
           ->columns(array('*',new Expression('(SELECT COUNT(g.goods_id) FROM dbshop_goods as g WHERE g.brand_id=dbshop_goods_brand.brand_id) AS goods_num')))
           ->where($where);
           if($limit > 0) {
               $select->limit($limit);
           }
           $select->order('dbshop_goods_brand.brand_sort ASC'); 
        });
        
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 商品品牌更新
     * @param array $array
     * @param array $where
     * @return bool|null
     */
    public function updateGoodsBrand(array $array,array $where) {
        $update = $this->update(dbshopCheckInData::updateGoodsBrandData($array),$where);
        if($update) {
            return true;
        }
        return null;
    }
    /**
     * 批量修改商品品牌信息
     * @param array $array
     * @param array $where
     * @return int
     */
    public function allBrandUpdate(array $array,array $where)
    {
        return $this->update($array, $where);
    }
}

?>