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
            //服务器端数据验证
            $userValidate = new FormUserValidate($this->getDbshopLang());
            $userValidate->checkUserForm($this->request->getPost(), 'homeUserEdit');

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
        $array['other_login'] = $this->getDbshopTable('OtherLoginTable')->infoOtherLogin(array('u.user_id'=>$userId));

        return $array;
    }
    /**
     * 绑定处理操作
     */
    public function otherloginAction() {
        $loginService = $this->checkOtherLoginConfig();
        $loginService->toLogin();
    }
    /**
     * 回调页面
     * @return \Zend\Http\Response
     */
    public function othercallbackAction() {
        $loginService  = $this->checkOtherLoginConfig();
        $callBackState = $loginService->callBack();
        $openId        = $loginService->getOpenId();
        //回调正确，检查该用户是否已经存在
        $userInfo      = $this->getDbshopTable('OtherLoginTable')->infoOtherLogin(array('dbshop_other_login.open_id'=>$openId));
        if($userInfo) {
            exit($this->getDbshopLang()->translate('该QQ已经在系统绑定，不能重复绑定！') . '&nbsp;<a href="' .$this->url()->fromRoute('fronthome/default', array('action'=>'qqset')). '">' . $this->getDbshopLang()->translate('返回') . '</a>');
        } else {
            $otherLoginArray = array(
                'user_id'       => $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id'),
                'open_id'       => $openId,
                'ol_add_time'   => time(),
                'login_type'    => 'QQ'
            );
            $addOtherLogin = $this->getDbshopTable('OtherLoginTable')->addOtherLogin($otherLoginArray);
            if($addOtherLogin) {
                return $this->redirect()->toRoute('fronthome/default', array('action'=>'qqset'));
            } else {
                exit($this->getDbshopLang()->translate('绑定失败，请稍后再试！') . '&nbsp;<a href="' .$this->url()->fromRoute('fronthome/default', array('action'=>'qqset')). '">' . $this->getDbshopLang()->translate('返回') . '</a>');
            }
        }
    }
    /**
     * 解绑QQ绑定
     * @return \Zend\Http\Response
     */
    public function delotherloginAction() {
        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        if($userId) {
            $this->getDbshopTable('OtherLoginTable')->delOtherLogin(array('user_id'=>$userId, 'login_type'=>'QQ'));
        }
        return $this->redirect()->toRoute('fronthome/default', array('action'=>'qqset'));
    }
    /**
     * 绑定QQ设置信息
     * @return array|object|\Zend\Http\Response
     */
    private function checkOtherLoginConfig() {
        //判断是否启用了第三方登陆
        if($this->getServiceLocator()->get('frontHelper')->getUserIni('qq_login_state') != 'true') {
            return $this->redirect()->toRoute('fronthome/default');
        }

        $loginService     = $this->getServiceLocator()->get('QqLogin');
        $loginConfigState = $loginService->getLoginConfigState();
        if(is_string($loginConfigState) and $loginConfigState == 'configError') exit($this->getDbshopLang()->translate('该绑定方式的配置信息错误，必须在公网上进行测试！'));

        $loginService->redirectUri = $this->getRequest()->getServer('SERVER_NAME') . $this->url()->fromRoute('fronthome/default',array('action'=>'othercallback'));

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
