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

namespace Operlog\Controller;

use Admin\Controller\BaseController;

class OperlogController extends BaseController
{
    /** 
     * !CodeTemplates.overridecomment.nonjd!
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $array        = array();

        $searchArray  = array();
        if($this->request->isGet()) {
            $searchArray          = $this->request->getQuery()->toArray();
            $array['searchArray'] = $searchArray;
        }
        $page = $this->params('page',1);
        $array['operlog_list'] = $this->getDbshopTable()->allOperlog(array('page'=>$page, 'page_num'=>20), $searchArray);

        //管理员组列表
        $array['group_array'] = $this->getDbshopTable('AdminGroupTable')->listAdminGroup(array('e.language'=>$this->getDbshopLang()->getLocale()));

        return $array;
    }
    /** 
     * 日志清除处理
     */
    public function clearlogAction()
    {
        $clearTime = intval($this->request->getPost('clear_time'));
        if($clearTime > 0) {
            $clearTime = time() - 3600 * 24 * $clearTime;
            $this->getDbshopTable('OperlogTable')->clearOperlog(array('log_time < ' . $clearTime));
        }
        return $this->redirect()->toRoute('operlog/default');
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'OperlogTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}
