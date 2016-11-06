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

namespace Links\Controller;

use Admin\Controller\BaseController;

class LinksController extends BaseController
{
    /** 
     * 友情链接列表
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $array = array();
        
        $array['links_list'] = $this->getDbshopTable()->listLinks();
        
        return $array;
    }
    /** 
     * 添加友情链接
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function addAction ()
    {
        if($this->request->isPost()) {
            $linksArray = $this->request->getPost()->toArray();
            //网站logo上传
            $linksLogo  = $this->getServiceLocator()->get('shop_other_upload')->linksWebnameLogoUpload('links_logo');
            if(!empty($linksLogo['image'])) {
                $linksArray['links_logo'] = $linksLogo['image'];
            }
            
            $this->getDbshopTable()->addLinks($linksArray);
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('友情链接'), 'operlog_info'=>$this->getDbshopLang()->translate('添加链接') . '&nbsp;' . $linksArray['links_webname']));
            
            unset($linksArray);
            
            return $this->redirect()->toRoute('links/default');
        }
    }
    /** 
     * 编辑友情链接
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:NULL
     */
    public function editAction ()
    {
        $linksId = (int) $this->params('links_id', 0);
        if($linksId == 0) return $this->redirect()->toRoute('links/default');
        
        if($this->request->isPost()) {
            $linksArray = $this->request->getPost()->toArray();
            //网站logo上传
            $linksLogo  = $this->getServiceLocator()->get('shop_other_upload')->linksWebnameLogoUpload('links_logo', (isset($linksArray['old_links_logo']) ? $linksArray['old_links_logo'] : ''));
            $linksArray['links_logo'] = $linksLogo['image'];

            $this->getDbshopTable()->updateLinks($linksArray, array('links_id'=>$linksId));
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('友情链接'), 'operlog_info'=>$this->getDbshopLang()->translate('更新链接') . '&nbsp;' . $linksArray['links_webname']));
            
            unset($linksArray);
            
            return $this->redirect()->toRoute('links/default');
        }
        
        $array = array();
        
        $array['links_info'] = $this->getDbshopTable()->infoLinks(array('links_id'=>$linksId));
        
        return $array;
    }
    public function allUpdateAction()
    {
        if($this->request->isPost()) {
            $linksArray  = $this->request->getPost()->toArray();
            if(is_array($linksArray) and !empty($linksArray)) {
                foreach($linksArray['links_sort'] as $key => $value) {
                    $this->getDbshopTable()->allUpdateLinks(array('links_sort'=>$value), array('links_id'=>$key));
                }
            }
        }

        return $this->redirect()->toRoute('links/default');
    }
    /** 
     * 删除友情链接
     */
    public function delAction ()
    {
        $linksId = (int) $this->request->getPost('links_id');
        if($linksId) {
            $infoLinks = $this->getDbshopTable()->infoLinks(array('links_id'=>$linksId));
            if($this->getDbshopTable()->delLinks(array('links_id'=>$linksId))) {
                if($infoLinks->links_logo != '') @unlink(DBSHOP_PATH . $infoLinks->links_logo);
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('友情链接'), 'operlog_info'=>$this->getDbshopLang()->translate('删除链接') . '&nbsp;' . $infoLinks->links_webname));
                exit('true');
            }
        }
        exit('false');
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'LinksTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}
