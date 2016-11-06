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

namespace Cms\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Cms\Model\SingleArticleExtend as dbshopCheckInData;

class SingleArticleExtendTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_single_article_extend';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加单页文章扩展
     * @param array $data
     * @return bool|null
     */
    public function addSingleArticleExtend(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addSingleArticleExtendData($data));
        if($row) {
            return true;
        }
        return null;
    }
    /**
     * 编辑单页文章扩展
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateSingleArticleExtend(array $data, array $where)
    {
        return $this->update(dbshopCheckInData::updateSingleArticleExtendData($data), $where);
    }
    /**
     * 删除单页文章扩张
     * @param array $where
     */
    public function delSingleArticleExtend(array $where)
    {
        //内容中的图片删除
        $extendArray = $this->select($where)->toArray();
        if(is_array($extendArray) and !empty($extendArray)) {
            foreach ($extendArray as $extendValue) {
                $imageArray = array();
                preg_match_all("/<img.*?src=[\'|\"](.*?(?:[.gif|.jpg]))[\'|\"].*?[\/]?>/", $extendValue['single_article_body'], $imageArray);
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