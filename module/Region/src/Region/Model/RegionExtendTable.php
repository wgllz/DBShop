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
use Region\Model\RegionExtend as dbshopCheckInData;

class RegionExtendTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_region_extend';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加地区扩展信息
     * @param array $data
     * @return number|NULL
     */
    public function addRegionExtend (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addRegionExtendData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 更新地区扩展信息
     * @param array $data
     * @param array $where
     * @return boolean
     */
    public function updateRegionExtend (array $data, array $where)
    {
        $this->update(dbshopCheckInData::updateRegionExtendData($data),$where);
        return true;
    }
}

?>