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

namespace Shopfront\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ArticleController extends AbstractActionController
{
    private $dbTables = array();
    private $translator;
    
    /**
     * 文章列表
     */
    public function indexAction ()
    {
        $article_class_id = $this->params('cms_class_id', 0);
        if($article_class_id == 0) return $this->redirect()->toRoute('shopfront/default');
        
        $array = array();
        $array['article_class_info'] = $this->getDbshopTable('ArticleClassTable')->infoArticleClass(array('dbshop_article_class.article_class_id'=>$article_class_id, 'e.language'=>$this->getDbshopLang()->getLocale()));
        if($array['article_class_info']->article_class_state == 0) return $this->redirect()->toRoute('shopfront/default');
        
        //文章分类信息输出到layout
        $this->layout()->title_name         = $array['article_class_info']->article_class_name;
        $this->layout()->extend_title_name  = $array['article_class_info']->article_class_extend_name;
        $this->layout()->extend_keywords    = $array['article_class_info']->article_class_keywords;
        $this->layout()->extend_description = $array['article_class_info']->article_class_description;
        
        //同级分类
        $array['c_class']            = $this->getDbshopTable('ArticleClassTable')->listArticleClass($this->getDbshopLang()->getLocale(),array('dbshop_article_class.article_class_top_id'=>$array['article_class_info']->article_class_top_id, 'dbshop_article_class.article_class_state'=>1));
        //顶级分类
        $array['t_class']            = $this->getDbshopTable('ArticleClassTable')->listArticleClass($this->getDbshopLang()->getLocale(),array('dbshop_article_class.article_class_top_id'=>0, 'dbshop_article_class.article_class_state'=>1));
        //下级分类
        $array['sub_class']          = $this->getDbshopTable('ArticleClassTable')->listArticleClass($this->getDbshopLang()->getLocale(),array('dbshop_article_class.article_class_top_id'=>$article_class_id, 'dbshop_article_class.article_class_state'=>1));

        //文章列表
        $sunClassId = $this->getDbshopTable('ArticleClassTable')->getSunClassId($article_class_id,$this->getDbshopLang()->getLocale());
        $page 		= $this->params('page',1);
        $array['article_list'] = $this->getDbshopTable('ArticleTable')->articlePageList(array('page'=>$page, 'page_num'=>16), $this->getDbshopLang()->getLocale(), array('article_class_in_id'=>implode(',', $sunClassId), 'article_state'=>1));

        //面包屑菜单
        $array['class_menu'] = $this->getDbshopTable('ArticleClassTable')->selectArticleClass('dbshop_article_class.article_class_id IN ('.$array['article_class_info']->article_class_path.')', array('dbshop_article_class.article_class_path ASC'));
        
        return $array;
    }
    /** 
     * 文章内容
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:NULL
     */
    public function contentAction ()
    {
        $array = array();
        $article_id = $this->params('cms_id', 0);
        $array['article_body'] = $this->getDbshopTable('ArticleTable')->infoArticle(array('dbshop_article.article_id'=>$article_id, 'e.language'=>$this->getDbshopLang()->getLocale()));
        if(!$array['article_body']) return $this->redirect()->toRoute('shopfront/default');
        
        //查看是否有跳转链接
        if(isset($array['article_body']->article_url) and !empty($array['article_body']->article_url)) {
            header("Location: " . $array['article_body']->article_url);
            exit();
        }
        
        //文章信息输出到layout
        $this->layout()->title_name         = $array['article_body']->article_title;
        $this->layout()->extend_title_name  = $array['article_body']->article_title_extend;
        $this->layout()->extend_keywords    = $array['article_body']->article_keywords;
        $this->layout()->extend_description = $array['article_body']->article_description;

        $array['article_class_info'] = $this->getDbshopTable('ArticleClassTable')->infoArticleClass(array('dbshop_article_class.article_class_id'=>$array['article_body']->article_class_id, 'e.language'=>$this->getDbshopLang()->getLocale()));
        //同级分类
        $array['c_class']            = $this->getDbshopTable('ArticleClassTable')->listArticleClass($this->getDbshopLang()->getLocale(),array('dbshop_article_class.article_class_top_id'=>$array['article_class_info']->article_class_top_id, 'dbshop_article_class.article_class_state'=>1, 'dbshop_article_class.article_class_top_id!=0'));
        //顶级分类
        $array['t_class']            = $this->getDbshopTable('ArticleClassTable')->listArticleClass($this->getDbshopLang()->getLocale(),array('dbshop_article_class.article_class_top_id'=>0, 'dbshop_article_class.article_class_state'=>1));
        
        //面包屑菜单
        $array['article_class_info'] = $this->getDbshopTable('ArticleClassTable')->infoArticleClass(array('dbshop_article_class.article_class_id'=>$array['article_body']->article_class_id, 'e.language'=>$this->getDbshopLang()->getLocale()));
        $array['class_menu'] = $this->getDbshopTable('ArticleClassTable')->selectArticleClass('dbshop_article_class.article_class_id IN ('.$array['article_class_info']->article_class_path.')', array('dbshop_article_class.article_class_path ASC'));

        //上一篇
        $array['pre_article'] = $this->getDbshopTable('ArticleTable')->infoArticle(array('dbshop_article.article_class_id='.$array['article_body']->article_class_id, 'dbshop_article.article_id < '.$article_id, 'e.language="'.$this->getDbshopLang()->getLocale().'"'), 1);
        //下一篇
        $array['next_article']= $this->getDbshopTable('ArticleTable')->infoArticle(array('dbshop_article.article_class_id='.$array['article_body']->article_class_id, 'dbshop_article.article_id > '.$article_id, 'e.language="'.$this->getDbshopLang()->getLocale().'"'), 1);;

        return $array;
    }
    /** 
     * 前台单页内容
     * @return multitype:NULL
     */
    public function singleAction()
    {
        $array = array();
        $singleArticleId = (int) $this->params('cms_id', 0);
        $array['single_article_info'] = $this->getDbshopTable('SingleArticleTable')->infoSingleArticle(array('dbshop_single_article.single_article_id'=>$singleArticleId, 'e.language'=>$this->getDbshopLang()->getLocale()));


        //单页文章信息输出到layout
        $this->layout()->title_name         = $array['single_article_info']->single_article_title;
        $this->layout()->extend_title_name  = $array['single_article_info']->single_article_title_extend;
        $this->layout()->extend_keywords    = $array['single_article_info']->single_article_keywords;
        $this->layout()->extend_description = $array['single_article_info']->single_article_description;
        
        return $array;
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName)
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
    /**
     * 语言包调用
     * @return Ambigous <object, multitype:, \Zend\I18n\Translator\Translator>
     */
    private function getDbshopLang ()
    {
        if (! $this->translator) {
            $this->translator = $this->getServiceLocator()->get('translator');
        }
        return $this->translator;
    }
}
