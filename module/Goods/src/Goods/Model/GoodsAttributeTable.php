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
use Goods\Model\GoodsAttribute as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class GoodsAttributeTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_attribute';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加属性信息
     * @param array $data
     * @return int|null
     */
    public function addAttribute (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addAttributeData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 属性信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoAttribute (array $where)
    {
        $result = $this->select(function (Select $select) use ($where) {
           $select->join(array('e'=>'dbshop_goods_attribute_extend'), 'e.attribute_id=dbshop_goods_attribute.attribute_id')
           ->where($where);
        });
        if($result) {
            return $result->current();
        };
        return null;
    }
    /**
     * 属性信息列表
     * @param array $where
     * @return array|null
     */
    public function listAttribute (array $where=array())
    {
        $result = $this->select(function (Select $select) use ($where){
           $select->join(array('e'=>'dbshop_goods_attribute_extend'), 'e.attribute_id=dbshop_goods_attribute.attribute_id')
           ->columns(array('*',new Expression('(SELECT g.attribute_group_name FROM dbshop_goods_attribute_group_extend AS g WHERE g.attribute_group_id=dbshop_goods_attribute.attribute_group_id) as attribute_group_name')))
           ->where($where)
           ->order('dbshop_goods_attribute.attribute_group_id ASC, dbshop_goods_attribute.attribute_sort ASC'); 
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 属性编辑更新
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function updateAttribute (array $data, array $where)
    {
        $this->update(dbshopCheckInData::updateAttributeData($data), $where);
        return true;
    }
    /**
     * 删除属性
     * @param array $where
     * @return bool|null
     */
    public function delAttribute (array $where)
    {
        if($this->delete($where)) {
            return true;
        }
        return null;
    }
    /**
     * 批量修改商品属性信息
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateGoodsAttribute(array $data, array $where)
    {
        return $this->update($data, $where);
    }
}

?>