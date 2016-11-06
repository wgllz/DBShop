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

namespace Stock\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Stock\Model\StockState as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

class StockStateTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_stock_state';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /** 
     * 添加库存状态
     * @param array $data
     * @return number|NULL
     */
    public function addStockState (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addStockStateData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /** 
     * 库存状态列表
     * @param array $where
     * @return NULL
     */
    public function listStockState (array $where=array())
    {
        $result = $this->select(function (Select $select) use ($where) {
           $select->join(array('e'=>'dbshop_stock_state_extend'), 'dbshop_stock_state.stock_state_id=e.stock_state_id', array('*'))
           ->where($where)
           ->order('dbshop_stock_state.stock_type_state ASC, dbshop_stock_state.state_sort ASC'); 
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /** 
     * 获取库存状态信息
     * @param array $where
     * @return Ambigous <multitype:, ArrayObject, NULL, \ArrayObject, unknown>|NULL
     */
    public function infoStockState (array $where)
    {
        $row = $this->select(function (Select $select) use ($where) {
            $select->join(array('e'=>'dbshop_stock_state_extend'), 'dbshop_stock_state.stock_state_id=e.stock_state_id', array('*'))
            ->where($where);
        });
        if($row) {
            return $row->current();
        }
        return null;
    }
    /** 
     * 编辑库存状态信息
     * @param array $data
     * @param array $where
     * @return boolean
     */
    public function updateStockState (array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::updateStockStateData($data), $where);
        return true;
    }
    /** 
     * 删除库存状态信息
     * @param array $where
     * @return string
     */
    public function delStockState (array $where)
    {
        $row = $this->select($where);
        if(!row) return 'false';
        if($row->current()->state_type == 1) return 'false';
        
        if($this->delete($where)) {
            $sql = new Sql($this->adapter);
            $sql->prepareStatementForSqlObject($sql->delete('dbshop_stock_state_extend')->where($where))->execute();
            return 'true';
        }
        return 'false';
    }
}

?>