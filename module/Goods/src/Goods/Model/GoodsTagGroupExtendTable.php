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
use Goods\Model\GoodsTagGroupExtend as dbshopCheckInData;

class GoodsTagGroupExtendTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_tag_group_extend';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加标签组扩展信息
     * @param array $data
     * @return int|null
     */
    public function addTagGroupExtend (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addTagGroupExtendData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 编辑更新标签组扩展信息
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function editTagGroupExtend (array $data, array $where)
    {
        $tagGroupInfo = $this->select($where);
        if($tagGroupInfo) $this->update(dbshopCheckInData::editTagGroupExtendData($data), $where);
        else $this->addTagGroupExtend($data);
        return true;
    }
    /**
     * 删除标签组扩展信息
     * @param array $where
     * @return bool|null
     */
    public function delTagGroupExtend (array $where)
    {
        $del = $this->delete($where);
        if($del) return true;
        return null;
    }
}

?>