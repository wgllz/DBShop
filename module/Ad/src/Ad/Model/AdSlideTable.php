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

namespace Ad\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Ad\Model\AdSlide as dbshopCheckInData;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;

class AdSlideTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_ad_slide';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加幻灯片信息
     * @param array $data
     * @return bool|null
     */
    public function addAdSlide(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addAdSlideData($data));
        if($row) {
            return true;
        }
        return null;
    }
    /**
     * 获取幻灯片组
     * @param array $where
     * @return array|null|\Zend\Db\ResultSet\ResultSet
     */
    public function listAdSlide (array $where)
    {
        $result = $this->select(function (Select $select) use ($where){
            $select->where($where)->order('ad_slide_sort ASC');
        });
        $result = $result->toArray();
        if(!empty($result)) {
            return $result;    
        }
        return null;
    }
    /**
     * 获取单个幻灯片图片的信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoAdSlide (array $where)
    {
        $row = $this->select($where);
        if($row) {
            return $row->current();
        }
        return null;
    }
    /**
     * 只删除幻灯片的数据，不对其中的图片进行删除，在编辑幻灯片时使用。先全部删除，在从新插入
     * @param array $where
     */
    public function delSlideData (array $where)
    {
        $this->delete($where);
    }
    /**
     * 幻灯片删除
     * @param array $where
     */
    public function delAdSlide (array $where)
    {
        $adArray = $this->select($where)->toArray();
        if(is_array($adArray) and !empty($adArray)) {
            foreach ($adArray as $adValue) {
                if($adValue['ad_slide_image'] != '') @unlink(DBSHOP_PATH . $adValue['ad_slide_image']);
            }
        }
        $this->delete($where);
        @rmdir(DBSHOP_PATH . '/public/upload/ad/' . $where['ad_id']);
    }
}