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

use User\FormValidate\FormUserValidate;

class HomeController extends MobileHomeController
{
    private $dbTables = array();
    private $translator;

    public function indexAction ()
    {
        $array = array();
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('个人中心');

        //统计使用
        $this->layout()->dbTongJiPage= 'user_home';

        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        //用户信息
        $array['user_info'] = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$userId));

        //我的收藏
        $array['favorites_goods'] = $this->getDbshopTable('UserFavoritesTable')->favoritesNum(5, array('dbshop_user_favorites.user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));

        //最新订单
        $array['order_list'] = $this->getDbshopTable('OrderTable')->allOrder(array('buyer_id'=>$userId, 'order_state NOT IN (0,60)'), array(), 5);

        //我的咨询
        $array['goods_ask_list'] = $this->getDbshopTable('GoodsAskTable')->numGoodsAsk(5, array('dbshop_goods_ask.ask_writer'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_name'), 'e.language'=>$this->getDbshopLang()->getLocale()));

        return $array;
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
        $array['goods_ask_list'] = $this->getDbshopTable('GoodsAskTable')->listGoodsAsk(array('page'=>$page, 'page_num'=>6), array('dbshop_goods_ask.ask_writer'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_name'), 'e.language'=>$this->getDbshopLang()->getLocale()));

        return $array;
    }
    /**
     * 商品咨询删除
     */
    public function askdelAction ()
    {
        $askId = (int) $this->params('ask_id');
        $type        = $this->request->getQuery('type');
        if($askId != 0) $this->getDbshopTable('GoodsAskTable')->delGoodsAsk(array('ask_id'=>$askId, 'ask_writer'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_name')));

        if(isset($type) and $type == 'home') {
            return $this->redirect()->toRoute('m_home/default');
        }
        return $this->redirect()->toRoute('m_home/default', array('action'=>'goodsask'));
    }
    /**
     * 编辑会员基本信息
     * @return multitype:NULL
     */
    public function usereditAction ()
    {
        $array = array();
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('账户资料修改');

        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');

        if($this->request->isPost()) {
            //服务器端数据验证
            $userValidate = new FormUserValidate($this->getDbshopLang());
            $userValidate->checkUserForm($this->request->getPost(), 'homeUserEdit');

            $userArray = $this->request->getPost()->toArray();

            $checkEmail = $this->getDbshopTable('UserTable')->infoUser(array('user_id!='.$userId.' and user_email="'.$userArray['user_email'].'"'));
            if($checkEmail) {
                $message = $this->getDbshopLang()->translate('您修改的邮箱已经存在，请重新修改！');
                $locationUrl = '';
            } else {
                $this->getDbshopTable('UserTable')->updateUser($userArray,array('user_id'=>$userId));
                $this->getServiceLocator()->get('frontHelper')->setUserSession(array('user_email'=>$userArray['user_email']));//修改session的user_email
                $this->getServiceLocator()->get('frontHelper')->setUserSession(array('user_phone'=>$userArray['user_phone']));//修改session的user_phone
                $message = $this->getDbshopLang()->translate('会员信息修改成功！');
                $locationUrl = 'window.location.href="'.$this->url()->fromRoute('m_home/default').'";';
            }
            echo '<script>alert("'.$message.'");'.$locationUrl.'</script>';
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
        $this->layout()->title_name = $this->getDbshopLang()->translate('密码修改');

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

                $message = $this->getDbshopLang()->translate('会员密码修改成功！');
                $locationUrl = 'window.location.href="'.$this->url()->fromRoute('m_home/default').'";';
            } else {
                $message = $this->getDbshopLang()->translate('会员密码修改失败,原始密码错误！');
                $locationUrl = '';
            }
            echo '<script>alert("'.$message.'");'.$locationUrl.'</script>';
        }

        return $array;
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