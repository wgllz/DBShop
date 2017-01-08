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

use User\FormValidate\FormUserValidate;

class HomeController extends FronthomeController
{
    private $dbTables = array();
    private $translator;
    
    /**
     * The default action - show the home page
     */
    public function indexAction ()
    {
        $array = array();
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('中心首页');
        
        //统计使用
        $this->layout()->dbTongJiPage= 'user_home';
        
        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        //用户信息
        $array['user_info'] = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$userId));
        //订单状态数量
        $array['order_state_num'] = $this->getDbshopTable('OrderTable')->allStateNumOrder($userId);
        //订单列表16条
        $array['order_list'] = $this->getDbshopTable('OrderTable')->allOrder(array('buyer_id'=>$userId,'refund_state'=>'0', 'order_state NOT IN (0,60)'), array(), 16);

        return $array;
    }
    /** 
     * 编辑会员基本信息
     * @return multitype:NULL
     */
    public function usereditAction ()
    {
        $array = array();
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('账户信息-基本信息');

        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');

        if($this->request->isPost()) {
            //判断注册项中是否开启了邮箱注册
            $userEmailRegisterState = $this->getServiceLocator()->get('frontHelper')->getRegOrLoginIni('register_email_state');
            //判断注册项中是否开启手机号码
            $userPhoneRegisterState = $this->getServiceLocator()->get('frontHelper')->getRegOrLoginIni('register_phone_state');
            //服务器端数据验证
            $userValidate = new FormUserValidate($this->getDbshopLang());
            if($userEmailRegisterState == 'true' and $userPhoneRegisterState == 'true') $userValidate->checkUserForm($this->request->getPost(), 'homeUserAllEdit');
            if($userEmailRegisterState != 'true' and $userPhoneRegisterState == 'true') $userValidate->checkUserForm($this->request->getPost(), 'homeUserPhoneEdit');
            if($userEmailRegisterState == 'true' and $userPhoneRegisterState != 'true') $userValidate->checkUserForm($this->request->getPost(), 'homeUserEdit');

            $userArray = $this->request->getPost()->toArray();

            $userInfo  = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$userId));
            $userArray['old_user_avatar'] = (isset($userInfo->user_avatar) and !empty($userInfo->user_avatar)) ? $userInfo->user_avatar : '';

            //会员头像上传
            $userAvatar = $this->getServiceLocator()->get('shop_other_upload')->userAvatarUpload($userId, 'user_avatar', (isset($userArray['old_user_avatar']) ? $userArray['old_user_avatar'] : ''), $this->getServiceLocator()->get('adminHelper')->defaultShopSet('shop_user_avatar_width'), $this->getServiceLocator()->get('adminHelper')->defaultShopSet('shop_user_avatar_height'));
            $userArray['user_avatar'] = $userAvatar['image'];
            
            if($userArray['user_avatar'] != '' and $userArray['user_avatar'] != $this->getServiceLocator()->get('frontHelper')->getUserSession('user_avatar')) {
                $this->getServiceLocator()->get('frontHelper')->setUserSession(array('user_avatar'=>$userArray['user_avatar']));
            }
            
            $this->getDbshopTable('UserTable')->updateUser($userArray,array('user_id'=>$userId));
            $this->getServiceLocator()->get('frontHelper')->setUserSession(array('user_email'=>$userArray['user_email']));//修改session的user_email
            $this->getServiceLocator()->get('frontHelper')->setUserSession(array('user_phone'=>$userArray['user_phone']));//修改session的user_phone
            $array['success_msg'] = $this->getDbshopLang()->translate('会员基本信息修改成功！');
        }
        $array['user_info']  = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$userId));
        $array['user_group'] = $this->getDbshopTable('UserGroupExtendTable')->infoUserGroupExtend(array('group_id'=>$array['user_info']->group_id));
        
        return $array;
    }
    /** 
     * 会员密码修改更新
     * @return multitype:NULL Ambigous <string, string, NULL, multitype:NULL , multitype:string NULL >
     */
    public function userpasswdAction ()
    {
        $array = array();
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('账户信息-密码修改');

        //判断是否是第三方登录用户，如果是判断是否未修改密码状态，如果是则取消修改密码时对于原始密码的要求
        $array['other_login_passwd'] = false;
        $otherLoginInfo = $this->getDbshopTable('OtherLoginTable')->infoOtherLogin(array('dbshop_other_login.user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        if($otherLoginInfo) {
            if($otherLoginInfo->user_password == $this->getServiceLocator()->get('frontHelper')->getPasswordStr($otherLoginInfo->open_id)) {
                $array['other_login_passwd'] = true;
            }
        }

        if($this->request->isPost()) {
            //服务器端数据验证
            $userValidate = new FormUserValidate($this->getDbshopLang());
            $userValidate->checkUserForm($this->request->getPost(), ($array['other_login_passwd'] ? 'homeOtherPasswd' : 'homeUserPasswd'));//第三方认证与站点注册用户的验证不一样

            $passwdArray = $this->request->getPost()->toArray();
            //对于原始密码的获取，当是第三方认证第一次修改密码时，不需要输入原始密码，所以需要程序获取默认密码
            $passwdArray['old_user_password'] = ($array['other_login_passwd'] ? $otherLoginInfo->open_id : $passwdArray['old_user_password']);

            $userInfo    = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
            //判断原始密码是否正确
            if($userInfo->user_password == $this->getServiceLocator()->get('frontHelper')->getPasswordStr($passwdArray['old_user_password'])) {
                $passwdArray['user_password'] = $this->getServiceLocator()->get('frontHelper')->getPasswordStr($passwdArray['user_password']);
                $this->getDbshopTable('UserTable')->updateUser($passwdArray,array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
                $array['success_msg'] = $this->getDbshopLang()->translate('会员密码修改成功！');
            } else {
                $array['false_msg'] = $this->getDbshopLang()->translate('会员密码修改失败,原始密码错误！');
            }
        }
        
        return $array;
    }
    /**
     * 绑定QQ页面
     * @return array|\Zend\Http\Response
     */
    public function qqsetAction()
    {
        //判断是否启用了第三方登陆
        if($this->getServiceLocator()->get('frontHelper')->getUserIni('qq_login_state') != 'true') {
            return $this->redirect()->toRoute('fronthome/default');
        }

        $array  = array();
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('账户信息-QQ绑定');

        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        $array['other_login'] = $this->getDbshopTable('OtherLoginTable')->infoOtherLogin(array('dbshop_other_login.login_type'=>'QQ','u.user_id'=>$userId));

        return $array;
    }
    /**
     * 绑定微信页面
     * @return array|\Zend\Http\Response
     */
    public function weixinsetAction()
    {
        //判断是否启用了第三方登陆
        if($this->getServiceLocator()->get('frontHelper')->getUserIni('weixin_login_state') != 'true') {
            return $this->redirect()->toRoute('fronthome/default');
        }

        $array  = array();
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('账户信息-微信绑定');

        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        $array['other_login'] = $this->getDbshopTable('OtherLoginTable')->infoOtherLogin(array('dbshop_other_login.login_type'=>'Weixin','u.user_id'=>$userId));

        return $array;
    }
    /**
     * 绑定支付宝页面
     * @return array|\Zend\Http\Response
     */
    public function alipaysetAction()
    {
        //判断是否启用了第三方登陆
        if($this->getServiceLocator()->get('frontHelper')->getUserIni('alipay_login_state') != 'true') {
            return $this->redirect()->toRoute('fronthome/default');
        }

        $array  = array();
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('账户信息-支付宝绑定');

        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        $array['other_login'] = $this->getDbshopTable('OtherLoginTable')->infoOtherLogin(array('dbshop_other_login.login_type'=>'Alipay','u.user_id'=>$userId));

        return $array;
    }
    /**
     * 绑定处理操作
     */
    public function otherloginAction() {
        $loginType = $this->request->getQuery('login_type');

        $loginService = $this->checkOtherLoginConfig($loginType);
        $loginService->toLogin();
    }
    /**
     * 解绑QQ绑定
     * @return \Zend\Http\Response
     */
    public function delotherloginAction() {
        $lType            = $this->request->getQuery('login_type');
        $loginType        = 'QQ';
        $actionName       = 'qqset';
        if($lType !='' and $lType != 'qq') {
            $loginType = ucfirst($lType);
            $actionName= strtolower($lType).'set';
        }

        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        if($userId) {
            $this->getDbshopTable('OtherLoginTable')->delOtherLogin(array('user_id'=>$userId, 'login_type'=>$loginType));
        }
        return $this->redirect()->toRoute('fronthome/default', array('action'=>$actionName));
    }
    /**
     * 绑定QQ设置信息
     * @return array|object|\Zend\Http\Response
     */
    private function checkOtherLoginConfig($loginType='QQ') {
        //判断是否启用了第三方登陆
        if($this->getServiceLocator()->get('frontHelper')->getUserIni('qq_login_state') != 'true' and $this->getServiceLocator()->get('frontHelper')->getUserIni('weixin_login_state') != 'true') {
            return $this->redirect()->toRoute('fronthome/default');
        }

        if($loginType == 'weixin') {//微信
            $loginService     = $this->getServiceLocator()->get('WeixinLogin');
        } else {//QQ
            $loginService     = $this->getServiceLocator()->get('QqLogin');
        }
        $loginConfigState = $loginService->getLoginConfigState();

        if(is_string($loginConfigState) and $loginConfigState == 'configError') exit($this->getDbshopLang()->translate('该绑定方式的配置信息错误，必须在公网上进行测试！'));

        if($loginType == 'weixin') {
            $loginService->redirectUri = $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost() . $this->url()->fromRoute('frontuser/default/other_login_type',array('action'=>'othercallback', 'login_type'=>'weixin'));
        } else {
            $loginService->redirectUri = $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost() . $this->url()->fromRoute('frontuser/default',array('action'=>'othercallback'));
        }

        return $loginService;
    }
    /** 
     * 商品咨询列表
     * @return multitype:unknown NULL
     */
    public function goodsaskAction ()
    {
    	$array = array();
    	//顶部title使用
    	$this->layout()->title_name = $this->getDbshopLang()->translate('我的咨询');
    	
    	//咨询分页
    	$page = $this->params('page',1);
    	$array['page']     = $page;
    	$array['ask_list'] = $this->getDbshopTable('GoodsAskTable')->listGoodsAsk(array('page'=>$page, 'page_num'=>16), array('dbshop_goods_ask.ask_writer'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_name'), 'e.language'=>$this->getDbshopLang()->getLocale()));

    	return $array;
    }
    /** 
     * 商品咨询删除
     */
    public function askdelAction ()
    {
    	$askId = (int) $this->params('ask_id');
    	if($askId != 0) $this->getDbshopTable('GoodsAskTable')->delGoodsAsk(array('ask_id'=>$askId, 'ask_writer'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_name')));
    	
    	return $this->redirect()->toRoute('fronthome/default', array('action'=>'goodsask'));
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
