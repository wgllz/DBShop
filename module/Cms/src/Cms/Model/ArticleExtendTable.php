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
use Cms\Model\ArticleExtend as dbshopCheckInData;

class ArticleExtendTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_article_extend';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加文章扩展
     * @param array $data
     * @return bool|null
     */
    public function addArticleExtend (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addArticleExtendData($data));
        if($row) {
            return true;
        }
        return null;
    }
    /**
     * 编辑文章扩展
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function updateArticleExtend (array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::updateArticleExtendData($data), $where);
        return true;
    }
    /**
     * 删除文章扩展内容
     * @param array $where
     */
    public function delArticleExtend (array $where)
    {
        //内容中的图片删除
        $extendArray = $this->select($where)->toArray();
        if(is_array($extendArray) and !empty($extendArray)) {
            foreach ($extendArray as $extendValue) {
                $imageArray = array();
                preg_match_all("/<img.*?src=[\'|\"](.*?(?:[.gif|.jpg]))[\'|\"].*?[\/]?>/", $extendValue['article_body'], $imageArray);
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