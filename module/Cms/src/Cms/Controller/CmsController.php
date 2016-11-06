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

namespace Cms\Controller;

use Admin\Controller\BaseController;

class CmsController extends BaseController
{
    /** 
     * 文章列表
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $array = array();
        //分页
        $searchArray  = array();
        if($this->request->isGet()) {
            $searchArray          = $this->request->getQuery()->toArray();
            $array['searchArray'] = $searchArray;
            //获取当前分类及其下级分类
            if(isset($searchArray['article_class_id'])) {
                $searchArray['article_class_in_id'] = implode(',', $this->getDbshopTable('ArticleClassTable')->getSunClassId($searchArray['article_class_id'],$this->getDbshopLang()->getLocale()));
                unset($searchArray['article_class_id']);
            }
        }
        $page = $this->params('page',1);
        $array['page'] = $page;
        $array['query']= $array['searchArray'];

        $array['article_list'] = $this->getDbshopTable()->articlePageList(array('page'=>$page, 'page_num'=>20), $this->getDbshopLang()->getLocale(), $searchArray);

        //文章分类
        $array['article_class'] = $this->getDbshopTable('ArticleClassTable')->classOptions(0, $this->getDbshopTable('ArticleClassTable')->listArticleClass($this->getDbshopLang()->getLocale()));
        
        return $array;
    }
    /** 
     * 添加文章
     * @return multitype:NULL
     */
    public function addAction()
    {
        if($this->request->isPost()) {
            //接收POST数据
            $articleArray = $this->request->getPost()->toArray();
            $articleId    = $this->getDbshopTable()->addArticle($articleArray);
            if($articleId) {
                $articleArray['article_id'] = $articleId;
                $articleArray['language']   = $this->getDbshopLang()->getLocale();

                //如果没有填写关键词则使用自动分词功能
                if(empty($articleArray['article_keywords']) and !empty($articleArray['article_body'])) {
                    $articleArray['article_keywords'] = $this->getServiceLocator()->get('adminHelper')->dbshopPhpAnalysis($articleArray['article_body'], 200);
                }
                //如果没有填写描述内容，则使用上面的关键字
                if(empty($articleArray['article_description']) and !empty($articleArray['article_keywords'])) {
                    $articleArray['article_description'] = $articleArray['article_keywords'];
                }
                $this->getDbshopTable('ArticleExtendTable')->addArticleExtend($articleArray);
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理文章'), 'operlog_info'=>$this->getDbshopLang()->translate('添加文章') . '&nbsp;' . $articleArray['article_title']));
                
                unset($articleArray);
                
                //跳转处理
                return $this->redirect()->toRoute('cms/default',array('controller'=>'cms'));
            }
        }

        $array = array();
        $array['article_class'] = $this->getDbshopTable('ArticleClassTable')->classOptions(0, $this->getDbshopTable('ArticleClassTable')->listArticleClass($this->getDbshopLang()->getLocale()));
        
        //添加子集文章时，使用
        $array['article_class_id'] = (int) $this->params('article_class_id', 0);

        return $array;
    }
    /** 
     * 编辑文章
     */
    public function editAction ()
    {
        $articleId = (int) $this->params('article_id', 0);
        if($articleId == 0) {
            return $this->redirect()->toRoute('cms/default',array('controller'=>'cms'));
        }
        $array = array();
        //用于返回对应的分页数
        $page = $this->params('page',1);
        $array['page']     = $page;
        $array['query']= $this->request->getQuery()->toArray();

        if($this->request->isPost()) {
            $articleArray = $this->request->getPost()->toArray();
            $this->getDbshopTable()->updateArticle($articleArray, array('article_id'=>$articleId));

            //如果没有填写关键词则使用自动分词功能
            if(empty($articleArray['article_keywords']) and !empty($articleArray['article_body'])) {
                $articleArray['article_keywords'] = $this->getServiceLocator()->get('adminHelper')->dbshopPhpAnalysis($articleArray['article_body'], 200);
            }
            //如果没有填写描述内容，则使用上面的关键字
            if(empty($articleArray['article_description']) and !empty($articleArray['article_keywords'])) {
                $articleArray['article_description'] = $articleArray['article_keywords'];
            }
            $this->getDbshopTable('ArticleExtendTable')->updateArticleExtend($articleArray, array('article_id'=>$articleId, 'language'=>$this->getDbshopLang()->getLocale()));
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理文章'), 'operlog_info'=>$this->getDbshopLang()->translate('更新文章') . '&nbsp;' . $articleArray['article_title']));
            
            unset($articleArray);

            return $this->redirect()->toRoute('cms/default/article_page',array('action'=>'index', 'controller'=>'cms', 'page'=>$page), array('query'=>$array['query']));
        }

        $array['article_info']  = $this->getDbshopTable()->infoArticle(array('dbshop_article.article_id'=>$articleId, 'e.language'=>$this->getDbshopLang()->getLocale()));

        $array['article_class'] = $this->getDbshopTable('ArticleClassTable')->classOptions(0, $this->getDbshopTable('ArticleClassTable')->listArticleClass($this->getDbshopLang()->getLocale()));        
        
        return $array;
    }
    /** 
     * 删除文章
     */
    public function delAction ()
    {
        $articleId = (int) $this->request->getPost('article_id');
        if($articleId) {
            //为了记录操作日志
            $articleInfo = $this->getDbshopTable()->infoArticle(array('dbshop_article.article_id'=>$articleId, 'e.language'=>$this->getDbshopLang()->getLocale()));
            
            if($this->getDbshopTable()->delArticle(array('article_id'=>$articleId))) {
                $this->getDbshopTable('ArticleExtendTable')->delArticleExtend(array('article_id'=>$articleId));
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理文章'), 'operlog_info'=>$this->getDbshopLang()->translate('删除文章') . '&nbsp;' . $articleInfo->article_title));
                
                exit('true');   
            }
        }
        exit('false');
    }
    /**
     * 批量修改
     * @return \Zend\Http\Response
     */
    public function allArticleUpdateAction()
    {
        if($this->request->isPost()) {
            $articleArray  = $this->request->getPost()->toArray();
            if(is_array($articleArray) and !empty($articleArray)) {
                foreach($articleArray['article_sort'] as $key => $value) {
                    $this->getDbshopTable()->allUpdateArticle(array('article_sort'=>$value), array('article_id'=>$key));
                }
            }
        }
        //用于返回对应的分页数
        $page = $this->params('page',1);
        $array['page']     = $page;
        $array['query']= $this->request->getQuery()->toArray();

        return $this->redirect()->toRoute('cms/default/article_page',array('action'=>'index', 'controller'=>'cms', 'page'=>$array['page']), array('query'=>$array['query']));
    }
    /** 
     * 单页文章列表
     * @return multitype:
     */
    public function singleArticleAction()
    {
        $array = array();
        
        $array['single_article_list'] = $this->getDbshopTable('SingleArticleTable')->listSingleArticle(array('e.language'=>$this->getDbshopLang()->getLocale()));
        $array['article_tag_type'] = $this->readerArticleTagIni();
        
        return $array;
    }
    /** 
     * 添加单页文章
     * @return multitype:
     */
    public function addSingleArticleAction()
    {
        if($this->request->isPost()) {
            $singleArticleArray = $this->request->getPost()->toArray();
            $singleArticleArray['template_tag'] = DBSHOP_TEMPLATE;
            $singleArticleId    = $this->getDbshopTable('SingleArticleTable')->addSingleArticle($singleArticleArray);
            if($singleArticleId) {
                $singleArticleArray['single_article_id'] = $singleArticleId;
                $singleArticleArray['language']          = $this->getDbshopLang()->getLocale();
                //如果没有填写关键词则使用自动分词功能
                if(empty($singleArticleArray['single_article_keywords']) and !empty($singleArticleArray['single_article_body'])) {
                    $singleArticleArray['single_article_keywords'] = $this->getServiceLocator()->get('adminHelper')->dbshopPhpAnalysis($singleArticleArray['single_article_body'], 200);
                }
                //如果没有填写描述内容，则使用上面的关键字
                if(empty($singleArticleArray['single_article_description']) and !empty($singleArticleArray['single_article_keywords'])) {
                    $singleArticleArray['single_article_description'] = $singleArticleArray['single_article_keywords'];
                }
                $this->getDbshopTable('SingleArticleExtendTable')->addSingleArticleExtend($singleArticleArray);
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理单页文章'), 'operlog_info'=>$this->getDbshopLang()->translate('添加单页文章') . '&nbsp;' . $singleArticleArray['single_article_title']));
                unset($singleArticleArray);
                //跳转处理
                return $this->redirect()->toRoute('cms/default',array('controller'=>'cms', 'action'=>'singleArticle'));
            }
        }
        
        $array = array();
        $array['article_tag_type'] = $this->readerArticleTagIni();

        return $array;
    }
    /** 
     * 编辑单页文章
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:NULL
     */
    public function editSingleArticleAction()
    {
        $singleArticleId = (int) $this->params('article_id', 0);
        if($singleArticleId == 0) {
            return $this->redirect()->toRoute('cms/default',array('controller'=>'cms', 'action'=>'singleArticle'));
        }
        if($this->request->isPost()) {
            $singleArticleArray = $this->request->getPost()->toArray();
            $this->getDbshopTable('SingleArticleTable')->updateSingleArticle($singleArticleArray, array('single_article_id'=>$singleArticleId));
            //如果没有填写关键词则使用自动分词功能
            if(empty($singleArticleArray['single_article_keywords']) and !empty($singleArticleArray['single_article_body'])) {
                $singleArticleArray['single_article_keywords'] = $this->getServiceLocator()->get('adminHelper')->dbshopPhpAnalysis($singleArticleArray['single_article_body'], 200);
            }
            //如果没有填写描述内容，则使用上面的关键字
            if(empty($singleArticleArray['single_article_description']) and !empty($singleArticleArray['single_article_keywords'])) {
                $singleArticleArray['single_article_description'] = $singleArticleArray['single_article_keywords'];
            }
            $this->getDbshopTable('SingleArticleExtendTable')->updateSingleArticleExtend($singleArticleArray, array('single_article_id'=>$singleArticleId, 'language'=>$this->getDbshopLang()->getLocale()));
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理文章'), 'operlog_info'=>$this->getDbshopLang()->translate('更新单页文章') . '&nbsp;' . $singleArticleArray['single_article_title']));
            
            unset($singleArticleArray);

            return $this->redirect()->toRoute('cms/default',array('controller'=>'cms', 'action'=>'singleArticle'));
        }
        
        $array = array();
        $array['single_article_info'] = $this->getDbshopTable('SingleArticleTable')->infoSingleArticle(array('dbshop_single_article.single_article_id'=>$singleArticleId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        $array['article_tag_type'] = $this->readerArticleTagIni();
        
        return $array;
    }
    /** 
     * 删除单页文章
     */
    public function singleDelAction()
    {
        $singleArticleId = (int) $this->request->getPost('single_article_id');
        if($singleArticleId) {
            //为了记录操作日志
            $singleArticleInfo = $this->getDbshopTable('SingleArticleTable')->infoSingleArticle(array('dbshop_single_article.single_article_id'=>$singleArticleId, 'e.language'=>$this->getDbshopLang()->getLocale()));
            if($this->getDbshopTable('SingleArticleTable')->delSingleArticle(array('single_article_id'=>$singleArticleId))) {
                $this->getDbshopTable('SingleArticleExtendTable')->delSingleArticleExtend(array('single_article_id'=>$singleArticleId));
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理文章'), 'operlog_info'=>$this->getDbshopLang()->translate('删除单页文章') . '&nbsp;' . $singleArticleInfo->single_article_title));
                
                exit('true');
            }
        }
        exit('false');
    }
    private function readerArticleTagIni()
    {
        $articleIni       = array();
        $adIniReader      = new \Zend\Config\Reader\Ini();
        $articleIni       = $adIniReader->fromFile(DBSHOP_PATH . '/module/Shopfront/view/' . DBSHOP_TEMPLATE . '/shopfront/template.ini');
        
        return (isset($articleIni['article_tag_type']) ? $articleIni['article_tag_type'] : null);
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'ArticleTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}
