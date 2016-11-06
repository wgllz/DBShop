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

namespace Currency\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Currency\Model\Currency as dbshopCheckInData;

class CurrencyTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_currency';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加货币信息
     * @param array $data
     * @return bool|null
     */
    public function addCurrency (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addCurrencyData($data));
        if($row) {
            return true;
        }
        return null;
    }
    /**
     * 货币信息列表
     * @param array $where
     * @return array|null
     */
    public function listCurrency (array $where=array())
    {
        $result = $this->select($where);
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 获取货币信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoCurrency (array $where)
    {
        $row = $this->select($where);
        if($row) {
            return $row->current();
        }
        return null;
    }
    /**
     * 货币信息更新
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function updateCurrency (array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::updateCurrencyData($data), $where);
        return true;
    }
    /**
     * 删除货币
     * @param array $where
     * @return bool|null
     */
    public function delCurrency (array $where)
    {
        $del = $this->delete($where);
        if($del) {
            return true;
        }
        return null;
    }
}

?>