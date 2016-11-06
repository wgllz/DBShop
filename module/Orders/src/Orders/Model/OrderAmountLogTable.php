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

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use Orders\Model\OrderAmountLog as dbshopCheckInData;
use Zend\Db\Adapter\Adapter;

class OrderAmountLogTable  extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_order_amount_log';

    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加订单金额修改历史
     * @param array $data
     * @return int|null
     */
    public function addOrderAmountLog(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addOrderAmountLogData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 订单金额历史列表
     * @param array $where
     * @return array|null
     */
    public function listOrderAmountLog(array $where)
    {
        $result = $this->select(function(Select $select) use ($where) {
           $select->where($where);
           $select->order('order_edit_amount_time ASC');
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
} 