<?php
/**
 * DBShop 电子商务系统
 *
 * ==========================================================================
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2016 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 * ==========================================================================
 *
 * @author    静静的风
 *
 */

namespace User\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use User\Model\UserMoneyLog as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class UserMoneyLogTable  extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_user_money_log';

    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加会员余额充值记录
     * @param array $data
     * @return bool|null
     */
    public function addUserMoneyLog(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addUserMoneyLogData($data));
        if($row) {
            return true;
        }
        return null;
    }
    /**
     * 余额日志记录列表
     * @param array $pageArray
     * @param array $where
     * @param string $type
     * @return Paginator
     */
    public function listUserMoneyLog(array $pageArray, array $where=array(), $type='admin')
    {
        $select = new Select(array('moneylog'=>$this->table));
        $where  = ($type == 'admin' ? dbshopCheckInData::whereUserMoneyLogData($where) : dbshopCheckInData::frontWhereUserMoneyLogData($where));

        $select->where($where)->order('money_log_id DESC');
        //实例化分页处理
        $pageAdapter = new DbSelect($select, $this->adapter);
        $paginator   = new Paginator($pageAdapter);
        $paginator->setCurrentPageNumber($pageArray['page']);
        $paginator->setItemCountPerPage($pageArray['page_num']);

        return $paginator;
    }
}