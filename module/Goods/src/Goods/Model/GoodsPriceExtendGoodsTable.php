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
use Goods\Model\GoodsPriceExtendGoods as dbshopCheckInData;
use Zend\Db\Sql\Select;

class GoodsPriceExtendGoodsTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_price_extend_goods';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加销售属性商品
     * @param array $array
     * @return bool|null
     */
    public function addPriceExtendGoods (array $array=array())
    {
        $row = $this->insert(dbshopCheckInData::addGoodsPriceExtendGoodsData($array));
        if($row) {
            return true;
        }
        return null;
    }
    /**
     * 销售属性商品列表
     * @param array $where
     * @return array|null
     */
    public function listPriceExtendGoods(array $where)
    {
        $result = $this->select(function (Select $select) use ($where) {
           $select->where($where)->order('goods_color ASC'); 
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 获取销售属性商品单个信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function InfoPriceExtendGoods(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 更新销售属性商品
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function updatePriceExtendGoods(array $data, array $where)
    {
        $update = $this->update($data, $where);
        if($update) {
            return true;
        }
        return false;
    }
    /**
     * 删除销售属性商品列表
     * @param array $where
     * @return bool|null
     */
    public function delPriceExtendGoods (array $where)
    {
        $delState = $this->delete($where);
        if($delState) {
            return true;
        }
        return null;
    }
}

?>