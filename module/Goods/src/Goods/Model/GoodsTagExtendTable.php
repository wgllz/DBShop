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
use Goods\Model\GoodsTagExtend as dbshopCheckInData;
use Zend\Db\Sql\Select;

class GoodsTagExtendTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_tag_extend';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加标签扩展信息
     * @param array $data
     * @return int|null
     */
    public function addTagExtend (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addGoodsExtendData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 获取标签信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoTagExtend (array $where=array())
    {
        $row = $this->select(function (Select $select) use ($where) {
            $select->join(array('a'=>'dbshop_goods_tag'), 'a.tag_id=dbshop_goods_tag_extend.tag_id')
            ->where($where);
        });
        
        if($row) {
            return $row->current();
        }
        return null;
    }
    /**
     * 更新扩展信息
     * @param array $data
     * @param array $where
     * @return bool|null
     */
    public function updateTagExtend (array $data, array $where=array())
    {
        $update = $this->update(dbshopCheckInData::updateGoodsExtendData($data), $where);
        if($update) {
            return true;
        }
        return null;
    }
    /**
     * 删除扩展信息
     * @param array $where
     * @return bool|null
     */
    public function delTagExtend (array $where)
    {
        $del = $this->delete($where);
        if($del) {
            return true;
        }
        return null;
    }

}

?>