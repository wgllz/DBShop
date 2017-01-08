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
use Zend\Mvc\MvcEvent;
use Zend\View\Helper\ServerUrl;

class FronthomeController extends AbstractActionController
{
    protected function attachDefaultListeners()
    {
        $events = $this->getEventManager();
        $events->attach(MvcEvent::EVENT_DISPATCH, array($this,'checkUserAuth'), 200);

        parent::attachDefaultListeners();
    }
    public function checkUserAuth ()
    {
        $url    = new ServerUrl();
        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        
        if($userId == ''
            and $this->params('action') != 'orderReturnPay'
            and $this->params('action') != 'orderNotifyPay'
            and $this->params('action') != 'paycheckReturnPay'
            and $this->params('action') != 'paycheckNotifyPay') {
            return $this->redirect()->toRoute('frontuser/default', array('action'=>'login'), array('query'=>array('http_referer'=>urlencode($url->__invoke(true)))));
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
                return $this->redirect()->toRoute('frontuser/default', array('action'=>'login'), array('query'=>array('http_referer'=>urlencode($url->__invoke(true)))));
            }
        }
        
    }
}