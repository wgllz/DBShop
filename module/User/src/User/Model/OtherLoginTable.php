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

use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use User\Model\OtherLogin as dbshopCheckInData;

class OtherLoginTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_other_login';

    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加第三方登录信息
     * @param array $data
     * @return null
     */
    public function addOtherLogin(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addOtherLoginData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 获取已经存在的信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoOtherLogin(array $where)
    {
        $result = $this->select(function(Select $select) use ($where) {
            $select->join(array('u'=>'dbshop_user'), 'u.user_id=dbshop_other_login.user_id');
            $select->where($where);
        });
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 更新信息
     * @param array $data
     * @param array $where
     */
    public function updateOtherLogin(array $data, array $where)
    {
        $this->update($data, $where);
    }
    /**
     * 删除指定信息
     * @param array $where
     * @return int
     */
    public function delOtherLogin(array $where)
    {
        return $this->delete($where);
    }
} 