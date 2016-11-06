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

namespace User\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use User\Model\IntegralLog as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class IntegralLogTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_integral_log';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加积分记录信息
     * @param array $data
     * @return bool|null
     */
    public function addIntegralLog(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addIntegralLogData($data));
        if($row) {
            return true;
        }
         return null;
    }
    /**
     * 积分记录列表
     * @param array $pageArray
     * @param array $where
     * @return Paginator
     */
    public function listIntegralLog(array $pageArray, array $where=array())
    {
        $select = new Select(array('log'=>$this->table));
        $select->where($where)->order('integral_log_id DESC');
        //实例化分页处理
        $pageAdapter = new DbSelect($select, $this->adapter);
        $paginator   = new Paginator($pageAdapter);
        $paginator->setCurrentPageNumber($pageArray['page']);
        $paginator->setItemCountPerPage($pageArray['page_num']);
         
        return $paginator;
    }
}