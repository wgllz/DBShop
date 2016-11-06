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
use Goods\Model\GoodsClassShow as dbshopCheckInData;

class GoodsClassShowTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_class_show';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加标签组到分类
     * @param array $data
     * @return bool|null
     */
    public function addGoodsClassTagGroup (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addGoodsClassTagGroupData($data));
        if($row) {
            return true;
        }
        return null;
    }
    /**
     * 编辑标签组到分类
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function eidtGoodsClassTagGroup(array $data, array $where)
    {
        $classTagGroup = $this->select($where)->current();
        if(!empty($classTagGroup->class_id)) {
            $this->update(dbshopCheckInData::editGoodsClassTagGroupData($data), $where);
        } else {
            $this->addGoodsClassTagGroup($data);
        }
        return true;
    }
    /**
     * 获取分类标签组的数组
     * @param array $where
     * @return mixed|null
     */
    public function arrayGoodsClassTagGroup (array $where)
    {
        $classTagGroup = $this->select($where)->current();
        if(isset($classTagGroup->show_body) and !empty($classTagGroup->show_body)) {
            return unserialize($classTagGroup->show_body);
        }
        return null;
    }
    /**
     * 删除分类中的标签组
     * @param array $where
     * @return int
     */
    public function delGoodsClassTagGroup (array $where)
    {
        return $this->delete($where);
    }
}

?>