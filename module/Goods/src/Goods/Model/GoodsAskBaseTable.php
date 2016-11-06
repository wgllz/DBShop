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

//暂时不使用，以后需要时再次使用

namespace Goods\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Goods\Model\GoodsAskBase as dbshopCheckInData;

class GoodsAskBaseTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_ask_base';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加商品咨询基础信息
     * @param array $data
     * @return int|null
     */
    public function addGoodsAskBase(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addGoodsAskBaseData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 获取商品咨询基础信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoGoodsAskBase(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 更新商品咨询基础表
     * @param array $data
     * @param array $where
     * @return bool|null
     */
    public function updateGoodsAskBase(array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::updateGoodsAskBaseData($data), $where);
        if($update) {
            return true;
        }
         return null;
    }
}

?>