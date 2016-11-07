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

use User\Service\OtherLogin\QqLogin;
use Zend\Mvc\Controller\AbstractActionController;
use User\FormValidate\FormUserValidate;
use Zend\Form\Element\Csrf;

class UserController extends AbstractActionController
{
    private $dbTables = array();
    private $translator;
    
    /**
     * The default action - show the home page
     */
    public function indexAction ()
    {
        return $this->redirect()->toRoute('shopfront/default');
    }
    /** 
     * 添加商品收藏
     */
    public function addFavoritesAction ()
    {
        $goodsId = intval($this->request->getPost('goods_id'));
        $classId = intval($this->request->getPost('class_id'));
        if($goodsId == 0 or $classId == 0) exit();
        if($this->getServiceLocator()->get('frontHelper')->getUserSession('user_id') == '') exit('login_false');
        
        $favoritesInfo = $this->getDbshopTable('UserFavoritesTable')->infoFavorites(array('goods_id'=>$goodsId, 'user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        if($favoritesInfo) exit('goods_exists');
        
        if($this->getDbshopTable('UserFavoritesTable')->addFavorites(array('class_id'=>$classId, 'goods_id'=>$goodsId, 'user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id'))))
            exit('true');
        
        exit('false');
    }
    /** 
     * 会员登录
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function loginAction ()
    {
        if($this->getServiceLocator()->get('frontHelper')->getUserSession('user_id') != '')
        return $this->redirect()->toRoute('shopfront/default');
        
        $this->layout()->title_name = $this->getDbshopLang()->translate('会员登录');
        
        $array = array();
        if($this->request->isPost()) {
            $userArray = $this->request->getPost()->toArray();
            $httpReferer = $userArray['http_referer'];
            unset($userArray['http_referer']);
            unset($userArray['captcha_code']);
            
            //服务器端数据校验
            $userValidate = new FormUserValidate($this->getDbshopLang());
            $userValidate->checkUserForm($this->request->getPost(), 'login');

            $userArray['user_password'] = $this->getServiceLocator()->get('frontHelper')->getPasswordStr($userArray['user_password']);
            $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_password'=>$userArray['user_password'], 'user_name'=>$userArray['user_name']));
            if($userInfo) {
                //当会员状态处于2（关闭）3（待审核）时，不进行登录操作
                $exitMessage = '';
                if($userInfo->user_state == 2) $exitMessage = $this->getDbshopLang()->translate('您的帐户处于关闭状态！')  . '&nbsp;<a href="' .$this->url()->fromRoute('shopfront/default'). '">' . $this->getDbshopLang()->translate('返回首页') . '</a>';
                if($userInfo->user_state == 3) $exitMessage = $this->getDbshopLang()->translate('您的帐户处于待审核状态！') . '&nbsp;<a href="' .$this->url()->fromRoute('shopfront/default'). '">' . $this->getDbshopLang()->translate('返回首页') . '</a>';
                if($exitMessage != '') exit($exitMessage);

                //根据等级积分判读，当前登录用户是否需要调整等级
                $groupId = $this->getDbshopTable('UserGroupTable')->checkUserGroup(array('group_id'=>$userInfo->group_id, 'integral_num'=>$userInfo->integral_type_2_num));
                if($groupId) {
                    $this->getDbshopTable('UserTable')->updateUser(array('group_id'=>$groupId), array('user_id'=>$userInfo->user_id));
                    $userInfo->group_id = $groupId;
                }

                $userGroup = $this->getDbshopTable('UserGroupExtendTable')->infoUserGroupExtend(array('group_id'=>$userInfo->group_id,'language'=>$this->getDbshopLang()->getLocale()));
                //session处理
                $sessionUser = array(
                        'user_name'      => $userInfo->user_name,
                        'user_id'        => $userInfo->user_id,
                        'user_email'     => $userInfo->user_email,
                        'user_phone'     => $userInfo->user_phone,
                        'group_id'       => $userInfo->group_id,
                        'user_group_name'=> $userGroup->group_name,
                        'user_avatar'    => (!empty($userInfo->user_avatar) ? $userInfo->user_avatar : $this->getServiceLocator()->get('frontHelper')->getUserIni('default_avatar'))
                );
                $this->getServiceLocator()->get('frontHelper')->setUserSession($sessionUser);
                //如果有返回网址，转向返回网址
                if($httpReferer != '') {
                    @header("Location: " . urldecode($httpReferer));
                    exit();
                }
                
                return $this->redirect()->toRoute('shopfront/default');
            } else {
                $array['message'] = $this->getDbshopLang()->translate('登录失败，用户名或密码错误，请重新登录！');
            }
        }

        $queryHttpReferer = '';
        if($this->request->isGet()) {
            $queryArray = $this->request->getQuery()->toArray();
            if(isset($queryArray['http_referer']) and !empty($queryArray['http_referer'])) $queryHttpReferer = $queryArray['http_referer'];
        }
        $array['http_referer'] = (isset($httpReferer) and $httpReferer != '') ? $httpReferer : (!empty($queryHttpReferer) ? $queryHttpReferer : urlencode($this->getRequest()->getServer('HTTP_REFERER')));
        //$array['http_referer'] = (isset($httpReferer) and $httpReferer != '') ? $httpReferer : $this->getRequest()->getServer('HTTP_REFERER');

        //登录的csrf
        $csrf = new Csrf('login_security');
        $csrf->setCsrfValidatorOptions(array('timeout'=>120, 'salt'=>'d56b699830e77ba53855679cb1d252da'));
		$array['login_csrf'] = $csrf->getAttributes();

        //统计使用
        $this->layout()->dbTongJiPage = 'user_login';
        
        return $array;
    }
    /** 
     * 会员注册
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function registerAction ()
    {
        if($this->getServiceLocator()->get('frontHelper')->getUserSession('user_id') != '')
        return $this->redirect()->toRoute('shopfront/default');

        $this->layout()->title_name = $this->getDbshopLang()->translate('会员注册');
        
        if($this->request->isPost()) {
            //判断是否关闭了注册功能
            if($this->getServiceLocator()->get('frontHelper')->getUserIni('user_register_state') == 'false') {
                exit($this->getServiceLocator()->get('frontHelper')->getUserIni('register_close_message'));    
            }
            
            //服务器端数据验证
            $userValidate = new FormUserValidate($this->getDbshopLang());
            $userValidate->checkUserForm($this->request->getPost(), 'register');
            
            //注册验证状态，null 无需验证，email电邮验证，audit人工验证
            $audit = $this->getServiceLocator()->get('frontHelper')->getUserIni('register_audit');
            //是否发送欢迎邮件
            $welcomeEmail = $this->getServiceLocator()->get('frontHelper')->getUserIni('welcomeemail');
            
            $userArray = $this->request->getPost()->toArray();
            $httpReferer = $userArray['http_referer'];
            
            $userArray['user_time']     = time();
            $userArray['group_id']      = $this->getServiceLocator()->get('frontHelper')->getUserIni('default_group_id');
            $userArray['user_state']    = (($audit == 'email' or $audit == 'audit') ? 3 : 1);//默认状态
            $userArray['user_password'] = $this->getServiceLocator()->get('frontHelper')->getPasswordStr($userArray['user_password']);
            
            $addState = $this->getDbshopTable('UserTable')->addUser($userArray);
            if($addState) {
                //初始积分处理
                $userIntegralType = $this->getDbshopTable('UserIntegralTypeTable')->listUserIntegralType(array('e.language'=>$this->getDbshopLang()->getLocale()));
                if(is_array($userIntegralType) and !empty($userIntegralType)) {
                    foreach($userIntegralType as $integralTypeValue) {
                        if($integralTypeValue['default_integral_num'] > 0) {
                            $integralLogArray = array();
                            $integralLogArray['user_id']           = $addState;
                            $integralLogArray['user_name']         = $userArray['user_name'];
                            $integralLogArray['integral_log_info'] = $this->getDbshopLang()->translate('会员注册默认起始积分数：') . $integralTypeValue['default_integral_num'];
                            $integralLogArray['integral_num_log']  = $integralTypeValue['default_integral_num'];
                            $integralLogArray['integral_log_time'] = time();
                            //默认消费积分
                            if($integralTypeValue['integral_type_mark'] == 'integral_type_1') {
                                $this->getDbshopTable('UserTable')->updateUserIntegralNum(array('integral_num_log'=>$integralTypeValue['default_integral_num']), array('user_id'=>$addState));

                                $integralLogArray['integral_type_id'] = 1;
                                $this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray);
                            }
                            //默认等级积分
                            if($integralTypeValue['integral_type_mark'] == 'integral_type_2') {
                                $this->getDbshopTable('UserTable')->updateUserIntegralNum(array('integral_num_log'=>$integralTypeValue['default_integral_num']), array('user_id'=>$addState), 2);

                                $integralLogArray['integral_type_id'] = 2;
                                $this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray);
                            }
                        }
                    }
                }

                $userGroup = $this->getDbshopTable('UserGroupExtendTable')->infoUserGroupExtend(array('group_id'=>$userArray['group_id'],'language'=>$this->getDbshopLang()->getLocale()));
                //判断是否对注册成功的会员发送欢迎电邮
                if($welcomeEmail == 'true') {
                    $sourceArray  = array(
                        '{username}',
                        '{shopname}',
                        '{adminemail}',
                        '{time}',
                        '{shopnameurl}'
                    );
                    $replaceArray = array(
                        $userArray['user_name'],
                        $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name'),
                        $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_email'),
                        date("Y-m-d H:i", time()),
                        '<a href="http://' . $this->getRequest()->getServer('SERVER_NAME') . $this->url()->fromRoute('shopfront/default') . '" target="_blank">' . $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name') . '</a>',
                    );
                    $registerEmail = array(
                        'send_user_name'=> $userArray['user_name'],
                        'send_mail'     => $userArray['user_email'],
                        'subject'       => str_replace($sourceArray, $replaceArray, $this->getServiceLocator()->get('frontHelper')->getUserIni('welcome_email_title')),
                        'body'          => nl2br(str_replace($sourceArray, $replaceArray, $this->getServiceLocator()->get('frontHelper')->getUserIni('welcome_email_body'))),
                    );
                    try {
                        $this->getServiceLocator()->get('shop_send_mail')->toSendMail($registerEmail);
                    } catch (\Exception $e) {
                    
                    }
                }
                //当验证为电邮验证或者人工验证时进行处理
                $exitMessage = '';
                if($audit == 'email') {
                    $userAuditCode = md5($userArray['user_name']) . md5(time());
                    $auditUrl      = 'http://' . $this->getRequest()->getServer('SERVER_NAME') . $this->url()->fromRoute('frontuser/default', array('action'=>'userAudit')) . '?userName=' . urlencode($userArray['user_name']) . '&auditCode=' . $userAuditCode;
                    //将生成的审核码更新到会员表中
                    $this->getDbshopTable('UserTable')->updateUser(array('user_audit_code'=>$userAuditCode),array('user_id'=>$addState));
                    $auditEmail = array(
                        'send_user_name'=> $userArray['user_name'],
                        'send_mail'     => $userArray['user_email'],
                        'subject'       => $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name') . $this->getDbshopLang()->translate('会员注册审核邮件'),
                        'body'          => $this->getDbshopLang()->translate('亲爱的') . $userArray['user_name'] . $this->getDbshopLang()->translate('您好，感谢您注册我们的会员，请点击会员审核链接进行认证审核 ') . '<a href="'.$auditUrl.'" target="_blank">'
                        . $this->getDbshopLang()->translate('点击审核会员 ') . '</a><br>' . $this->getDbshopLang()->translate('如果您无法点击审核链接，请复制下面的链接地址在浏览器中打开，完成审核 ') . '<br>' . $auditUrl
                    );
                    try {
                        $this->getServiceLocator()->get('shop_send_mail')->toSendMail($auditEmail);
                        $exitMessage = $this->getDbshopLang()->translate('您的帐户注册成功，需要验证邮件后方可使用，请登录您的邮箱进行验证！') . '&nbsp;<a href="' .$this->url()->fromRoute('shopfront/default'). '">' . $this->getDbshopLang()->translate('返回首页') . '</a>';
                    } catch (\Exception $e) {
                        exit($this->getDbshopLang()->translate('发送验证邮件失败，请联系网站管理员进行处理'));
                    }
                }
                if($audit == 'audit') $exitMessage = $this->getDbshopLang()->translate('您的帐户注册成功，需要人工审核后才可使用！')  . '&nbsp;<a href="' .$this->url()->fromRoute('shopfront/default'). '">' . $this->getDbshopLang()->translate('返回首页') . '</a>';
                if($exitMessage != '') exit($exitMessage);
                
                //注册成功session赋值，如果需要审核则不进行此操作
                $this->getServiceLocator()->get('frontHelper')->setUserSession(
                    array(
                        'user_name' =>$userArray['user_name'],
                        'user_id'   =>$addState,
                        'user_email'=>$userArray['user_email'],
                        'group_id'  =>$userArray['group_id'],
                        'user_group_name'=>$userGroup->group_name,
                        'user_avatar'=>$this->getServiceLocator()->get('frontHelper')->getUserIni('default_avatar')
                    )
                );
                
                //如果有返回网址，转向返回网址
                if($httpReferer != '') {
                    @header("Location: " . urldecode($httpReferer));
                    exit();
                }
                return $this->redirect()->toRoute('shopfront/default');
            }
        }
        
        $array = array();
        $array['http_referer'] = (isset($httpReferer) and $httpReferer != '') ? $httpReferer : urlencode($this->getRequest()->getServer('HTTP_REFERER'));
        
        //注册的csrf
        $csrf = new Csrf('register_security');
        $csrf->setCsrfValidatorOptions(array('timeout'=>120, 'salt'=>'9de4a97425678c5b1288aa70c1669a64'));
        $array['register_csrf'] = $csrf->getAttributes();
        
        //统计使用
        $this->layout()->dbTongJiPage = 'user_register';
        
        return $array;
    }
    /**
     * 找回密码
     */
    public function forgotpasswdAction ()
    {
        $array = array();
        
        if($this->request->isPost()) {
            $postArray = $this->request->getPost()->toArray();
            $userInfo  = $this->getDbshopTable('UserTable')->infoUser(array('user_name'=>$postArray['user_name'], 'user_email'=>$postArray['user_email']));
            if(isset($userInfo->user_name) and $userInfo->user_name != '') {
                //生成唯一码及url
                $editCode    = md5($userInfo->user_name . $userInfo->user_email) . md5(time());
                $editUrl     = 'http://' . $this->getRequest()->getServer('SERVER_NAME') . $this->url()->fromRoute('frontuser/default', array('action'=>'forgotpasswdedit')) . '?editcode=' . $editCode;
                //发送的邮件内容
                $forgotEmail = array(
                    'send_user_name'=> $userInfo->user_name,
                    'send_mail'     => $userInfo->user_email,
                    'subject'       => $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name') . $this->getDbshopLang()->translate('会员密码修改'),
                    'body'          => $this->getDbshopLang()->translate('亲爱的') . $userInfo->user_name . '<br>' . $this->getDbshopLang()->translate('您好，请点击下面的链接进行密码修改') . '<a href="'.$editUrl.'" target="_blank">'
                            . $this->getDbshopLang()->translate('点击修改密码 ') . '</a><br>' . $this->getDbshopLang()->translate('如果您无法点击修改链接，请复制下面的链接地址在浏览器中打开，完成密码修改 ') . '<br>' . $editUrl
                );
                
                try {
                    $this->getServiceLocator()->get('shop_send_mail')->toSendMail($forgotEmail);
                    $this->getDbshopTable('UserTable')->updateUser(array('user_forgot_passwd_code'=>$editCode),array('user_id'=>$userInfo->user_id));
                    $array['message'] = sprintf($this->getDbshopLang()->translate('已经向您的邮箱 %s 发送了一封邮件，请根据邮件内容完成新密码设定'), '<font color="red">' . $userInfo->user_email . '</font>');
                } catch (\Exception $e) {
                    $array['message'] = $this->getDbshopLang()->translate('无法向您的邮箱发送邮件，请联系管理员处理！');
                }
            } else {
                $array['message'] = $this->getDbshopLang()->translate('您输入的信息错误，没有匹配的会员信息！') . '&nbsp;' . $this->getDbshopLang()->translate('请重新输入') . '<a href="'.$this->url()->fromRoute('frontuser/default', array('action'=>'forgotpasswd')).'">' . $this->getDbshopLang()->translate('返回') . '</a>';
            }
        }
        
        return $array;
    }
    /**
     * 新密码设定
     */
    public function forgotpasswdeditAction ()
    {
        if($this->getServiceLocator()->get('frontHelper')->getUserSession('user_id') != '')
        return $this->redirect()->toRoute('fronthome/default');
        
        $array     = array();
        $editCode  = trim($_REQUEST['editcode']);
        
        $userInfo  = $this->getDbshopTable('UserTable')->infoUser(array('user_forgot_passwd_code'=>$editCode));
        if(isset($userInfo->user_name) and $userInfo->user_name != '') {
            if($this->request->isPost()) {//重新设定密码操作
                $postArray = $this->request->getPost()->toArray();
                if($postArray['user_password'] != $postArray['user_com_passwd']) {
                    $array['message'] = $this->getDbshopLang()->translate('两次输入的密码不相同！') . '<a href="'. $this->url()->fromRoute('frontuser/default', array('action'=>'forgotpasswdedit')) . '?editcode=' . $editCode .'">' . $this->getDbshopLang()->translate('返回') . '</a>';
                } else {
                    //设定新密码，并擦除找回密码唯一码
                    $this->getDbshopTable('UserTable')->updateUser(array('user_password'=>$this->getServiceLocator()->get('frontHelper')->getPasswordStr($postArray['user_password']), 'user_forgot_passwd_code'=>'no'),array('user_id'=>$userInfo->user_id));
                    $array['message'] = $this->getDbshopLang()->translate('您的新密码已经修改生效，请登录测试') . '<a href="'. $this->url()->fromRoute('frontuser/default', array('action'=>'login')) .'">' . $this->getDbshopLang()->translate('登录') . '</a>';
                }
            }
            
            $array['user_name'] = $userInfo->user_name;
        } else {
            $array['message'] = $this->getDbshopLang()->translate('该密码修改链接无效，请确认其正确性，如有疑问请联系管理员处理！');
        }
        
        return $array;
    }
    /** 
     * 会员登出
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function loginoutAction ()
    {
    	$userSession = new \Zend\Session\Container();
    	$userSession->getManager()->getStorage()->clear('user_info');
    	
        @header("Location: " . $this->getRequest()->getServer('HTTP_REFERER'));
        exit;
    }
    /**
     * 客户信息验证
     */
    public function checkAction ()
    {
        $checkType = $this->params('check_type');
        $userId    = (int) $this->params('user_id',0);
    
        $userInfo  = '';
        if($checkType == 'user_name') {
            $userName       = trim($this->request->getPost('user_name'));
            //对是否用户名被限制检查
            $retainState    = true;
            $registerRetain = $this->getServiceLocator()->get('frontHelper')->getUserIni('register_retain');
            if($registerRetain != '') {
                $registerRetain = explode('|', $registerRetain);
                if(in_array($userName, $registerRetain)) $retainState = false;
            }
            //检查是否存在
            $userInfo = $this->getDbshopTable()->infoUser(array('user_name'=>$userName,'user_id!='.$userId));
            
            exit(((($userInfo and $userInfo->user_id != 0) or !$retainState) ? 'false' : 'true'));
        }
        if($checkType == 'user_email') {
            $userInfo = $this->getDbshopTable()->infoUser(array('user_email'=>trim($this->request->getPost('user_email')),'user_id!='.$userId));
            exit((($userInfo and $userInfo->user_id != 0) ? 'false' : 'true'));
        }
        exit();
    }
    /**
     * 邮件审核时，需要的验证操作
     * @return multitype:NULL Ambigous <string, string, NULL, multitype:NULL , multitype:string NULL >
     */
    public function userAuditAction ()
    {
        if($this->getServiceLocator()->get('frontHelper')->getUserSession('user_id') != '')
        return $this->redirect()->toRoute('fronthome/default');
        
        $array     = array();
        $userName  = trim($_REQUEST['userName']);
        $auditCode = trim($_REQUEST['auditCode']);
        
        $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_name'=>$userName));
        if($userInfo and $userInfo->user_id != '' and $userInfo->user_audit_code == $auditCode) {
            $array['message'] = $this->getDbshopLang()->translate('您的帐户已经验证成功！可正常使用！');
            $this->getDbshopTable('UserTable')->updateUser(array('user_state'=>1, 'user_audit_code'=>'no'),array('user_id'=>$userInfo->user_id));
        } else {
            if($userInfo and $userInfo->user_state == 1) $array['message'] = $this->getDbshopLang()->translate('您的帐户已经验证成功！可以正常使用！');
            else $array['message'] = $this->getDbshopLang()->translate('您的帐户验证失败，请确认您的链接是否正确！');
        }
        return $array;
    }
    /**
     * 第三方登录跳转处理
     */
    public function otherloginAction()
    {
        $loginService     = $this->checkOtherLoginConfig();
        $loginService->toLogin();
    }
    /**
     * 第三方登录返回地址
     */
    public function othercallbackAction()
    {
        //进行第三方登录回调验证处理
        $loginService     = $this->checkOtherLoginConfig();
        $callBackState    = $loginService->callBack();

        //回调正确，检查该用户是否已经存在
        $userInfo         = $this->getDbshopTable('OtherLoginTable')->infoOtherLogin(array('dbshop_other_login.open_id'=>$loginService->getOpenId()));
        if($userInfo) {
            //当会员状态处于2（关闭）3（待审核）时，不进行登录操作
            $exitMessage = '';
            if($userInfo->user_state == 2) $exitMessage = $this->getDbshopLang()->translate('您的帐户处于关闭状态！')  . '&nbsp;<a href="' .$this->url()->fromRoute('shopfront/default'). '">' . $this->getDbshopLang()->translate('返回首页') . '</a>';
            if($userInfo->user_state == 3) $exitMessage = $this->getDbshopLang()->translate('您的帐户处于待审核状态！') . '&nbsp;<a href="' .$this->url()->fromRoute('shopfront/default'). '">' . $this->getDbshopLang()->translate('返回首页') . '</a>';
            if($exitMessage != '') exit($exitMessage);
            //获取会员组信息
            $userGroup = $this->getDbshopTable('UserGroupExtendTable')->infoUserGroupExtend(array('group_id'=>$userInfo->group_id,'language'=>$this->getDbshopLang()->getLocale()));
            //session处理，登录完成
            $sessionUser = array(
                'user_name'      => $userInfo->user_name,
                'user_id'        => $userInfo->user_id,
                'user_email'     => $userInfo->user_email,
                'group_id'       => $userInfo->group_id,
                'user_group_name'=> $userGroup->group_name,
                'user_avatar'    => (!empty($userInfo->user_avatar) ? $userInfo->user_avatar : $this->getServiceLocator()->get('frontHelper')->getUserIni('default_avatar'))
            );
            $this->getServiceLocator()->get('frontHelper')->setUserSession($sessionUser);

            $loginService->clearLoginSession();

            //判断是否为手机端访问
            if($this->getServiceLocator()->get('frontHelper')->isMobile()) return $this->redirect()->toRoute('mobile/default');
            //如果不是手机访问
            else  return $this->redirect()->toRoute('shopfront/default');
        }

        //如果该用户在数据库中不存在，则跳转到补充内容页面，完成最终注册
        //判断是否为手机端访问
        if($this->getServiceLocator()->get('frontHelper')->isMobile()) return $this->redirect()->toRoute('m_user/default', array('action'=>'otherregister'));
        else return $this->redirect()->toRoute('frontuser/default',array('action'=>'otherregister'));
    }
    /**
     * 第三方注册操作
     */
    public function otherregisterAction()
    {
        //验证从第三方回调获取的信息是否完整
        $loginService     = $this->checkOtherLoginConfig();
        $openId           = $loginService->getOpenId();
        $otherUserInfo    = $loginService->getOtherInfo();

        if($this->request->isPost()) {
            //判断是否关闭了注册功能
            if($this->getServiceLocator()->get('frontHelper')->getUserIni('user_register_state') == 'false') {
                exit($this->getServiceLocator()->get('frontHelper')->getUserIni('register_close_message'));
            }

            //服务器端数据验证
            $userValidate = new FormUserValidate($this->getDbshopLang());
            $userValidate->checkUserForm($this->request->getPost(), 'otherregister');

            //注册验证状态，null 无需验证，email电邮验证，audit人工验证
            $audit = $this->getServiceLocator()->get('frontHelper')->getUserIni('register_audit');
            //是否发送欢迎邮件
            $welcomeEmail = $this->getServiceLocator()->get('frontHelper')->getUserIni('welcomeemail');

            //开启数据库事务处理
            $this->getDbshopTable('dbshopTransaction')->DbshopTransactionBegin();
            //异常开启，如果产生异常，则执行事务回归操作
            try {
                //会员数据插入处理
                $userArray = $this->request->getPost()->toArray();
                $userArray['user_time']     = time();
                $userArray['group_id']      = $this->getServiceLocator()->get('frontHelper')->getUserIni('default_group_id');
                $userArray['user_state']    = (($audit == 'email' or $audit == 'audit') ? 3 : 1);//默认状态
                $userArray['user_password'] = $this->getServiceLocator()->get('frontHelper')->getPasswordStr($openId);
                $addState = $this->getDbshopTable('UserTable')->addUser($userArray);

                //初始积分处理
                $userIntegralType = $this->getDbshopTable('UserIntegralTypeTable')->listUserIntegralType(array('e.language'=>$this->getDbshopLang()->getLocale()));
                if(is_array($userIntegralType) and !empty($userIntegralType)) {
                    foreach($userIntegralType as $integralTypeValue) {
                        if($integralTypeValue['default_integral_num'] > 0) {
                            $integralLogArray = array();
                            $integralLogArray['user_id']           = $addState;
                            $integralLogArray['user_name']         = $userArray['user_name'];
                            $integralLogArray['integral_log_info'] = $this->getDbshopLang()->translate('会员注册默认起始积分数：') . $integralTypeValue['default_integral_num'];
                            $integralLogArray['integral_num_log']  = $integralTypeValue['default_integral_num'];
                            $integralLogArray['integral_log_time'] = time();
                            //默认消费积分
                            if($integralTypeValue['integral_type_mark'] == 'integral_type_1') {
                                $this->getDbshopTable('UserTable')->updateUserIntegralNum(array('integral_num_log'=>$integralTypeValue['default_integral_num']), array('user_id'=>$addState));

                                $integralLogArray['integral_type_id'] = 1;
                                $this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray);
                            }
                            //默认等级积分
                            if($integralTypeValue['integral_type_mark'] == 'integral_type_2') {
                                $this->getDbshopTable('UserTable')->updateUserIntegralNum(array('integral_num_log'=>$integralTypeValue['default_integral_num']), array('user_id'=>$addState), 2);

                                $integralLogArray['integral_type_id'] = 2;
                                $this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray);
                            }
                        }
                    }
                }

                $userGroup = $this->getDbshopTable('UserGroupExtendTable')->infoUserGroupExtend(array('group_id'=>$userArray['group_id'],'language'=>$this->getDbshopLang()->getLocale()));

                $otherLoginArray = array(
                    'user_id'       => $addState,
                    'open_id'       => $openId,
                    'ol_add_time'   => $userArray['user_time'],
                    'login_type'    => 'QQ'
                );
                $addOtherLogin = $this->getDbshopTable('OtherLoginTable')->addOtherLogin($otherLoginArray);

            } catch (\Exception $e) {
                $this->getDbshopTable('dbshopTransaction')->DbshopTransactionRollback();//事务回滚
            }
            $this->getDbshopTable('dbshopTransaction')->DbshopTransactionCommit();//事务确认

            if($addOtherLogin) {
                //判断是否对注册成功的会员发送欢迎电邮
                if($welcomeEmail == 'true') {
                    $sourceArray  = array(
                        '{username}',
                        '{shopname}',
                        '{adminemail}',
                        '{time}',
                        '{shopnameurl}'
                    );
                    $replaceArray = array(
                        $userArray['user_name'],
                        $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name'),
                        $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_email'),
                        date("Y-m-d H:i", time()),
                        '<a href="http://' . $this->getRequest()->getServer('SERVER_NAME') . $this->url()->fromRoute('shopfront/default') . '" target="_blank">' . $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name') . '</a>',
                    );
                    $registerEmail = array(
                        'send_user_name'=> $userArray['user_name'],
                        'send_mail'     => $userArray['user_email'],
                        'subject'       => str_replace($sourceArray, $replaceArray, $this->getServiceLocator()->get('frontHelper')->getUserIni('welcome_email_title')),
                        'body'          => nl2br(str_replace($sourceArray, $replaceArray, $this->getServiceLocator()->get('frontHelper')->getUserIni('welcome_email_body'))),
                    );
                    try {
                        $this->getServiceLocator()->get('shop_send_mail')->toSendMail($registerEmail);
                    } catch (\Exception $e) {

                    }
                }
                //当验证为电邮验证或者人工验证时进行处理
                $exitMessage = '';
                if($audit == 'email') {
                    $userAuditCode = md5($userArray['user_name']) . md5(time());
                    $auditUrl      = 'http://' . $this->getRequest()->getServer('SERVER_NAME') . $this->url()->fromRoute('frontuser/default', array('action'=>'userAudit')) . '?userName=' . urlencode($userArray['user_name']) . '&auditCode=' . $userAuditCode;
                    //将生成的审核码更新到会员表中
                    $this->getDbshopTable('UserTable')->updateUser(array('user_audit_code'=>$userAuditCode),array('user_id'=>$addState));
                    $auditEmail = array(
                        'send_user_name'=> $userArray['user_name'],
                        'send_mail'     => $userArray['user_email'],
                        'subject'       => $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name') . $this->getDbshopLang()->translate('会员注册审核邮件'),
                        'body'          => $this->getDbshopLang()->translate('亲爱的') . $userArray['user_name'] . $this->getDbshopLang()->translate('您好，感谢您注册我们的会员，请点击会员审核链接进行认证审核 ') . '<a href="'.$auditUrl.'" target="_blank">'
                            . $this->getDbshopLang()->translate('点击审核会员 ') . '</a><br>' . $this->getDbshopLang()->translate('如果您无法点击审核链接，请复制下面的链接地址在浏览器中打开，完成审核 ') . '<br>' . $auditUrl
                    );
                    try {
                        $this->getServiceLocator()->get('shop_send_mail')->toSendMail($auditEmail);
                        $exitMessage = $this->getDbshopLang()->translate('您的帐户注册成功，需要验证邮件后方可使用，请登录您的邮箱进行验证！') . '&nbsp;<a href="' .$this->url()->fromRoute('shopfront/default'). '">' . $this->getDbshopLang()->translate('返回首页') . '</a>';
                    } catch (\Exception $e) {
                        exit($this->getDbshopLang()->translate('发送验证邮件失败，请联系网站管理员进行处理'));
                    }
                }
                if($audit == 'audit') $exitMessage = $this->getDbshopLang()->translate('您的帐户注册成功，需要人工审核后才可使用！')  . '&nbsp;<a href="' .$this->url()->fromRoute('shopfront/default'). '">' . $this->getDbshopLang()->translate('返回首页') . '</a>';
                if($exitMessage != '') exit($exitMessage);

                //注册成功session赋值，如果需要审核则不进行此操作
                $this->getServiceLocator()->get('frontHelper')->setUserSession(
                    array(
                        'user_name'         =>$userArray['user_name'],
                        'user_id'           =>$addState,
                        'user_email'        =>$userArray['user_email'],
                        'group_id'          =>$userArray['group_id'],
                        'user_group_name'   =>$userGroup->group_name,
                        'user_avatar'       =>$this->getServiceLocator()->get('frontHelper')->getUserIni('default_avatar')
                    )
                );
                //清空第三方登录中设置过的session值
                $loginService->clearLoginSession();

                return $this->redirect()->toRoute('shopfront/default');
            }

        }

        $array = array('open_id'=>$openId, 'other_user_info'=>$otherUserInfo);

        return $array;
    }
    /**
     * 检查第三方登录配置
     * @return array|object
     */
    private function checkOtherLoginConfig()
    {
        $loginService     = $this->getServiceLocator()->get('QqLogin');
        $loginConfigState = $loginService->getLoginConfigState();
        if(is_string($loginConfigState) and $loginConfigState == 'configError') exit($this->getDbshopLang()->translate('该登录方式的配置信息错误，必须在公网上进行测试！'));

        $loginService->redirectUri = 'http://' . $this->getRequest()->getServer('SERVER_NAME') . $this->url()->fromRoute('frontuser/default',array('action'=>'othercallback'));

        return $loginService;
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'UserTable')
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
        if (!$this->translator) {
            $this->translator = $this->getServiceLocator()->get('translator');
        }
        return $this->translator;
    }
}
