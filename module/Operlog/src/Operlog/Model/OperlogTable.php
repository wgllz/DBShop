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

namespace Operlog\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Operlog\Model\Operlog as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class OperlogTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_operlog';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /** 
     * 添加日志
     * @param array $data
     * @return NULL
     */
    public function addOperlog(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addOperlogData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /** 
     * 日志列表
     * @param array $where
     */
    public function allOperlog(array $pageArray, array $where=array())
    {
    	$select = new Select($this->table);
        $where  = dbshopCheckInData::whereOperData($where);
    	$select->where($where)->order('log_time DESC');
    	
    	//实例化分页处理
    	$pageAdapter = new DbSelect($select, $this->adapter);
    	$paginator   = new Paginator($pageAdapter);
    	$paginator->setCurrentPageNumber($pageArray['page']);
    	$paginator->setItemCountPerPage($pageArray['page_num']);
    	
    	return $paginator;
    }
    /** 
     * 清除日志
     * @param array $where
     * @return boolean|NULL
     */
    public function clearOperlog(array $where)
    {
        $del = $this->delete($where);
        if($del) {
            return true;
        }
        return null;
    }
}
