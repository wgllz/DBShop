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

namespace Orders\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Orders\Model\OrderLog as dbshopCheckInData;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;

class OrderLogTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_order_log';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加订单历史
     * @param array $data
     * @return int|null
     */
    public function addOrderLog (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addOrderLogData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 订单历史列表
     * @param array $where
     * @return array|null
     */
    public function listOrderLog (array $where)
    {
        $result = $this->select(function (Select $select) use ($where){
           $select->where($where)->order('log_time ASC'); 
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
}