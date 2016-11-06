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
use User\Model\UserMailLog as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class UserMailLogTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_user_mail_log';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 发送给会员的电邮历史记录
     * @param array $data
     * @return int|null
     */
    public function addUserMailLog (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addUserMailLogData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 电邮历史列表
     * @param array $pageArray
     * @param array $where
     * @return Paginator
     */
    public function listMailLog (array $pageArray, array $where)
    {
    	$select = new Select($this->table);
    	$select->where($where)->order('mail_log_id DESC');
    	
    	//实例化分页处理
    	$pageAdapter = new DbSelect($select, $this->adapter);
    	$paginator   = new Paginator($pageAdapter);
    	$paginator->setCurrentPageNumber($pageArray['page']);
    	$paginator->setItemCountPerPage($pageArray['page_num']);
    	
    	return $paginator;
    }
    /**
     * 删除单个电邮历史
     * @param array $where
     * @return int
     */
    public function delMailLog (array $where)
    {
        return $this->delete($where);
    }
}

?>