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

namespace Express\Model;

use Zend\Db\Sql\Expression;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Express\Model\Express as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

class ExpressTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_express';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加配送方式
     * @param array $data
     * @return int|null
     */
    public function addExpress (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addExpressData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 配送方式列表
     * @param array $where
     * @return array|null
     */
    public function listExpress (array $where=array())
    {
        $result = $this->select(function (Select $select) use ($where) {
            $select->where($where)->order('express_sort ASC');
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 批量发货列表
     * @param array $where
     * @return array|null
     */
    public function orderExpressList(array $where=array())
    {
        $result = $this->select(function(Select $select) use ($where) {
            $select->columns(array('*', new Expression(
                '(SELECT count(o.order_id) FROM dbshop_order as o WHERE o.express_id=dbshop_express.express_id and (o.order_state=20 or o.order_state=30)) as order_num,
                (SELECT count(n.express_number_id) FROM dbshop_express_number as n WHERE n.express_id=dbshop_express.express_id and n.express_number_state=0) as express_number_total,
                (SELECT count(nu.express_number_id) FROM dbshop_express_number as nu WHERE nu.express_id=dbshop_express.express_id and nu.express_number_state=1) as used_express_number_total'
            )));
            $select->where($where);
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 获取单个配送方式信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoExpress (array $where)
    {
        $row = $this->select($where);
        if($row) {
            return $row->current();
        }
        return null;
    }
    /**
     * 编辑配送信息
     * @param array $data
     * @param array $where
     * @return bool|null
     */
    public function updateExpress (array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::updateExpressData($data), $where);
        if($update) {
            return true;
        }
        return null;
    }
    /**
     * 删除配送方式
     * @param array $where
     * @return bool|null
     */
    public function delExpress (array $where)
    {
        $del = $this->delete($where);
        if($del) {
            $sql = new Sql($this->adapter);
            $sql->prepareStatementForSqlObject($sql->delete('dbshop_express_individuation')->where($where))->execute();
            
            return true;
        }
        return null;
    }
}

?>