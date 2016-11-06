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
use Zend\Db\Adapter\Adapter;
use Ad\Model\Ad as dbshopCheckInData;

class AbTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_ad';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加广告内容
     * @param array $data
     * @return int|null
     */
    public function addAd (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addAdData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 更新广告内容
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function updateAd (array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::updateAdDate($data), $where);
        return true;
    }
    /**
     * 获取广告信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoAd (array $where)
    {
        $result = $this->select($where)->current();
        if($result->ad_id) {
            return $result;
        }
        return null;
    }
    /**
     * 广告列表
     * @param array $where
     * @return array|null
     */
    public function listAd (array $where=array())
    {
        $result = $this->select($where);
        if($result->count()>0) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 删除广告信息
     * @param array $where
     */
    public function delAd (array $where)
    {
        $this->delete($where);
    }
    /**
     * 统计一个广告类别中已经加入了多少广告
     * @param array $where
     * @return int
     */
    public function classAdCount(array $where)
    {
        $count = $this->select($where)->count();
        return $count;
    }
}