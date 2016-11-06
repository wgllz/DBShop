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
use Cms\Model\Article as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class ArticleTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_article';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加文章操作
     * @param array $data
     * @return int|null
     */
    public function addArticle (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addArticleData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 文章分页列表
     * @param array $pageArray
     * @param $language
     * @param array $where
     * @return Paginator
     */
    public function articlePageList (array $pageArray, $language, array $where = array())
    {
    	$select= new Select(array('dbshop_article'=>$this->table));
        $where = dbshopCheckInData::whereArticleData($where);
        
        $select->columns(array('*', new Expression('(SELECT ce.article_class_name FROM dbshop_article_class_extend as ce WHERE ce.article_class_id=dbshop_article.article_class_id and ce.language=\''. $language .'\') as article_class_name')));
        
        $select->join(array('e'=>'dbshop_article_extend'), 'e.article_id=dbshop_article.article_id', array('article_title'));
        $select->where($where);
        $select->order(array('dbshop_article.article_sort ASC', 'dbshop_article.article_id DESC'));
        //实例化分页处理
        $pageAdapter = new DbSelect($select, $this->adapter);
        $paginator   = new Paginator($pageAdapter);
        $paginator->setCurrentPageNumber($pageArray['page']);
        $paginator->setItemCountPerPage($pageArray['page_num']);
        
        return $paginator;
    }
    /**
     * 单个文章信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoArticle (array $where, $limitNum=null, $orderStr=null)
    {
        $row = $this->select(function (Select $select) use ($where, $limitNum, $orderStr) {
           $select->join(array('e'=>'dbshop_article_extend'), 'e.article_id=dbshop_article.article_id')
           ->where($where);
           if(!empty($orderStr)) $select->order($orderStr);
           if(!empty($limitNum)) $select->limit($limitNum);
        });
        if($row) {
            return $row->current();
        }
        return null;
    }
    /**
     * 更新文章
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function updateArticle (array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::updateArticleData($data), $where);
        return true;
    }
    /**
     * 批量更新文章，目前只是排序
     * @param array $data
     * @param array $where
     * @return int
     */
    public function allUpdateArticle(array $data, array $where)
    {
        return $this->update($data, $where);
    }
    /**
     * 删除文章
     * @param array $where
     * @return bool|null
     */
    public function delArticle (array $where)
    {
        $del = $this->delete($where);
        if($del) {
            return true;
        }
        return null;
    }
}

?>