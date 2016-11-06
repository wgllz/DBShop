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

class ArticleExtend
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['article_id']           = (isset($data['article_id'])           and !empty($data['article_id']))           ? intval($data['article_id'])         : null;
        self::$dataArray['article_title']        = (isset($data['article_title'])        and !empty($data['article_title']))        ? trim($data['article_title'])        : null;
        self::$dataArray['language']             = (isset($data['language'])             and !empty($data['language']))             ? trim($data['language'])             : null;

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['article_body']         = (isset($data['article_body'])         and !empty($data['article_body']))         ? trim($data['article_body'])         : '';
        self::$dataArray['article_title_extend'] = (isset($data['article_title_extend']) and !empty($data['article_title_extend'])) ? trim($data['article_title_extend']) : '';
        self::$dataArray['article_keywords']     = (isset($data['article_keywords'])     and !empty($data['article_keywords']))     ? trim($data['article_keywords'])     : '';
        self::$dataArray['article_description']  = (isset($data['article_description'])  and !empty($data['article_description']))  ? trim($data['article_description'])  : '';
        
        return self::$dataArray;
    }
    /**
     * 添加文章扩展过滤
     * @param array $data
     * @return array
     */
    public static function addArticleExtendData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 更新文章扩展过滤
     * @param array $data
     * @return array
     */
    public static function updateArticleExtendData(array $data)
    {
        unset($data['article_id']);
        
        return self::checkData($data);
    }
}

?>