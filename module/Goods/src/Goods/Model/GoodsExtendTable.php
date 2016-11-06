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
use Goods\Model\GoodsExtend as dbshopCheckInData;

class GoodsExtendTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_extend';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 商品扩展信息添加
     * @param array $array
     * @return bool|null
     */
    public function addGoodsExtend(array $array)
    {
        $row = $this->insert(dbshopCheckInData::addGoodsExtendData($array));
        if($row) {
            return true;
        } else {
            return null;
        }
    }
    /**
     * 商品扩展信息更新
     * @param array $array
     * @param array $where
     * @return bool|null
     */
    public function updateGoodsExtend(array $array,array $where=array())
    {
        $update = $this->update(dbshopCheckInData::updateGoodsExtendData($array),$where);
        if($update) {
            return true;
        }
        return null;
    }
    /**
     * 获取商品扩展信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoGoodsExtend (array $where)
    {
        $info = $this->select($where);
        if($info) {
            return $info->current();
        }
        return null;
    }
    /**
     * 删除商品内容中的图片
     * @param array $goodsId
     */
    public function delGoodsExtend(array $goodsId)
    {
        $where = 'goods_id IN (' . implode(',', $goodsId) . ')';
        //内容中的图片删除
        $goodsArray = $this->select($where)->toArray();
        if(is_array($goodsArray) and !empty($goodsArray)) {
            foreach ($goodsArray as $goodsValue) {
                $imageArray = array();
                preg_match_all("/<img.*?src=[\'|\"](.*?(?:[.gif|.jpg|.png]))[\'|\"].*?[\/]?>/", $goodsValue['goods_body'], $imageArray);
                if(is_array($imageArray[1]) and !empty($imageArray[1])) {
                    foreach ($imageArray[1] as $imageFile) {
                        $image = array();
                        $image = explode('/../../../', $imageFile);
                        if(isset($image[1]) and !empty($image[1])) @unlink(DBSHOP_PATH . '/public/' . str_replace('//', '/', $image[1]));
                    }
                }
            }
        }
        $this->delete($where);
    }
}

?>