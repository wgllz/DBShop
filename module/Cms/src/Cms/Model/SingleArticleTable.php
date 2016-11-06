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
use Cms\Model\SingleArticle as dbshopCheckInData;
use Zend\Db\Sql\Select;

class SingleArticleTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_single_article';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加单页文章
     * @param array $data
     * @return int|null
     */
    public function addSingleArticle(array $data)
    {
        $row = $this->insert(dbshopCheckInData::addSingleArticleData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 单页文章列表
     * @param array $where
     * @return array|null
     */
    public function listSingleArticle(array $where=array())
    {
        $result = $this->select(function (Select $select) use ($where) {
            $select->join(array('e'=>'dbshop_single_article_extend'), 'e.single_article_id=dbshop_single_article.single_article_id');
            $select->where($where);
            $select->where('dbshop_single_article.template_tag="'.DBSHOP_TEMPLATE.'"');
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 单页文章内容
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoSingleArticle(array $where)
    {
        $result = $this->select(function (Select $select) use ($where) {
            $select->join(array('e'=>'dbshop_single_article_extend'), 'e.single_article_id=dbshop_single_article.single_article_id');
            $select->where($where);
        });
        if($result) {
            return $result->current();
        }
        return null;
    }
    /**
     * 更新单页文章内容
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateSingleArticle(array $data, array $where)
    {
        return $this->update(dbshopCheckInData::updateSingleArticleData($data), $where);
    }
    /**
     * 删除单页文章
     * @param array $where
     * @return int
     */
    public function delSingleArticle(array $where)
    {
        return $this->delete($where);
    }
}