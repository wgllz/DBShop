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
use Goods\Model\GoodsTagGroup as dbshopCheckInData;
use Zend\Db\Sql\Select;

class GoodsTagGroupTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_tag_group';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /** 
     * 添加标签组
     * @param array $data
     * @return NULL
     */
    public function addTagGroup (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addTagGroupData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 编辑标签组
     * @param array $data
     * @param array $where
     * @return boolean
     */
    public function editTagGroup (array $data, array $where)
    {
        $editState = $this->update(dbshopCheckInData::editTagGroupData($data),$where);
        return true;
    }
    /**
     * 获取标签组信息
     * @param array $where
     * @return array|null
     */
    public function infoTagGroup (array $where)
    {
        $result = $this->select(function (Select $select) use ($where) {
            $select->join(array('e'=>'dbshop_goods_tag_group_extend'), 'dbshop_goods_tag_group.tag_group_id=e.tag_group_id', array('*'));
            $select->where($where);
        });
        if($result) {
            return $result->toArray();
        }
        return null;        
    }
    /**
     * 标签组列表
     * @param array $where
     * @return array|null
     */
    public function listTagGroup (array $where=array())
    {
        $result = $this->select(function (Select $select) use ($where) {
           $select->join(array('e'=>'dbshop_goods_tag_group_extend'), 'dbshop_goods_tag_group.tag_group_id=e.tag_group_id', array('*'));
           $select->where($where)
           ->order('tag_group_sort ASC'); 
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 标签组删除
     * @param array $where
     * @return bool|null
     */
    public function delTagGroup (array $where)
    {
        $del = $this->delete($where);
        if($del) return true;
        return null;
    }
    /**
     * 批量修改标签组信息
     * @param array $data
     * @param array $where
     * @return int
     */
    public function allTagGroupUpdate(array $data, array $where)
    {
        return $this->update($data, $where);
    }
}

?>