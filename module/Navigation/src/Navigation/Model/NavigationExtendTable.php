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
use Navigation\Model\NavigationExtend as dbshopCheckInData;

class NavigationExtendTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_navigation_extend';

    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /** 
     * 添加导航扩展信息
     * @param array $data
     * @return boolean|NULL
     */
    public function addNavigationExtend (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addNavigationExtendData($data));
        if($row) {
            return true;
        }
        return null;
    }
    /** 
     * 更新导航扩展信息
     * @param array $data
     * @param array $where
     * @return boolean
     */
    public function updateNavigationExtend (array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::updateNavigationExtendData($data), $where);
        return true;
    }
}
?>