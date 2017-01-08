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
use Goods\Model\GoodsPriceExtendSize as dbshopCheckInData;

class GoodsPriceExtendSizeTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_price_extend_size';

    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加销售属性，尺寸
     * @param array $array
     * @return bool|null
     */
    public function addPriceExtendSize (array $array)
    {
        $row = $this->insert(dbshopCheckInData::addGoodsPriceExtendSizeData($array));
        if($row) {
            return true;
        }
        return null;
    }
    /**
     * 获取销售属性，尺寸
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoPriceExtendSize (array $where)
    {
        $row = $this->select($where);
        if($row) {
            return $row->current();
        }
        return null;
    }
    /**
     * 列出销售属性，尺寸
     * @param array $where
     * @return array|null
     */
    public function listPriceExtendSize (array $where)
    {
        $result = $this->select($where);
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 删除销售属性，尺寸
     * @param array $where
     * @return bool|null
     */
    public function delPriceExtendSize (array $where)
    {
        $delState = $this->delete($where);
        if($delState) {
            return true;
        }
        return null;        
    }
    /**
     * 获取商品尺寸字符串，用于商品索引
     * @param array $where
     * @return null|string
     */
    public function goodsExtendSizeStr(array $where)
    {
        $result = $this->select($where);
        if($result) {
            $sizeStr    = '';
            $sizeArray  = $result->toArray();
            foreach($sizeArray as $sizeValue) {
                $sizeStr .= $sizeValue['size_info'];
            }
            return $sizeStr;
        }
        return '';
    }
}

?>