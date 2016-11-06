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

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use User\Model\UserIntegralTypeExtend as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

class UserIntegralTypeExtendTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_user_integral_type_extend';

    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    /**
     * 添加积分类型扩展信息
     * @param array $data
     * @return int|null
     */
    public function addUserIntegralTypeExtend (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addUserIntegralTypeExtendData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 积分类型扩展信息更新
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function updateUserIntegralTypeExtend (array $data,array $where)
    {
        $update = $this->update(dbshopCheckInData::updateUserIntegralTypeExtendData($data),$where);
        return true;
    }
}