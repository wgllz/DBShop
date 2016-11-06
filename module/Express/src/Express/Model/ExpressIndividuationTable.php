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

namespace Express\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Express\Model\ExpressIndividuation as dbshopCheckInData;

class ExpressIndividuationTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_express_individuation';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加配送个性化信息
     * @param array $data
     * @return int|null
     */
    public function addExpressIndividuation (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addExpressIndividuationData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 更新配送个性化信息
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function updateExpressIndividuation (array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::updateExpressIndividuationData($data), $where);
        if($update) {
            return true;
        }
        return false;
    }
    /**
     * 获取配送个性化信息列表
     * @param array $where
     * @return array|null
     */
    public function listExpressIndividuation (array $where=array())
    {
        $result = $this->select($where);
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 获取配送个性化信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoExpressIndividuation (array $where)
    {
        $row = $this->select($where);
        if($row) {
            return $row->current();
        }
        return null;
    }
    /**
     * 删除配送个性化信息
     * @param array $where
     * @return bool|null
     */
    public function delExpressIndividuation (array $where)
    {
        $del = $this->delete($where);
        if($del) {
            return true;
        }
        return null;
    }
}

?>