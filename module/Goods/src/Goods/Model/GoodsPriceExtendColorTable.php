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
use Goods\Model\GoodsPriceExtendColor as dbshopCheckInData;

class GoodsPriceExtendColorTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_price_extend_color';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加扩展销售颜色
     * @param array $array
     * @return bool|null
     */
    public function addGoodsPriceExtendColor(array $array)
    {
        $row = $this->insert(dbshopCheckInData::addPriceExtendColorData($array));
        if($row) {
            return true;
        }
        return null;
    }
    /**
     * 获取扩展销售颜色
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoPriceExtendColor (array $where)
    {
        $row = $this->select($where);
        if($row) {
            return $row->current();
        }
        return null;
    }
    /**
     * 获得扩展销售颜色
     * @param array $where
     * @return array|null
     */
    public function listPriceExtendColor(array $where=array())
    {
        $result = $this->select($where);
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 删除扩展颜色
     * @param array $where
     * @return bool|null
     */
    public function delGoodsPriceExtendColor(array $where)
    {
        $delState = $this->delete($where);
        if($delState) {
            return true;
        }
        return null;
    }
    /**
     * 获取扩展颜色的字符串，用于商品索引
     * @param array $where
     * @return null|string
     */
    public function goodsExtendColorStr(array $where)
    {
        $result = $this->select($where);
        if($result) {
            $colorStr   = '';
            $colorArray = $result->toArray();
            foreach($colorArray as $colorValue) {
                $colorStr .= $colorValue['color_info'];
            }
            return $colorStr;
        }
        return '';
    }
}

?>