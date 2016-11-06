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
use Goods\Model\GoodsPriceExtend as dbshopCheckInData;

class GoodsPriceExtendTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_price_extend';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加商品销售扩展
     * @param array $array
     * @return int|null
     */
    public function addPriceExtend(array $array)
    {
        $row = $this->insert(dbshopCheckInData::addGoodsPriceExtendData($array));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 编辑商品销售扩展信息
     * @param array $array
     * @param array $where
     * @return int|null
     */
    public function editPriceExtend(array $array,array $where)
    {
        $extendInfo = $this->infoPriceExtend($where);
        if($extendInfo) {
            $this->update(dbshopCheckInData::updateGoodsPriceExtendData($array),$where);
            return $extendInfo->extend_id;
        } else {
            return $this->addPriceExtend($array);
        }
    }
    /**
     * 获取单个销售属性信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoPriceExtend(array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->current();
        }
        return null;
    }
}

?>