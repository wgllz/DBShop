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
use Goods\Model\GoodsAttributeGroupExtend as dbshopCheckInData;

class GoodsAttributeGroupExtendTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_attribute_group_extend';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加属性组扩展信息
     * @param array $data
     * @return bool|null
     */
    public function addAttributeGroupExtend (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addAttributeGroupExtendData($data));
        if($row) {
            return true;
        }
        return null;
    }
    /**
     * 编辑属性组扩展信息
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function editAttributeGroupExtend (array $data, array $where)
    {
        $info = $this->select($where)->current();
        if($info->attribute_group_id) {
            $this->update(dbshopCheckInData::editAttributeGroupExtendData($data), $where);
        } else {
            $this->addAttributeGroupExtend($data);
        }
        return true;
    }
    /**
     * 删除属性组扩展
     * @param array $where
     * @return bool|null
     */
    public function delAttributeGroupExtend (array $where)
    {
        $del = $this->delete($where);
        if($del) {
            return true;
        }
        return null;
    }
}

?>