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
use Goods\Model\GoodsInAttribute as dbshopCheckInData;
use Zend\Db\Sql\Select;

class GoodsInAttributeTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_in_attribute';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加商品属性值
     * @param array $data
     * @return bool|null
     */
    public function addGoodsInAttribute (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addGoodsInAttributeData($data));
        if($row) {
            return true;
        }
        return null;
    }
    /**
     * 商品属性值列表
     * @param array $where
     * @return array
     */
    public function listGoodsInAttribute (array $where)
    {
        $result = $this->select($where);
        return $result->toArray();
    }
    /**
     * 删除
     * @param array $where
     * @return bool|null
     */
    public function delGoodsInAttribute (array $where)
    {
        $del = $this->delete($where);
        if($del) {
            return true;
        }
        return null;
    }
    /**
     * 获取属性信息，用于商品索引
     * @param array $where
     * @return array
     */
    public function goodsInAttributeStr(array $where)
    {
        $result = $this->select(function (Select $select)use($where){
            $select->join(array('a'=>'dbshop_goods_attribute'), 'a.attribute_id=dbshop_goods_in_attribute.attribute_id');
            $select->where($where);
        });
        if($result) {
            $attributeStr = '';
            $attributeValueIdStr = '';
            $attributeArray = $result->toArray();
            foreach($attributeArray as $aValue) {
                if($aValue['attribute_type'] == 'input' or $aValue['attribute_type'] == 'textarea') $attributeStr .= $aValue['attribute_body'];
                else $attributeValueIdStr .= $aValue['attribute_body'].',';
            }
            return array('attributestr'=>$attributeStr, 'attributevalueid'=>rtrim($attributeValueIdStr, ','));
        }
        return array('attributestr'=>'', 'attributevalueid'=>'');
    }
}

?>