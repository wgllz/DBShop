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

namespace Region\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Region\Model\Region as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

class RegionTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_region';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 地区添加
     * @param array $data
     * @return number|NULL
     */
    public function addRegion (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addRegionData($data));
        if($row) {
            $regionId   = $this->getLastInsertValue();

            if(isset($data['region_path']) and $data['region_path'] == 0) {
                $regionPath = $regionId;
            } else {
                $info = $this->select(array('region_id'=>$data['region_top_id']))->current();
                $regionPath = $info->region_path . ',' . $regionId;
                //去除重复的处理
                $array = explode(',', $regionPath);
                $array = array_unique($array);
                $regionPath = implode(',', $array);
            }

            $this->update(array('region_path'=>$regionPath),array('region_id'=>$regionId));
            return $regionId;
        }
        return null;
    }
    /**
     * 地区更新
     * @param array $data
     * @param array $where
     * @return boolean
     */
    public function updateRegion (array $data, array $where) {
        $update = $this->update(dbshopCheckInData::updateRegionData($data),$where);
        return true;
    }
    /**
     * 获取地区信息
     * @param array $where
     * @return NULL
     */
    public function infoRegion (array $where=array()) {
        $row = $this->select(function (Select $select) use ($where) {
            $select->join(array('e'=>'dbshop_region_extend'), 'e.region_id=dbshop_region.region_id',array('*'))
            ->where($where);
        });
        $array = $row->toArray();
        if($array) {
            return $array[0];    
        }
        return null;
    }
    /**
     * 地区列表
     * @param array $where
     * @param string $orderStr
     * @return array|null
     */
    public function listRegion (array $where=array(), $orderStr='dbshop_region.region_sort ASC')
    {
        $result = $this->select(function (Select $select) use ($where, $orderStr) {
            $select->join(array('e'=>'dbshop_region_extend'), 'e.region_id=dbshop_region.region_id',array('*'))
            ->where($where)
            ->order($orderStr);
        });
        
        if($result) {
            return $result->toArray();
        }
        return null;
        
    }
    /**
     * 地区删除
     * @param array $where
     * @return boolean|NULL
     */
    public function delRegion (array $where)
    {
        if($this->delete($where)) {
            $sql = new Sql($this->adapter);
            $sql->prepareStatementForSqlObject($sql->delete('dbshop_region_extend')->where($where))->execute();
            return true;
        }
        return null;
    }
}

?>