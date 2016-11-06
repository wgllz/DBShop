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

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Express\Model\ExpressNumber as dbshopCheckInData;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class ExpressNumberTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_express_number';

    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加快递单号
     * @param array $data
     * @return int|null
     */
    public function addExpressNumber(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addExpressNumberData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 单个快递单信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoExpressNumber(array $where)
    {
        $reslut = $this->select($where);
        if($reslut) {
            return $reslut->current();
        }
        return null;
    }
    /**
     * 获取一个快递单信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function oneExpressNumber(array $where)
    {
        $result = $this->select(function(Select $select) use($where){
           $select->where($where)->limit(1);
        });
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 快递单号列表
     * @param array $pageArray
     * @param array $where
     * @return Paginator
     */
    public function listExpressNumber(array $pageArray, array $where=array())
    {
        $select = new Select(array('e'=>$this->table));
        $select->where($where)->order('e.express_number_id DESC');

        //实例化分页处理
        $pageAdapter = new DbSelect($select, $this->adapter);
        $paginator   = new Paginator($pageAdapter);
        $paginator->setCurrentPageNumber($pageArray['page']);
        $paginator->setItemCountPerPage($pageArray['page_num']);

        return $paginator;
    }
    /**
     * 获取数组快递单号
     * @param array $where
     * @return array|null
     */
    public function arrayExpressNumber(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 更新快递单号
     * @param array $data
     * @param array $where
     */
    public function updateExpressNumber(array $data, array $where)
    {
        $this->update($data, $where);
    }
    /**
     * 删除快递单号
     * @param array $where
     * @return int
     */
    public function delExpressNumber(array $where)
    {
        return $this->delete($where);
    }
} 