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

class ClassController extends BaseController
{
    /** 
     * 文章分类列表
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction ()
    {
        $array = array();
        $array['article_class'] = $this->classlistfunAction();
        
        return $array;
    }
    /** 
     * 添加文章分类
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:
     */
    public function addAction ()
    {
        $array = array();
        if($this->request->isPost()) {
            //接收POST数据
            $classArray                       = $this->request->getPost()->toArray();
            $classArray['article_class_path'] = $classArray['article_class_top_id'];

            if($this->getServiceLocator()->get('adminHelper')->defaultShopSet('dbshop_news', 'index')) {//当前台启用的模板的配置文件中，存在新闻设置时，启用
                $classArray['index_news'] = (isset($classArray['index_news']) ? 1 : 2);
                //当值为1时，下面将分类里有1的值，设置为2
                if($classArray['index_news'] == 1) $this->getDbshopTable()->updateArticleCalss(array('index_news'=>2), array('index_news'=>1));
            }

            $articleClassId = $this->getDbshopTable()->addArticleClass($classArray);
            if($articleClassId) {
                $classArray['article_class_id'] = $articleClassId;
                $classArray['language']         = $this->getDbshopLang()->getLocale();
                $this->getDbshopTable('ArticleClassExtendTable')->addArticleClassExtend($classArray);
            }
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理文章分类'), 'operlog_info'=>$this->getDbshopLang()->translate('添加分类') . '&nbsp;' . $classArray['article_class_name']));
            
            //跳转处理
            return $this->redirect()->toRoute('cms/default',array('controller'=>'class')); 
        }
        //文章分类
        $array['article_class'] = $this->classlistfunAction();
        //添加子分类时，使用接收
        $array['article_class_top_id'] = (int) $this->params('article_class_id', 0);

        return $array;
    }
    /**
     * 编辑文章分类
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:NULL \Cms\Controller\unknown
     */
    public function editAction ()
    {
        $articleClassId = (int) $this->params('article_class_id', 0);
        if(!$articleClassId) {
            return $this->redirect()->toRoute('cms/default',array('controller'=>'class'));
        }
        $array = array();
        if($this->request->isPost()) {
            $classArray  = $this->request->getPost()->toArray();
            $classArray['article_class_path'] = $classArray['article_class_top_id'];

            if($this->getServiceLocator()->get('adminHelper')->defaultShopSet('dbshop_news', 'index')) {//当前台启用的模板的配置文件中，存在新闻设置时，启用
                $classArray['index_news'] = (isset($classArray['index_news']) ? 1 : 2);
                //当值为1时，下面将分类里有1的值，设置为2
                if($classArray['index_news'] == 1) $this->getDbshopTable()->updateArticleCalss(array('index_news'=>2), array('index_news'=>1));
            }

            $editState   = $this->getDbshopTable()->editArticleClass($classArray, array('article_class_id'=>$articleClassId));
            if($editState) {
                $this->getDbshopTable('ArticleClassExtendTable')->editArticleClassExtend($classArray, array('article_class_id'=>$articleClassId, 'language'=>$this->getDbshopLang()->getLocale()));
            }
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理文章分类'), 'operlog_info'=>$this->getDbshopLang()->translate('更新分类') . '&nbsp;' . $classArray['article_class_name']));
            
            //跳转
            if($classArray['class_save_type'] != 'save_return_edit') {
                return $this->redirect()->toRoute('cms/default',array('controller'=>'class'));
            }
            unset($classArray);
            
            $array['success_msg'] = $this->getDbshopLang()->translate('文章分类编辑成功！');
        }
        //文章分类信息
        $array['article_class_info'] = $this->getDbshopTable()->infoArticleClass(array('e.article_class_id'=>$articleClassId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        //文章分类
        $array['article_class'] = $this->classlistfunAction();

        return $array;
    }
    /**
     * 删除文章分类
     */
    public function delAction ()
    {
        $articleClassId = intval($this->request->getPost('article_class_id'));
        if($articleClassId) {
            //为了记录操作日志
            $articleClassInfo = $this->getDbshopTable()->infoArticleClass(array('e.article_class_id'=>$articleClassId, 'e.language'=>$this->getDbshopLang()->getLocale()));
            //判断该分类下是否还有文章
            if($this->getDbshopTable('ArticleTable')->infoArticle(array('dbshop_article.article_class_id'=>$articleClassId))) exit('error_article');
            //如果该分类下无文章，可以删除
            if($this->getDbshopTable()->delArticleClass(array('article_class_id'=>$articleClassId))) {
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理文章分类'), 'operlog_info'=>$this->getDbshopLang()->translate('删除分类') . '&nbsp;' . $articleClassInfo->article_class_name));
                
                exit('true');
            }
        }
        exit('false');
    }
    /**
     * 批量修改分类排序
     * @return \Zend\Http\Response
     */
    public function allUpdateAction()
    {
        if($this->request->isPost()) {
            $classArray  = $this->request->getPost()->toArray();
            if(is_array($classArray) and !empty($classArray)) {
                foreach($classArray['article_class_sort'] as $key => $value) {
                    $this->getDbshopTable()->updateArticleCalss(array('article_class_sort'=>$value), array('article_class_id'=>$key));
                }
            }
        }
        //跳转处理
        return $this->redirect()->toRoute('cms/default',array('controller'=>'class'));
    }
    /**
     * 检查分类的可设置性
     */
    public function checkclasstopAction ()
    {
        $articleClassId  = (int)$this->params('article_class_id', 0);
        $classTopId      = intval($this->request->getPost('article_class_top_id'));
        if($classTopId == $articleClassId or $articleClassId == 0) {//上级分类不能与当前分类相同
            echo 'false';
            exit();
        }
        //当前分类，不能将上级分类设置为当前分类子类
        $sun_class    = $this->getDbshopTable()->getSunClassId($articleClassId, $this->getDbshopLang()->getLocale());
        if(in_array($classTopId,$sun_class)) echo 'false';
        else echo 'true';
        exit;
    }
    /**
     * 调用文章分类
     * @return unknown
     */
    public function classlistfunAction ()
    {
        $classArray = $this->getDbshopTable()->listArticleClass($this->getDbshopLang()->getLocale());
        $classArray = $this->getDbshopTable()->classOptions(0,$classArray);
        return $classArray;
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'ArticleClassTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}
