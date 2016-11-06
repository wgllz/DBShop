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

namespace Navigation\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Navigation\Model\Navigation as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

class NavigationTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_navigation';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /** 
     * 添加导航信息
     * @param array $data
     * @return NULL
     */
    public function addNavigation(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addNavigationData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /** 
     * 更新导航信息
     * @param array $data
     * @param array $where
     * @return boolean
     */
    public function updateNavigation(array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::updateNavigationData($data), $where);
        return true;
    }
    /** 
     * 导航列表
     * @param array $where
     * @return NULL
     */
    public function listNavigation(array $where=array())
    {
        $result = $this->select(function (Select $select) use ($where) {
           $select->join(array('e'=>'dbshop_navigation_extend'), 'e.navigation_id=dbshop_navigation.navigation_id')
           ->where($where)
           ->order(array('dbshop_navigation.navigation_type ASC', 'dbshop_navigation.navigation_sort ASC'));
        });
        
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /** 
     * 获取单个导航信息
     * @param array $where
     * @return mixed|NULL
     */
    public function infoNavigation (array $where)
    {
        $row = $this->select(function (Select $select) use ($where) {
            $select->join(array('e'=>'dbshop_navigation_extend'), 'e.navigation_id=dbshop_navigation.navigation_id')
            ->where($where);            
        });
        if($row) {
            return $row->current();
        }
        return null;
    }
    /**
     * 批量更新导航排序
     * @param array $data
     * @param array $where
     * @return int
     */
    public function allUpdateNavigation (array $data, array $where)
    {
        return $this->update($data, $where);
    }
    /** 
     * 删除导航信息
     * @param array $where
     * @return boolean|NULL
     */
    public function delNavigation (array $where)
    {
        $del = $this->delete($where);
        if($del) {
            $sql = new Sql($this->adapter);
            $sql->prepareStatementForSqlObject($sql->delete('dbshop_navigation_extend')->where($where))->execute();
            return true;
        }
        return null;
    }
}

?>