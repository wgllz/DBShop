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
use Cms\Model\ArticleClassExtend as dbshopCheckInData;

class ArticleClassExtendTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_article_class_extend';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加文章分类扩展
     * @param array $data
     * @return bool|null
     */
    public function addArticleClassExtend (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addArticleClassExtendData($data));
        if($row) {
            return true;
        }
        return null;
    }
    /**
     * 文章分类扩展编辑
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function editArticleClassExtend (array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::updateArticleClassExtendData($data), $where);
        return true;
    }
}

?>