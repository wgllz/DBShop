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

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Admin\Model\AdminGroupExtend as dbshopCheckInData;

class AdminGroupExtendTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_admin_group_extend';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加管理员组扩展信息
     * @param array $data
     * @return int|null
     */
    public function addAdminGroupExtend (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addAdminGroupExtendData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 更新管理员扩展信息
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function updateAdminGroupExtend (array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::updateAdminGroupExtendData($data),$where);
        return true;
    }
}

?>