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

namespace Mobile\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ArticleController extends AbstractActionController
{
    private $dbTables = array();
    private $translator;

    /**
     * 文章内容
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:NULL
     */
    public function contentAction ()
    {
        $array = array();
        $article_id = (int) $this->params('cms_id', 0);

        $array['article_body'] = $this->getDbshopTable('ArticleTable')->infoArticle(array('dbshop_article.article_id'=>$article_id, 'e.language'=>$this->getDbshopLang()->getLocale()));
        if(!$array['article_body']) return $this->redirect()->toRoute('mobile/default');

        //查看是否有跳转链接
        if(isset($array['article_body']->article_url) and !empty($array['article_body']->article_url)) {
            header("Location: " . $array['article_body']->article_url);
            exit();
        }

        //判断是否为pc端访问
        if(!$this->getServiceLocator()->get('frontHelper')->isMobile()) return $this->redirect()->toRoute('frontarticle/default/cms_id', array('action'=>'content', 'cms_id'=>$article_id));

        //文章信息输出到layout
        $this->layout()->title_name         = $array['article_body']->article_title;
        $this->layout()->extend_title_name  = $array['article_body']->article_title_extend;
        $this->layout()->extend_keywords    = $array['article_body']->article_keywords;
        $this->layout()->extend_description = $array['article_body']->article_description;

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

        //判断是否为pc端访问
        if(!$this->getServiceLocator()->get('frontHelper')->isMobile()) return $this->redirect()->toRoute('frontarticle/default/cms_id', array('action'=>'single', 'cms_id'=>$singleArticleId));

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