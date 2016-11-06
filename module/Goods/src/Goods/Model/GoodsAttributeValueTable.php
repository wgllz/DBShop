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
use Goods\Model\GoodsAttributeValue as dbshopCheckInData;
use Zend\Db\Sql\Select;

class GoodsAttributeValueTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_attribute_value';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加属性值
     * @param array $data
     * @return int|null
     */
    public function addAttributeValue(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addAttributeValueData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 编辑属性值信息
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function updateAttributeValue(array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::updateAttributeValueData($data), $where);
        return true;
    }
    /**
     * 批量修改商品属性值信息
     * @param array $data
     * @param array $where
     * @return int
     */
    public function allUpdateGoodsAttributeValue(array $data, array $where)
    {
        return $this->update($data, $where);
    }
    /**
     * 属性值信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoAttributeValue(array $where)
    {
        $result = $this->select(function (Select $select) use ($where){
            $select->join(array('e'=>'dbshop_goods_attribute_value_extend'), 'e.value_id=dbshop_goods_attribute_value.value_id')
            ->where($where);
        });
        if($result) {
            return $result->current();
        }
        return null;        
    }
    /**
     * 属性值列表
     * @param array $where
     * @return array|null
     */
    public function listAttributeValue (array $where)
    {
        $result = $this->select(function (Select $select) use ($where){
           $select->join(array('e'=>'dbshop_goods_attribute_value_extend'), 'e.value_id=dbshop_goods_attribute_value.value_id')
           ->where($where)
           ->order('dbshop_goods_attribute_value.value_sort ASC');
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 属性值删除
     * @param array $where
     * @return bool|null
     */
    public function delAttributeValue(array $where)
    {
        if($this->delete($where)) {
            return true;
        }
        return null;
    }
}

?>