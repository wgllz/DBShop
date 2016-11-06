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

class SingleArticleExtend
{
    private static $dataArray = array();
    
    private static function checkData ($data)
    {
        self::$dataArray['single_article_id']           = (isset($data['single_article_id'])           and !empty($data['single_article_id']))           ? intval($data['single_article_id'])         : null;
        self::$dataArray['single_article_title']        = (isset($data['single_article_title'])        and !empty($data['single_article_title']))        ? trim($data['single_article_title'])        : null;
        self::$dataArray['language']                    = (isset($data['language'])                    and !empty($data['language']))             ? trim($data['language'])             : null;

        self::$dataArray = array_filter(self::$dataArray);

        self::$dataArray['single_article_body']         = (isset($data['single_article_body'])         and !empty($data['single_article_body']))         ? trim($data['single_article_body'])         : '';
        self::$dataArray['single_article_title_extend'] = (isset($data['single_article_title_extend']) and !empty($data['single_article_title_extend'])) ? trim($data['single_article_title_extend']) : '';
        self::$dataArray['single_article_keywords']     = (isset($data['single_article_keywords'])     and !empty($data['single_article_keywords']))     ? trim($data['single_article_keywords'])     : '';
        self::$dataArray['single_article_description']  = (isset($data['single_article_description'])  and !empty($data['single_article_description']))  ? trim($data['single_article_description'])  : '';
    
        return self::$dataArray;
    }
    /**
     * 添加单页扩展过滤
     * @param array $data
     * @return array
     */
    public static function addSingleArticleExtendData (array $data)
    {
        return self::checkData($data);
    }
    /**
     * 更新单页扩展过滤
     * @param array $data
     * @return array
     */
    public static function updateSingleArticleExtendData(array $data)
    {
        unset($data['single_article_id']);
        return self::checkData($data);
    }
}

?>