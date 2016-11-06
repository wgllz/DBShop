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

class ArticleClass
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['article_class_id']     = (isset($data['article_class_id'])     and !empty($data['article_class_id']))     ? intval($data['article_class_id'])     : null;
        self::$dataArray['article_class_path']   = (isset($data['article_class_path'])   and !empty($data['article_class_path']))   ? trim($data['article_class_path'])     : null;
        self::$dataArray['article_class_sort']   = (isset($data['article_class_sort'])   and !empty($data['article_class_sort']))   ? intval($data['article_class_sort'])   : null;
        self::$dataArray['index_news']           = (isset($data['index_news'])           and !empty($data['index_news']))           ? intval($data['index_news'])           : null;

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['article_class_state']  = (isset($data['article_class_state'])  and !empty($data['article_class_state']))  ? intval($data['article_class_state'])  : 0;
        self::$dataArray['article_class_top_id'] = (isset($data['article_class_top_id']) and !empty($data['article_class_top_id'])) ? intval($data['article_class_top_id']) : 0;
        
        return self::$dataArray;
    }
    /**
     * 添加文章分类过滤
     * @param array $data
     * @return array
     */
    public static function addArticleClassData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 编辑文章分类过滤
     * @param array $data
     * @return array
     */
    public static function updateArticleClassData (array $data)
    {
        unset($data['article_class_id']);
        
        return self::checkData($data);
    }
}

?>