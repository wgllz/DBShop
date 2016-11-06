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
use Goods\Model\GoodsBrandExtend as dbshopCheckInData;

class GoodsBrandExtendTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_brand_extend';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加商品品牌扩展信息
     * @param array $array
     * @return int|null
     */
    public function addGoodsBrandExtend(array $array)
    {
        $row = $this->insert(dbshopCheckInData::addGoodsBrandExtendData($array));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 商品品牌扩展信息更新
     * @param array $array
     * @param array $where
     * @return bool|null
     */
    public function updateBrandextend (array $array,array $where=array())
    {
        $update = $this->update(dbshopCheckInData::updateGoodsBrandExtendData($array),$where);
        if($update) {
            return true;
        }
        return null;
    }
    /**
     * 删除商品品牌扩展信息
     * @param array $where
     * @return null
     */
    public function delBrandextend (array $where)
    {
        $info = $this->select($where)->current();
        if($info) {
            @unlink(DBSHOP_PATH . $info->brand_logo);
            $this->delete($where);
        }
        return null;
    }
}

?>