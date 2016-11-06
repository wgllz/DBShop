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

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;

class BaseController extends AbstractActionController
{
    protected $dbTables = array();
    protected $translator;
    
    protected function attachDefaultListeners()
    {
        $events = $this->getEventManager();
        $events->attach(MvcEvent::EVENT_DISPATCH, array($this,'checkAuth'), 200);
        
        parent::attachDefaultListeners();
    }
    /**
     * 检测管理员登录状态
     * @param MvcEvent $e
     */
    public function checkAuth (MvcEvent $e)
    {
        //$this->route->getParam('__CONTROLLER__');获取controller的名字
        $controllerName = $e->getRouteMatch()->getParam('controller');
        $actionName     = $e->getRouteMatch()->getParam('action');
        if($controllerName == 'Admin\Controller\Admin' and $actionName == 'index') {
            return ;
        }
        //这里用于多图上传时使用，flash无法传递cookie信息，会出现302错误（firefox）
        if($actionName == 'addimage') {
            return ;
        }
        //检查会员登录        
        if(!$this->getServiceLocator()->get('adminHelper')->returnAuth('admin_id')) {
            return $this->redirect()->toRoute('admin');
        }
        //权限检查
        $namespace    = $e->getRouteMatch()->getParam('__NAMESPACE__');
        $controller   = ucwords($e->getRouteMatch()->getParam('__CONTROLLER__'));//单独的controller名字，没有 斜杠 \
        $sourceArray  = include DBSHOP_PATH . '/module/Admin/data/purviewArray.php';
        $purviewArray = $this->getServiceLocator()->get('adminHelper')->returnAuth('admin_group_purview');
        if(isset($sourceArray[$namespace][$controller]) and in_array($actionName, $sourceArray[$namespace][$controller])) {
            //当为全部权限时
            if(isset($purviewArray['purviewAll']) and $purviewArray['purviewAll'] == 1) return ;
            //当设定个性权限
            $purviewStr = $namespace . '|' . $controller . '|' . $actionName;
            if(!isset($purviewArray[$purviewStr])) {
                return $this->redirect()->toRoute('admin/default',array('action'=>'purviewError'));
            }
        }
    }
    /**
     * 语言包调用
     * @return Ambigous <object, multitype:, \Zend\I18n\Translator\Translator>
     */
    protected function getDbshopLang ()
    {
        if (! $this->translator) {
            $this->translator = $this->getServiceLocator()->get('translator');
        }
        return $this->translator;
    }
    /**
     * 添加操作日志
     * @param array $data
     */
    protected function insertOperlog(array $data)
    {
        $operlogArray = array();
        $operlogArray['log_oper_user']      = $this->getServiceLocator()->get('adminHelper')->returnAuth('admin_name');
        $operlogArray['log_oper_usergroup'] = $this->getServiceLocator()->get('adminHelper')->returnAuth('admin_group_name');
        $operlogArray['log_time']           = time();
        $operlogArray['log_ip']             = $this->getRequest()->getServer('REMOTE_ADDR');
        $operlogArray['log_content']        = '[' .$data['operlog_name']. ']&nbsp;' . $data['operlog_info'];
        $this->getServiceLocator()->get('OperlogTable')->addOperlog($operlogArray);
    }
}

?>