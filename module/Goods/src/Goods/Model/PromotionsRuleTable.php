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

namespace Goods\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Goods\Model\PromotionsRule as dbshopCheckInData;

class PromotionsRuleTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_promotions_rule';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /** 
     * 添加优惠规则
     * @param array $data
     * @return NULL
     */
    public function addPromotionsRule(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addPromotionsRuleData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 获取优惠规则信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoPromotionsRule(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 输出所有优惠规则
     * @param array $where
     * @return array|null
     */
    public function listPromotionsRule(array $where=array())
    {
        $result = $this->select($where);
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /** 
     * 更新优惠规则
     * @param array $data
     * @param array $where
     */
    public function updatePromotionsRule(array $data, array $where)
    {
        $this->update(dbshopCheckInData::updatePromotionsRuleData($data), $where);
    }
    /** 
     * 删除优惠规则
     * @param array $where
     */
    public function delPromotionsRule(array $where)
    {
        $this->delete($where);
    }
}