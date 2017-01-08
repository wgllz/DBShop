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
use Zend\Mvc\MvcEvent;
use Zend\View\Helper\ServerUrl;

class MobileHomeController  extends AbstractActionController
{
    protected function attachDefaultListeners()
    {
        $events = $this->getEventManager();
        $events->attach(MvcEvent::EVENT_DISPATCH, array($this,'checkMobileUserAuth'), 200);

        parent::attachDefaultListeners();
    }
    public function checkMobileUserAuth ()
    {
        $url    = new ServerUrl();
        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');

        $action = $this->params('action');
        if($userId == '' and $action != 'orderReturnPay' and $action != 'orderNotifyPay' and $action != 'notify') {
            return $this->redirect()->toRoute('m_user/default', array('action'=>'login'), array('query'=>array('http_referer'=>urlencode($url->__invoke(true)))));
        }
        //判断该用户是否在登录后，后台被管理员删除
        if($userId != '') {
            $userInfo = $this->getServiceLocator()->get('UserTable')->infoUser(array('user_id'=>$userId));
            if($userInfo == null) {
                $array = array(
                    'user_name'      => '',
                    'user_id'        => '',
                    'user_email'     => '',
                    'user_group_name'=> '',
                    'user_phone'     => '',
                    'group_id'       => '',
                    'user_avatar'    => ''
                );
                $this->getServiceLocator()->get('frontHelper')->setUserSession($array);
                return $this->redirect()->toRoute('m_user/default', array('action'=>'login'), array('query'=>array('http_referer'=>urlencode($url->__invoke(true)))));
            }
        }

    }
} 