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

class Article
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['article_id']       = (isset($data['article_id'])       and !empty($data['article_id']))       ? intval($data['article_id'])       : null;
        self::$dataArray['article_class_id'] = (isset($data['article_class_id']) and !empty($data['article_class_id'])) ? intval($data['article_class_id']) : null;
        self::$dataArray['article_add_time'] = (isset($data['article_add_time']) and !empty($data['article_add_time'])) ? strtotime($data['article_add_time']) : time();
        self::$dataArray['article_sort']     = (isset($data['article_sort'])     and !empty($data['article_sort']))     ? intval($data['article_sort'])     : 255;

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['article_state']    = (isset($data['article_state'])    and !empty($data['article_state']))    ? intval($data['article_state'])    : 0;
        self::$dataArray['article_writer']   = (isset($data['article_writer'])   and !empty($data['article_writer']))   ? trim($data['article_writer'])     : '';
        self::$dataArray['article_url']      = (isset($data['article_url'])      and !empty($data['article_url']))      ? trim($data['article_url'])        : '';

        return self::$dataArray;
    }
    /**
     * 添加文章过滤
     * @param array $data
     * @return array
     */
    public static function addArticleData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 编辑文章过滤
     * @param array $data
     * @return array
     */
    public static function updateArticleData (array $data)
    {
        unset($data['article_id']);
        return self::checkData($data);
    }
    /**
     * 文章查找过滤
     * @param array $data
     * @return array
     */
    public static function whereArticleData (array $data)
    {
        $searchArray = array();
        $searchArray[] = (isset($data['start_article_id'])      and !empty($data['start_article_id']))   ? 'dbshop_article.article_id >= ' . intval($data['start_article_id'])            : '';
        $searchArray[] = (isset($data['end_article_id'])        and !empty($data['end_article_id']))     ? 'dbshop_article.article_id <= ' . intval($data['end_article_id'])              : '';
        $searchArray[] = (isset($data['article_title'])         and !empty($data['article_title']))      ? 'e.article_title like \'%' . trim($data['article_title']) . '%\''              : '';
        $searchArray[] = (isset($data['article_class_id'])      and $data['article_class_id'] != '')     ? 'dbshop_article.article_class_id=' . intval($data['article_class_id'])         : '';
        $searchArray[] = (isset($data['article_state'])         and $data['article_state'] != '')        ? 'dbshop_article.article_state =' . intval($data['article_state'])              : '';
        $searchArray[] = (isset($data['search_start_time'])     and !empty($data['search_start_time']))  ? 'dbshop_article.article_add_time >= ' . strtotime($data['search_start_time'])  : '';
        $searchArray[] = (isset($data['search_end_time'])       and !empty($data['search_end_time']))    ? 'dbshop_article.article_add_time <= ' . strtotime($data['search_end_time'])    : '';
        $searchArray[] = (isset($data['article_class_in_id'])   and !empty($data['article_class_in_id']))? 'dbshop_article.article_class_id IN (' . $data['article_class_in_id'] . ')'    : '';
        
        return array_filter($searchArray);
    }
}

?>