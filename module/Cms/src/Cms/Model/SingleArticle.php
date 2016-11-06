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

class SingleArticle
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['single_article_id']       = (isset($data['single_article_id'])       and !empty($data['single_article_id']))       ? intval($data['single_article_id'])          : null;
        self::$dataArray['template_tag']            = (isset($data['template_tag'])            and !empty($data['template_tag']))            ? trim($data['template_tag'])                 : null;
        self::$dataArray['single_article_add_time'] = (isset($data['single_article_add_time']) and !empty($data['single_article_add_time'])) ? strtotime($data['single_article_add_time']) : time();

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['single_article_writer']   = (isset($data['single_article_writer'])   and !empty($data['single_article_writer']))   ? trim($data['single_article_writer'])     : '';
        self::$dataArray['article_tag']             = (isset($data['article_tag'])             and !empty($data['article_tag']))             ? trim($data['article_tag'])               : '';
    
        return self::$dataArray;
    }
    /**
     * 添加单页文章过滤
     * @param array $data
     * @return array
     */
    public static function addSingleArticleData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 更新单页文章过滤
     * @param array $data
     * @return array
     */
    public static function updateSingleArticleData (array $data)
    {
        unset($data['single_article_id']);
        return self::checkData($data);
    }
}