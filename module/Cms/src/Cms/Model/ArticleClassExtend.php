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

class ArticleClassExtend
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['article_class_id']          = (isset($data['article_class_id'])          and !empty($data['article_class_id']))          ? intval($data['article_class_id'])        : null;
        self::$dataArray['article_class_name']        = (isset($data['article_class_name'])        and !empty($data['article_class_name']))        ? trim($data['article_class_name'])        : null;
        self::$dataArray['language']                  = (isset($data['language'])                  and !empty($data['language']))                  ? trim($data['language'])                  : null;

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['article_class_info']        = (isset($data['article_class_info'])        and !empty($data['article_class_info']))        ? trim($data['article_class_info'])        : '';
        self::$dataArray['article_class_extend_name'] = (isset($data['article_class_extend_name']) and !empty($data['article_class_extend_name'])) ? trim($data['article_class_extend_name']) : '';
        self::$dataArray['article_class_keywords']    = (isset($data['article_class_keywords'])    and !empty($data['article_class_keywords']))    ? trim($data['article_class_keywords'])    : '';
        self::$dataArray['article_class_description'] = (isset($data['article_class_description']) and !empty($data['article_class_description'])) ? trim($data['article_class_description']) : '';
        
        return self::$dataArray;
    }
    /**
     * 添加文章分类扩展过滤
     * @param array $data
     * @return array
     */
    public static function addArticleClassExtendData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 更新文章分类扩展过滤
     * @param array $data
     * @return array
     */
    public static function updateArticleClassExtendData (array $data)
    {
        unset($data['article_class_id']);
        
        return self::checkData($data);
    }
}

?>