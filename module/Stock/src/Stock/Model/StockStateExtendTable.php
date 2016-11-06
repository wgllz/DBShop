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
use Stock\Model\StockStateExtend as dbshopCheckInData;

class StockStateExtendTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_stock_state_extend';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /** 
     * 添加库存状态扩展
     * @param array $data
     * @return boolean|NULL
     */
    public function addStockStateExtend (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addStockStateExtendData($data));
        if($row) {
            return true;
        }
        return null;
    }
    /** 
     * 编辑库存状态扩展
     * @param array $data
     * @param array $where
     * @return boolean
     */
    public function updateStockStateExtend (array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::updateStockStateExtendData($data), $where);
        return true;
    }
}

?>