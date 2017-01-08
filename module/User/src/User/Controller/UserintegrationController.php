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

namespace User\Controller;

use Admin\Controller\BaseController;

class UserintegrationController extends BaseController
{
    /** 
     * 客户整合接口列表
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $integrationArray = array();
        $filePath          = DBSHOP_PATH . '/data/moduledata/User/Integration';
        if(is_dir($filePath)) {
            $dh = opendir($filePath);
            while (false !== ($fileName = readdir($dh))) {
                if($fileName != '.' and $fileName != '..' and $fileName != '.DS_Store') {
                    $integrationArray[] = include($filePath . '/' . $fileName);
                }
            }
        }
        return array('integration'=>$integrationArray);
    }
    /** 
     * 接口编辑
     */
    public function editAction()
    {
        $integrationType = $this->params('integrationtype');
        if(!in_array($integrationType, array('ucenter'))) $integrationType = 'ucenter';
        
        if($this->request->isPost()) {
            $integrationArray = $this->request->getPost()->toArray();
            $this->getServiceLocator()->get($integrationType)->saveIntegrationConfig($integrationArray);
            
            //操作日志记录
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('整合设置'), 'operlog_info'=>$this->getDbshopLang()->translate('更新整合') . '&nbsp;' . $integrationArray['integration_name']));
            return $this->redirect()->toRoute('userintegration/default',array('action'=>'edit', 'paytype'=>$integrationType));
        }
        
        $integrationInput = $this->getServiceLocator()->get($integrationType)->getFromInput();
        
        return array('form_input'=>$integrationInput);
    }
}