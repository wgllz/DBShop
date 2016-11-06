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

class UsergroupController extends BaseController
{
    /**
     * 客户组列表
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction ()
    {
        $array = array();
        $array['group_array'] = $this->getDbshopTable()->listUserGroup(array('language'=>$this->getDbshopLang()->getLocale()));

        return $array;
    }
    /**
     * 添加客户组信息
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function addAction ()
    {
        $array = array();
        if($this->request->isPost()) {
            $userGroupArray = $this->request->getPost()->toArray();
            $userGroupId    = $this->getDbshopTable()->addUserGroup($userGroupArray);
            
            if($userGroupId) {
                $userGroupArray['group_id'] = $userGroupId;
                $userGroupArray['language'] = $this->getDbshopLang()->getLocale();
                $this->getDbshopTable('UserGroupExtendTable')->addUserGroupExtend($userGroupArray);
            }
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理客户组'), 'operlog_info'=>$this->getDbshopLang()->translate('添加客户组') . '&nbsp;' . $userGroupArray['user_group_name']));
            
            return $this->redirect()->toRoute('usergroup/default',array('controller'=>'usergroup','action'=>'index'));
        }
    }
    /**
     * 客户组更新
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:NULL
     */
    public function editAction ()
    {
        $userGroupId = (int) $this->params('user_group_id',0);
        if(!$userGroupId) {
            return $this->redirect()->toRoute('usergroup/default',array('controller'=>'usergroup','action'=>'index'));
        }
        $array = array();
        if($this->request->isPost()) {
            $userGroupArray = $this->request->getPost()->toArray();
            $this->getDbshopTable('UserGroupTable')->updateUserGroup($userGroupArray, array('group_id'=>$userGroupId));
            $this->getDbshopTable('UserGroupExtendTable')->updateUserGroupExtend($userGroupArray, array('group_id'=>$userGroupId,'language'=>$this->getDbshopLang()->getLocale()));
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理客户组'), 'operlog_info'=>$this->getDbshopLang()->translate('更新客户组') . '&nbsp;' . $userGroupArray['user_group_name']));

            return $this->redirect()->toRoute('usergroup/default',array('controller'=>'usergroup','action'=>'index'));
        }
        $array['group_info'] = $this->getDbshopTable('UserGroupExtendTable')->infoUserGroupExtend(array('group_id'=>$userGroupId,'language'=>$this->getDbshopLang()->getLocale()));

        $array['group_info_one'] = $this->getDbshopTable('UserGroupTable')->infoUserGroup(array('group_id'=>$userGroupId));

        return $array;
    }
    /** 
     * 客户组删除
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function delAction ()
    {
        $userGroupId = (int) $this->request->getPost('group_id');
        if(!$userGroupId or $userGroupId == 1) {
            exit('false');
        }
        $userInfo    = $this->getDbshopTable('UserTable')->infoUser(array('group_id'=>$userGroupId));
        if(!empty($userInfo)) {
            echo 'false';
            exit();
        }
        //为了记录操作日志
        $userGroupInfo = $this->getDbshopTable('UserGroupExtendTable')->infoUserGroupExtend(array('group_id'=>$userGroupId,'language'=>$this->getDbshopLang()->getLocale()));
        
        //如果无客户信息，进行删除操作
        $delState    = $this->getDbshopTable('UserGroupTable')->delUserGroup(array('group_id'=>$userGroupId));
        
        if($delState) {
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理客户组'), 'operlog_info'=>$this->getDbshopLang()->translate('删除客户组') . '&nbsp;' . $userGroupInfo->group_name));
            echo 'true';
        } else {
            echo 'false';
        }
        exit();
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'UserGroupTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}