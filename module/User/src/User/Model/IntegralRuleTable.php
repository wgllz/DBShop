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
use User\Model\IntegralRule as dbshopCheckInData;

class IntegralRuleTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_integral_rule';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加积分规则
     * @param array $data
     * @return int|null
     */
    public function addIntegralRule(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addIntegralRuleData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 获取积分规则信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoIntegralRule(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 输出所有积分规则
     * @param array $where
     * @return array|null
     */
    public function listIntegralRule(array $where=array())
    {
        $result = $this->select($where);
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 更新积分规则
     * @param array $data
     * @param array $where
     */
    public function updateIntegralRule(array $data, array $where)
    {
        $this->update(dbshopCheckInData::updateIntegralRuleData($data), $where);
    }
    /**
     * 删除积分规则
     * @param array $where
     */
    public function delIntegralRule(array $where)
    {
        $this->delete($where);
    }
}