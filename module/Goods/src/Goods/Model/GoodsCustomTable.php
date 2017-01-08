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
use Goods\Model\GoodsCustom as dbshopCheckInData;
use Zend\Db\Sql\Select;

class GoodsCustomTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_custom';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加商品自定义信息
     * @param array $data
     * @return bool|null
     */
    public function addGoodsCustom (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addGoodsCustomData($data));
        if($row) {
            return true;
        }
        return null;
    }
    /**
     * 列出商品自定义信息
     * @param array $where
     * @return array|null
     */
    public function listGoodsCustom (array $where)
    {
        $result = $this->select(function (Select $select) use ($where) {
           $select->where($where)->order('custom_key ASC'); 
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 删除商品自定义信息
     * @param array $where
     * @return bool|null
     */
    public function delGoodsCustom (array $where)
    {
        $del = $this->delete($where);
        if($del) {
            return true;
        }
        return null;
    }
    /**
     * 获取商品扩展的字符串，用于商品索引
     * @param array $where
     * @return null|string
     */
    public function goodsCustomStr(array $where)
    {
        $result = $this->select($where);
        if($result) {
            $customStr   = '';
            $customArray = $result->toArray();
            foreach($customArray as $customValue) {
                $customStr .= $customValue['custom_content'];
            }
            return $customStr;
        }
        return '';
    }
}

?>