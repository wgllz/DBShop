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
use Goods\Model\GoodsAttributeGroup as dbshopCheckInData;
use Zend\Db\Sql\Select;

class GoodsAttributeGroupTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_attribute_group';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加属性组
     * @param array $data
     * @return int|null
     */
    public function addAttributeGroup (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addAttributeGroupData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 获取属性组信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoAttributeGroup(array $where)
    {
        $result = $this->select(function (Select $select) use ($where){
            $select->join(array('e'=>'dbshop_goods_attribute_group_extend'), 'e.attribute_group_id=dbshop_goods_attribute_group.attribute_group_id')
            ->where($where);
            });
            if($result) {
                return $result->current();
            }
            return null;
    }
    /**
     * 商品属性组列表
     * @param array $where
     * @return array|null
     */
    public function listAttributeGroup (array $where=array())
    {
        $result = $this->select(function (Select $select) use ($where) {
            $select->join(array('e'=>'dbshop_goods_attribute_group_extend'), 'e.attribute_group_id=dbshop_goods_attribute_group.attribute_group_id')
            ->where($where)
            ->order('dbshop_goods_attribute_group.attribute_group_sort ASC');
            });
            if($result) {
                return $result->toArray();
            }
            return null;
    }
    /**
     * 编辑商品属性组
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function editAttributeGroup (array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::editAttributeGroupData($data), $where);
        return true;
    }
    /**
     * 属性组删除
     * @param array $where
     * @return bool|null
     */
    public function delAttributeGroup (array $where)
    {
        $del = $this->delete($where);
        if($del) {
            return true;
        }
        return null;
    }
    /**
     * 批量修改商品属性组信息
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateGoodsAttributeGroup(array $data, array $where)
    {
        return $this->update($data, $where);
    }
}

?>