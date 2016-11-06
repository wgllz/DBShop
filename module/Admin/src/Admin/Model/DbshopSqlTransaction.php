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

namespace Admin\Model;

use Zend\Db\Adapter\Adapter;

class DbshopSqlTransaction implements \Zend\Db\Adapter\AdapterAwareInterface
{
    private $adapter;
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }
    /**
     * 事务开始启用
     */
    public function DbshopTransactionBegin()
    {
        $this->adapter->getDriver()->getConnection()->beginTransaction();
    }
    /** 
     * 事务回滚取消
     */
    public function DbshopTransactionRollback()
    {
        $this->adapter->getDriver()->getConnection()->rollback();
    }
    /** 
     * 事务确认完成
     */
    public function DbshopTransactionCommit()
    {
        $this->adapter->getDriver()->getConnection()->commit();
    }    
}