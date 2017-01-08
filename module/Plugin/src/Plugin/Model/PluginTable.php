<?php
/**
 * Created by PhpStorm.
 * User: binzi
 * Date: 2016/12/29
 * Time: 10:45
 */

namespace Plugin\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Plugin\Model\Plugin as dbshopCheckInData;

class PluginTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_plugin';

    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加插件
     * @param array $data
     * @return int|null
     */
    public function addPlugin(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addPluginData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 获取单独插件信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoPlugin(array $where)
    {
        $info = $this->select($where);
        if($info) {
            return $info->current();
        }
        return null;
    }
    /**
     * 插件列表
     * @param array $where
     * @return mixed
     */
    public function listPlugin(array $where=array())
    {
        $select = new Select($this->table);
        if(!empty($where)) $select->where($where);
        $select->order('plugin_id DESC');

        $resultSet = $this->selectWith($select);
        return $resultSet->toArray();
    }
    /**
     * 编辑更新插件信息
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function updatePlugin(array $data, array $where)
    {
        $update = $this->update($data, $where);
        if($update) {
            return true;
        }
        return false;
    }
    /**
     * 删除插件
     * @param array $where
     * @return int
     */
    public function delPlugin(array $where)
    {
        return $this->delete($where);
    }

}