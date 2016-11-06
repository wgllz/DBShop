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
use Goods\Model\GoodsAttributeExtend as dbshopCheckInData;

class GoodsAttributeExtendTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_attribute_extend';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 属性多语言扩展信息
     * @param array $data
     * @return bool|null
     */
    public function addAttributeExtend (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addAttributeExtendData($data));
        if($row) {
            return true;
        }
        return null;
    }
    /**
     * 属性多语言信息编辑
     * @param array $data
     * @param array $where
     * @return bool|int|null
     */
    public function updateAttributeExtend (array $data, array $where)
    {
        $row = $this->select($where)->current();
        if($row->attribute_id) {
            return $this->update(dbshopCheckInData::updateAttributeExtendData($data), $where);
        } else {
            return $this->addAttributeExtend($data);
        }
    }
    /**
     * 属性扩展删除
     * @param array $where
     * @return bool|null
     */
    public function delAttributeExtend (array $where)
    {
        if($this->delete($where)) {
            return true;
        }
        return null;
    }
}

?>