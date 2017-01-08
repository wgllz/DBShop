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
use Goods\Model\GoodsAttributeValueExtend as dbshopCheckInData;

class GoodsAttributeValueExtendTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_attribute_value_extend';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加属性值扩展信息
     * @param array $data
     * @return bool|null
     */
    public function addAttributeValueExtend (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addAttributeValueExtendData($data));
        if($row) {
            return true;
        }
        return null;
    }
    /**
     * 编辑属性值扩展信息
     * @param array $data
     * @param array $where
     * @return bool|int|null
     */
    public function updateAttributeValueExtend (array $data, array $where)
    {
        $row = $this->select($where)->current();
        if($row->value_id) {
            return $this->update(dbshopCheckInData::updateAttributeValueExtendData($data), $where);
        } else {
            return $this->addAttributeValueExtend($data);
        }
    }
    /**
     * 删除属性值扩展
     * @param array $where
     * @return bool|null
     */
    public function delAttributeValueExtend (array $where)
    {
        if($this->delete($where)) {
            return true;
        }
        return null;
    }
    /**
     * 获取属性值信息的字符串
     * @param array $where
     * @return string
     */
    public function attributeValueStr(array $where)
    {
        $result = $this->select($where)->toArray();
        if($result) {
            $valueStr = '';
            foreach($result as $value) {
                $valueStr .= $value['value_name'];
            }
            return $valueStr;
        }
        return '';
    }
}

?>