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

namespace Links\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Links\Model\Links as dbshopCheckInData;
use Zend\Db\Sql\Select;

class LinksTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_links';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /** 
     * 友情链接添加
     * @param array $data
     * @return boolean|NULL
     */
    public function addLinks (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addLinksData($data));
        if($row) {
            return true;
        }
        return null;
    }
    /** 
     * 友情链接列表
     * @param array $where
     * @return NULL
     */
    public function listLinks (array $where=array())
    {
        $result = $this->select(function (Select $select) use ($where) {
         $select->where($where)
                ->order(array('dbshop_links.links_sort ASC'));
        });

        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /** 
     * 友情链接信息
     * @param array $where
     * @return mixed|NULL
     */
    public function infoLinks (array $where)
    {
        $row = $this->select($where);
        if($row) {
            return $row->current();
        }
        return null;
    }
    /** 
     * 更新友情链接
     * @param array $data
     * @param array $where
     * @return boolean
     */
    public function updateLinks (array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::updataLinksData($data), $where);
        return true;
    }
    /**
     * 批量更新友情链接
     * @param array $data
     * @param array $where
     * @return int
     */
    public function allUpdateLinks (array $data, array $where)
    {
        return $this->update($data, $where);
    }
    /** 
     * 删除友情链接
     * @param array $where
     * @return boolean|NULL
     */
    public function delLinks (array $where)
    {
        $del = $this->delete($where);
        if($del) {
            return true;
        }
        return null;
    }
}

?>