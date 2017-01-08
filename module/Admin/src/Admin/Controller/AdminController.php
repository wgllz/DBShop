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

use Zend\View\Model\ViewModel;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Authentication\AuthenticationService;
use Zend\Session\Container;
use Zend\Form\Element\Csrf;
use Admin\FormValidate\FormAdminValidate;

class AdminController extends BaseController
{
    /** 
     * 管理员登錄
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction ()
    {   
        $array = array();
        
        $auth = new AuthenticationService();
        if ($this->request->isPost()) {
            //获取登录信息
            $userName    = $this->request->getPost('user_name');
            $userPasswd  = $this->request->getPost('user_passwd');
            $captchaCode = $this->request->getPost('captcha_code');
            
            //服务器端数据校验
            $adminValidate = new FormAdminValidate($this->getDbshopLang());
            $adminValidate->checkAdminForm($this->request->getPost(), 'login');
            
            $captchaSession = new Container('captcha');
            if (strtolower($captchaCode) == strtolower($captchaSession->word)) {
                //登录验证
                $db = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
                $authAdapter = new AuthAdapter($db);
                $authAdapter->setTableName('dbshop_admin')->setIdentityColumn('admin_name')->setCredentialColumn('admin_passwd');
                $authAdapter->setIdentity($userName);
                $authAdapter->setCredential($this->getServiceLocator()->get('frontHelper')->getPasswordStr($userPasswd));
                $result = $authAdapter->authenticate();
                //信息验证通过，讲需要记录的信息进行记录
                if ($result->isValid()) {
                    //管理员组信息
                    $groupInfo = $this->getDbshopTable('AdminGroupTable')->infoAdminGroup(array('dbshop_admin_group.admin_group_id'=>$authAdapter->getResultRowObject()->admin_group_id, 'e.language'=>$this->getDbshopLang()->getLocale()));
                    //管理员信息session保存
                    $auth->getStorage()->write(
                        array(
                            'admin_id'           => $authAdapter->getResultRowObject()->admin_id,  
                            'admin_name'         => $authAdapter->getResultRowObject()->admin_name,  
                            'admin_group_name'   => $groupInfo->admin_group_name,
                            'admin_group_purview'=> unserialize($groupInfo->admin_group_purview)
                    ));
                    //记录操作日志
                    $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理员登录'), 'operlog_info'=>$this->getDbshopLang()->translate('管理员登录成功')));
                    
                    $this->redirect()->toRoute('adminHome');
                } else {
                	$array['admin_user']        = $userName;
                	$array['admin_login_state'] = 'false';
                }
            }
        } else {
            if($auth->getIdentity()) {
                $this->redirect()->toRoute('adminHome');
            }
        }
                
        //登录的csrf
        $csrf = new Csrf('admin_login_security');
        $csrf->setCsrfValidatorOptions(array('timeout'=>120, 'salt'=>'a3107e4d4ae0ea783cd1177c52f1e630'));
        $array['admin_login_csrf'] = $csrf->getAttributes();
        
        return $array;
    }
    /**
     * 管理员退出
     */
    public function loginoutAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);
        
        //记录操作日志
        $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理员退出'), 'operlog_info'=>$this->getDbshopLang()->translate('管理员退出成功')));
        
        $auth = new AuthenticationService();
        $auth->clearIdentity();
        
        $this->redirect()->toRoute('admin');
    }
    /**
     * 管理员列表
     */
    public function administratorAction ()
    {
        $array = array();
        $array['admin_list'] = $this->getDbshopTable()->listAdmin();

        return $array;
    }
    /**
     * 管理员添加
     * @return multitype:NULL
     */
    public function adminaddAction ()
    {
        $array = array();
        if($this->request->isPost()) {
            $adminArray = $this->request->getPost()->toArray();
            $adminArray['admin_add_time'] = time();
            $adminArray['admin_password'] = $this->getServiceLocator()->get('frontHelper')->getPasswordStr($adminArray['admin_password']);
            
            $this->getDbshopTable()->addAdmin($adminArray);
            
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理员设置'), 'operlog_info'=>$this->getDbshopLang()->translate('添加管理员') . '&nbsp;' . $adminArray['admin_name']));
            
            return $this->redirect()->toRoute('admin/default',array('controller'=>'admin','action'=>'administrator'));
        }
        $array['group_array'] = $this->getDbshopTable('AdminGroupTable')->listAdminGroup(array('e.language'=>$this->getDbshopLang()->getLocale()));
        
        return $array;
    }
    /**
     * 编辑管理员
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:NULL
     */
    public function admineditAction ()
    {
        $adminId = (int) $this->params('admin_id',0);
        if(!$adminId or ($adminId == 1 and $this->getServiceLocator()->get('adminHelper')->returnAuth('admin_id') != 1)) {//其他管理员无法编辑创始人信息
            return $this->redirect()->toRoute('admin/default',array('controller'=>'admin','action'=>'administrator'));
        }
        $array   = array();
        if($this->request->isPost()) {
            $adminArray = $this->request->getPost()->toArray();
            if(isset($adminArray['admin_password']) and !empty($adminArray['admin_password']) and isset($adminArray['admin_password_con']) and !empty($adminArray['admin_password_con']) and $adminArray['admin_password'] == $adminArray['admin_password_con']) {
                $adminArray['admin_password'] = $this->getServiceLocator()->get('frontHelper')->getPasswordStr($adminArray['admin_password']);
                //判断是否将新修的密码发送给该管理员邮箱中
                if(isset($adminArray['password_email_notice']) and $adminArray['password_email_notice'] == 1) {
                    try {
                        $this->getServiceLocator()->get('shop_send_mail')->toSendMail(
                                array(
                                        'send_mail'      => $adminArray['admin_email'],
                                        'send_user_name' => (isset($adminArray['admin_name']) ? $adminArray['admin_name'] : ''),
                                        'subject'        => $this->getDbshopLang()->translate('修改密码通知'),
                                        'body'           => sprintf($this->getDbshopLang()->translate('管理员%s您好，您在%s修改了新密码为'), $adminArray['admin_name'], $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name')) . '：' . $this->request->getPost('admin_password')
                                )
                        );
                    } catch (\Exception $e) {
                        
                    }
                }
            }
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理员设置'), 'operlog_info'=>$this->getDbshopLang()->translate('编辑管理员') . '&nbsp;' . $adminArray['admin_name']));
            
            $this->getDbshopTable()->updateAdmin($adminArray,array('admin_id'=>$adminId));
            
            $array['success_msg'] = $this->getDbshopLang()->translate('管理员信息编辑成功！');
        }
        
        $array['admin_info']  = $this->getDbshopTable()->infoAdmin(array('admin_id'=>$adminId));
        $array['group_array'] = $this->getDbshopTable('AdminGroupTable')->listAdminGroup(array('e.language'=>$this->getDbshopLang()->getLocale()));
        
        return $array;
    }
    /**
     * 删除管理员
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function admindelAction ()
    {
        $adminId = (int) $this->request->getPost('admin_id');
        if(!$adminId) {
            return $this->redirect()->toRoute('admin/default',array('controller'=>'admin','action'=>'administrator'));
        }
        if($adminId == $this->getServiceLocator()->get('adminHelper')->returnAuth('admin_id')) {//自己不能删除自己
            echo 'false';exit();
        }

        //为记录操作日志使用
        $adminInfo = $this->getDbshopTable()->infoAdmin(array('admin_id'=>$adminId));
        
        $delState  = $this->getDbshopTable()->delAdmin(array('admin_id'=>$adminId));
        if($delState) {
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理员设置'), 'operlog_info'=>$this->getDbshopLang()->translate('删除管理员') . '&nbsp;' . $adminInfo->admin_name));
            
            echo 'true';
        } else {
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理员设置'), 'operlog_info'=>$this->getDbshopLang()->translate('删除管理员失败') . '&nbsp;' . $adminInfo->admin_name));

            echo 'false';
        }
        exit();
    }
    /**
     * 检查管理员名称或邮箱是否重复
     */
    public function checkadminAction ()
    {
        $checkType = $this->params('check_type');
        $adminId   = (int) $this->params('admin_id',0);
        $adminInfo = '';
        if($checkType == 'admin_name') {
            $adminInfo = $this->getDbshopTable()->infoAdmin(array('admin_name'=>trim($this->request->getPost('admin_name')),'admin_id!='.$adminId));
            exit((($adminInfo and $adminInfo->admin_id != 0) ? 'false' : 'true'));
        }
        if($checkType == 'admin_email') {
            $adminInfo = $this->getDbshopTable()->infoAdmin(array('admin_email'=>trim($this->request->getPost('admin_email')),'admin_id!='.$adminId));
            exit((($adminInfo and $adminInfo->admin_id != 0) ? 'false' : 'true'));
        }
        exit();
    }
    /**
     * 管理员组列表
     */
    public function admingroupAction ()
    {
        $array = array();
        $array['group_array'] = $this->getDbshopTable('AdminGroupTable')->listAdminGroup(array('e.language'=>$this->getDbshopLang()->getLocale()));

        return $array;
    }
    /**
     * 管理员组添加
     */
    public function groupaddAction ()
    {
        if($this->request->isPost()) {
            $adminGroupArray = $this->request->getPost()->toArray();
            $adminGroupArray['admin_group_purview'] = (empty($adminGroupArray['purview']) ? '' : serialize($adminGroupArray['purview']));
            $adminGroupId    = $this->getDbshopTable('AdminGroupTable')->addAdminGroup($adminGroupArray);
            if($adminGroupId) {
                $adminGroupArray['admin_group_id'] = $adminGroupId;
                $adminGroupArray['language']       = $this->getDbshopLang()->getLocale();
                $this->getDbshopTable('AdminGroupExtendTable')->addAdminGroupExtend($adminGroupArray);
                
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理员设置'), 'operlog_info'=>$this->getDbshopLang()->translate('添加管理员组') . '&nbsp;' . $adminGroupArray['admin_group_name']));
            }
            return $this->redirect()->toRoute('admin/default',array('controller'=>'admin','action'=>'admingroup'));
        }
        
        $array = array();
        //权限设置
        $array['purview']       = include DBSHOP_PATH . '/module/Admin/data/purviewArray.php';
        $array['purview_lang']  = $this->groupPurviewLangauge();
        
        return $array;
    }
    /**
     * 管理员组编辑
     */
    public function editgroupAction ()
    {
        $adminGroupId = (int) $this->params('admin_group_id',0);
        
        if(!$adminGroupId) {
            return $this->redirect()->toRoute('admin/default',array('controller'=>'admin','action'=>'admingroup'));
        }
        if($this->request->isPost()) {
            $adminGroupArray = $this->request->getPost()->toArray();
            //更新管理组基本表，目前只更新权限
            if(!empty($adminGroupArray['purview'])) {
                $this->getDbshopTable('AdminGroupTable')->updateAdminGroup(array('admin_group_purview'=>serialize($adminGroupArray['purview'])), array('admin_group_id'=>$adminGroupId));
            }
            //更新管理组扩展表
            $this->getDbshopTable('AdminGroupExtendTable')->updateAdminGroupExtend($adminGroupArray,array('admin_group_id'=>$adminGroupId,'language'=>$this->getDbshopLang()->getLocale()));
            
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理员设置'), 'operlog_info'=>$this->getDbshopLang()->translate('编辑管理员组') . '&nbsp;' . $adminGroupArray['admin_group_name']));
            
            return $this->redirect()->toRoute('admin/default',array('controller'=>'admin','action'=>'admingroup'));
        }
        $array        = array();
        $array['admin_group_info'] = $this->getDbshopTable('AdminGroupTable')->infoAdminGroup(array('dbshop_admin_group.admin_group_id'=>$adminGroupId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        
        //权限设置
        $array['purview']         = include DBSHOP_PATH . '/module/Admin/data/purviewArray.php';
        $array['purview_lang']    = $this->groupPurviewLangauge();
        $array['checked_purview'] = ($array['admin_group_info']->admin_group_purview != '' ? @unserialize($array['admin_group_info']->admin_group_purview) : array());
        
        return $array;
    }
    /**
     * 管理员组删除
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function groupdelAction ()
    {
        $adminGroupId = (int) $this->request->getPost('admin_group_id');
        if(!$adminGroupId or $adminGroupId == 1) {
            return $this->redirect()->toRoute('admin/default',array('controller'=>'admin','action'=>'admingroup'));
        }
        //判断管理员组中是否有管理员
        $adminInfo = $this->getDbshopTable()->infoAdmin(array('dbshop_admin.admin_group_id'=>$adminGroupId));
        if($adminInfo) exit('false');
        
        //记录操作日志
        $adminGroupInfo = $this->getDbshopTable('AdminGroupTable')->infoAdminGroup(array('dbshop_admin_group.admin_group_id'=>$adminGroupId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理员设置'), 'operlog_info'=>$this->getDbshopLang()->translate('删除管理员组') . '&nbsp;' . $adminGroupInfo->admin_group_name));
        
        $this->getDbshopTable('AdminGroupTable')->delAdminGroup(array('admin_group_id'=>$adminGroupId));
        exit('true');
    }
    /** 
     * 
     */
    public function adminMessageAction()
    {
        
    }
    /** 
     * 无权限显示页面
     * @return multitype:
     */
    public function purviewErrorAction ()
    {
        $array = array();
        return $array;
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'AdminTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
    /**
     * 权限语言输出
     */
    private function groupPurviewLangauge()
    {
        $purviewLanguage = array(
                'System\Controller'     => $this->getDbshopLang()->translate('系统管理'),
                'System'         => $this->getDbshopLang()->translate('系统管理'),
                'Systemindex'           => $this->getDbshopLang()->translate('系统设置'),
                'Systemuploadset'       => $this->getDbshopLang()->translate('附件设置'),
                'Systemuserset'         => $this->getDbshopLang()->translate('客户设置'),
                'SystemsendMessageSet'  => $this->getDbshopLang()->translate('电子邮件提醒设置'),
                'SystemphoneMessageSet' => $this->getDbshopLang()->translate('手机短信提醒设置'),
                'Optimization'   => $this->getDbshopLang()->translate('性能优化'),
                'Optimizationindex'     => $this->getDbshopLang()->translate('性能优化设置'),
                'Online'         => $this->getDbshopLang()->translate('客服管理'),
                'Onlineindex'           => $this->getDbshopLang()->translate('在线客服查看'),
                'Onlineadd'             => $this->getDbshopLang()->translate('在线客服添加'),
                'Onlineedit'            => $this->getDbshopLang()->translate('在线客服编辑'),
                'Onlinedel'             => $this->getDbshopLang()->translate('在线客服删除'),
                'Onlinegroup'           => $this->getDbshopLang()->translate('在线客服组查看'),
                'Onlinegroupadd'        => $this->getDbshopLang()->translate('在线客服组添加'),
                'Onlinegroupedit'       => $this->getDbshopLang()->translate('在线客服组编辑'),
                'Onlinegroupdel'        => $this->getDbshopLang()->translate('在线客服组删除'),
                
                'Admin\Controller'      => $this->getDbshopLang()->translate('管理员管理'),
                'Admin'          => $this->getDbshopLang()->translate('管理员设置'),
                'Adminadministrator'    => $this->getDbshopLang()->translate('管理员查看'),
                'Adminadminadd'         => $this->getDbshopLang()->translate('管理员添加'),
                'Adminadminedit'        => $this->getDbshopLang()->translate('管理员编辑'),
                'Adminadmindel'         => $this->getDbshopLang()->translate('管理员删除'),
                'Adminadmingroup'       => $this->getDbshopLang()->translate('管理员组查看'),
                'Admingroupadd'         => $this->getDbshopLang()->translate('管理员组添加'),
                'Admineditgroup'        => $this->getDbshopLang()->translate('管理员组编辑'),
                'Admingroupdel'         => $this->getDbshopLang()->translate('管理员组删除'),
                
                'Template\Controller'   => $this->getDbshopLang()->translate('模板管理'),
                'Template'       => $this->getDbshopLang()->translate('模板管理'),
                'Templateindex'         => $this->getDbshopLang()->translate('模板设置'),

                'Package\Controller'   => $this->getDbshopLang()->translate('系统更新'),
                'Package'       => $this->getDbshopLang()->translate('系统更新'),
                'Packageindex'         => $this->getDbshopLang()->translate('更新列表查看'),
                'Packageonlineupdate'  => $this->getDbshopLang()->translate('在线更新操作'),
                
                'Currency\Controller'   => $this->getDbshopLang()->translate('货币设置'),
                'Currency'       => $this->getDbshopLang()->translate('货币设置'),
                'Currencyindex'         => $this->getDbshopLang()->translate('货币查看'),
                'Currencyadd'           => $this->getDbshopLang()->translate('货币添加'),
                'Currencyedit'          => $this->getDbshopLang()->translate('货币编辑'),
                'Currencydel'           => $this->getDbshopLang()->translate('货币删除'),
            
                'Payment\Controller'    => $this->getDbshopLang()->translate('支付设置'),
                'Payment'        => $this->getDbshopLang()->translate('支付设置'),
                'Paymentindex'          => $this->getDbshopLang()->translate('支付查看'),
                'Paymentpayment'        => $this->getDbshopLang()->translate('支付编辑'),
            
                'Express\Controller'    => $this->getDbshopLang()->translate('配送设置'),
                'Express'        => $this->getDbshopLang()->translate('配送设置'),
                'Expressindex'          => $this->getDbshopLang()->translate('配送查看'),
                'Expressadd'            => $this->getDbshopLang()->translate('配送添加'),
                'Expressedit'           => $this->getDbshopLang()->translate('配送编辑'),
                'Expressdel'            => $this->getDbshopLang()->translate('配送删除'),
                'Expressexpressapi'     => $this->getDbshopLang()->translate('动态API查看'),
                'Expressapiedit'        => $this->getDbshopLang()->translate('动态API编辑'),
            
                'Region\Controller'     => $this->getDbshopLang()->translate('地区管理'),
                'Region'         => $this->getDbshopLang()->translate('地区设置'),
                'Regionindex'           => $this->getDbshopLang()->translate('地区查看'),
                'Regionadd'             => $this->getDbshopLang()->translate('地区添加'),
                'Regionedit'            => $this->getDbshopLang()->translate('地区编辑'),
                'Regiondel'             => $this->getDbshopLang()->translate('地区删除'),
            
                'Stock\Controller'      => $this->getDbshopLang()->translate('状态设置'),
                'State'          => $this->getDbshopLang()->translate('库存状态'),
                'Stateindex'            => $this->getDbshopLang()->translate('库存状态查看'),
                'Stateadd'              => $this->getDbshopLang()->translate('库存状态添加'),
                'Stateedit'             => $this->getDbshopLang()->translate('库存状态编辑'),
                'Statedel'              => $this->getDbshopLang()->translate('库存状态删除'),
            
                'Navigation\Controller' => $this->getDbshopLang()->translate('导航设置'),
                'Navigation'     => $this->getDbshopLang()->translate('导航设置'),
                'Navigationindex'       => $this->getDbshopLang()->translate('导航查看'),
                'Navigationadd'         => $this->getDbshopLang()->translate('导航添加'),
                'Navigationedit'        => $this->getDbshopLang()->translate('导航编辑'),
                'Navigationdel'         => $this->getDbshopLang()->translate('导航删除'),
            
                'Links\Controller'      => $this->getDbshopLang()->translate('友情链接'),
                'Links'          => $this->getDbshopLang()->translate('友情链接'),
                'Linksindex'            => $this->getDbshopLang()->translate('友情链接查看'),
                'Linksadd'              => $this->getDbshopLang()->translate('友情链接添加'),
                'Linksedit'             => $this->getDbshopLang()->translate('友情链接编辑'),
                'Linksdel'              => $this->getDbshopLang()->translate('友情链接删除'),
            
                'Email\Controller'      => $this->getDbshopLang()->translate('邮件管理'),
                'Email'          => $this->getDbshopLang()->translate('邮件发送'),
                'Emailindex'            => $this->getDbshopLang()->translate('邮件发送页面'),
                'Emailemailsend'        => $this->getDbshopLang()->translate('邮件发送操作'),
            
                'Ad\Controller'         => $this->getDbshopLang()->translate('广告管理'),
                'Ad'             => $this->getDbshopLang()->translate('广告设置'),
                'Adindex'               => $this->getDbshopLang()->translate('广告类型查看'),
                'Adsetad'               => $this->getDbshopLang()->translate('广告查看'),
                'Adadd'                 => $this->getDbshopLang()->translate('广告添加'),
                'Adedit'                => $this->getDbshopLang()->translate('广告编辑'),
                'Addelad'               => $this->getDbshopLang()->translate('广告删除'),
                'AdmobileIndex'         => $this->getDbshopLang()->translate('广告类型查看(phone)'),
                'AdmobileSetad'         => $this->getDbshopLang()->translate('广告查看(phone)'),
                'AdmobileAdd'           => $this->getDbshopLang()->translate('广告添加(phone)'),
                'AdmobileEdit'          => $this->getDbshopLang()->translate('广告编辑(phone)'),
                'AdMobileDelad'         => $this->getDbshopLang()->translate('广告删除(phone)'),

                'Dbsql\Controller'      => $this->getDbshopLang()->translate('数据库备份还原'),
                'Dbsql'          => $this->getDbshopLang()->translate('数据库备份还原'),
                'Dbsqlindex'            => $this->getDbshopLang()->translate('数据库备份查看'),
                'Dbsqldelbackup'        => $this->getDbshopLang()->translate('数据库备份删除'),
                'Dbsqlbackup'           => $this->getDbshopLang()->translate('数据库备份操作'),
                'Dbsqlimportbackup'     => $this->getDbshopLang()->translate('数据库导入操作'),
            
                'Operlog\Controller'    => $this->getDbshopLang()->translate('操作日志'),
                'Operlog'        => $this->getDbshopLang()->translate('操作日志'),
                'Operlogindex'          => $this->getDbshopLang()->translate('操作日志查看'),
                'Operlogdel'            => $this->getDbshopLang()->translate('操作日志删除'),
            
                'Cms\Controller'        => $this->getDbshopLang()->translate('CMS管理'),
                'Cms'            => $this->getDbshopLang()->translate('文章管理'),
                'Cmsindex'              => $this->getDbshopLang()->translate('文章查看'),
                'Cmsadd'                => $this->getDbshopLang()->translate('文章添加'),
                'Cmsedit'               => $this->getDbshopLang()->translate('文章编辑'),
                'Cmsdel'                => $this->getDbshopLang()->translate('文章删除'),
                'CmssingleArticle'      => $this->getDbshopLang()->translate('单页文章查看'),
                'CmsaddSingleArticle'   => $this->getDbshopLang()->translate('单页文章添加'),
                'CmseditSingleArticle'  => $this->getDbshopLang()->translate('单页文章编辑'),
                'CmssingleDel'          => $this->getDbshopLang()->translate('单页文章删除'),
                'Class'          => $this->getDbshopLang()->translate('分类管理'),
                'Classindex'            => $this->getDbshopLang()->translate('分类查看'),
                'Classadd'              => $this->getDbshopLang()->translate('分类添加'),
                'Classedit'             => $this->getDbshopLang()->translate('分类编辑'),
                'Classdel'              => $this->getDbshopLang()->translate('分类删除'),
            
                'User\Controller'       => $this->getDbshopLang()->translate('客户管理'),
                'User'           => $this->getDbshopLang()->translate('客户管理'),
                'Userindex'             => $this->getDbshopLang()->translate('客户查看'),
                'Useradd'               => $this->getDbshopLang()->translate('客户添加'),
                'Useredit'              => $this->getDbshopLang()->translate('客户编辑'),
                'Userdel'               => $this->getDbshopLang()->translate('客户删除'),
                'Usereditall'           => $this->getDbshopLang()->translate('客户批量操作'),
                'Usergroup'      => $this->getDbshopLang()->translate('客户组管理'),
                'Usergroupindex'        => $this->getDbshopLang()->translate('客户组查看'),
                'Usergroupadd'          => $this->getDbshopLang()->translate('客户组添加'),
                'Usergroupedit'         => $this->getDbshopLang()->translate('客户组编辑'),
                'Usergroupdel'          => $this->getDbshopLang()->translate('客户组删除'),
                'Usermoney'      => $this->getDbshopLang()->translate('客户余额'),
                'Usermoneyindex'        => $this->getDbshopLang()->translate('余额记录查看'),
                'UsermoneyaddUserMoney' => $this->getDbshopLang()->translate('客户充值'),
                'Usermoneypaycheck'     => $this->getDbshopLang()->translate('充值记录查看'),
                'UsermoneypaycheckDel'  => $this->getDbshopLang()->translate('充值记录删除'),
                'Usermoneywithdraw'     => $this->getDbshopLang()->translate('提现记录查看'),
                'UsermoneywithdrawUdate'=> $this->getDbshopLang()->translate('审核提现记录'),
                'UsermoneywithdrawDel'  => $this->getDbshopLang()->translate('删除提现记录'),
                'Integral'       => $this->getDbshopLang()->translate('积分规则'),
                'IntegralintegralRule'   => $this->getDbshopLang()->translate('积分规则查看'),
                'IntegraladdIntegralRule'=> $this->getDbshopLang()->translate('积分规则添加'),
                'IntegraleditIntegralRule'=> $this->getDbshopLang()->translate('积分规则编辑'),
                'IntegraldelIntegralRule'=> $this->getDbshopLang()->translate('积分规则删除'),
                            
                'Goods\Controller'      => $this->getDbshopLang()->translate('商品管理'),
                'Goods'          => $this->getDbshopLang()->translate('商品管理'),
                'Goodsindex'            => $this->getDbshopLang()->translate('商品查看'),
                'Goodsadd'              => $this->getDbshopLang()->translate('商品添加'),
                'Goodsedit'             => $this->getDbshopLang()->translate('商品编辑'),
                'Goodsdel'              => $this->getDbshopLang()->translate('商品删除'),
                'Goodseditall'          => $this->getDbshopLang()->translate('商品批量处理'),
                'Class'          => $this->getDbshopLang()->translate('分类管理'),
                'Classindex'            => $this->getDbshopLang()->translate('分类查看'),
                'Classadd'              => $this->getDbshopLang()->translate('分类添加'),
                'Classedit'             => $this->getDbshopLang()->translate('分类编辑'),
                'Classdel'              => $this->getDbshopLang()->translate('分类删除'),
                'Brand'          => $this->getDbshopLang()->translate('品牌管理'),
                'Brandindex'            => $this->getDbshopLang()->translate('品牌查看'),
                'Brandadd'              => $this->getDbshopLang()->translate('品牌添加'),
                'Brandedit'             => $this->getDbshopLang()->translate('品牌编辑'),
                'Branddel'              => $this->getDbshopLang()->translate('品牌删除'),
                'Attribute'      => $this->getDbshopLang()->translate('属性管理'),
                'Attributeindex'        => $this->getDbshopLang()->translate('属性查看'),
                'Attributeadd'          => $this->getDbshopLang()->translate('属性添加'),
                'Attributeedit'         => $this->getDbshopLang()->translate('属性编辑'),
                'Attributedel'          => $this->getDbshopLang()->translate('属性删除'),
                'AttributeattributeValue'     => $this->getDbshopLang()->translate('属性值查看'),
                'AttributeaddAttributeValue'  => $this->getDbshopLang()->translate('属性值添加'),
                'AttributeeditAttributeValue' => $this->getDbshopLang()->translate('属性值编辑'),
                'AttributedelAttributeValue'  => $this->getDbshopLang()->translate('属性值删除'),
                'AttributeattributeGroup'     => $this->getDbshopLang()->translate('属性组查看'),
                'AttributeaddAttributeGroup'  => $this->getDbshopLang()->translate('属性值添加'),
                'AttributeeditAttributeGroup' => $this->getDbshopLang()->translate('属性值编辑'),
                'AttributedelAttributeGroup'  => $this->getDbshopLang()->translate('属性值删除'),
                'Comment'        => $this->getDbshopLang()->translate('评价管理'),
                'Commentindex'          => $this->getDbshopLang()->translate('评价查看'),
                'Commentedit'           => $this->getDbshopLang()->translate('评价编辑'),
                'Commentdel'            => $this->getDbshopLang()->translate('评价删除'),
        		'Ask'			 => $this->getDbshopLang()->translate('咨询管理'),
        		'Askindex'				=> $this->getDbshopLang()->translate('咨询查看'),
        		'Askdel'				=> $this->getDbshopLang()->translate('咨询删除'),
        		'Askreplycontent'		=> $this->getDbshopLang()->translate('咨询回复'),
        		'AskchangeShowState'	=> $this->getDbshopLang()->translate('显示状态修改'),
                'Tag'            => $this->getDbshopLang()->translate('标签管理'),
                'Tagindex'              => $this->getDbshopLang()->translate('标签查看'),
                'Tagadd'                => $this->getDbshopLang()->translate('标签添加'),
                'Tagedit'               => $this->getDbshopLang()->translate('标签编辑'),
                'Tagdel'                => $this->getDbshopLang()->translate('标签删除'),
                'TagtagGroup'           => $this->getDbshopLang()->translate('标签组查看'),
                'TagaddTagGroup'        => $this->getDbshopLang()->translate('标签组添加'),
                'TageditTagGroup'       => $this->getDbshopLang()->translate('标签组编辑'),
                'TagdelTagGroup'        => $this->getDbshopLang()->translate('标签组删除'),
                'Promotions'     => $this->getDbshopLang()->translate('优惠规则'),
                'Promotionsindex'       => $this->getDbshopLang()->translate('规则查看'),
                'Promotionsadd'         => $this->getDbshopLang()->translate('规则添加'),
                'Promotionsedit'        => $this->getDbshopLang()->translate('规则编辑'),
                'Promotionsdel'         => $this->getDbshopLang()->translate('规则删除'),
            
                'Orders\Controller'     => $this->getDbshopLang()->translate('销售管理'),
                'Orders'         => $this->getDbshopLang()->translate('订单管理'),
                'Ordersindex'           => $this->getDbshopLang()->translate('订单查看'),
                'Ordersedit'            => $this->getDbshopLang()->translate('订单编辑'),
                'Ordersorderprint'      => $this->getDbshopLang()->translate('订单打印'),
                'Orderspayoper'         => $this->getDbshopLang()->translate('订单支付'),
                'Ordersshipoper'        => $this->getDbshopLang()->translate('订单配送'),
                'Ordersfinishoper'      => $this->getDbshopLang()->translate('订单完成'),
                'Ordersrefund'          => $this->getDbshopLang()->translate('退货记录查看'),
                'OrdersoperRefund'      => $this->getDbshopLang()->translate('退货处理'),
                'OrdersshowRefund'      => $this->getDbshopLang()->translate('查看退货详情'),

                'OrdersaddExpressNumber'=> $this->getDbshopLang()->translate('单独添加快递单号'),
                'OrdersimportExpressNumber'=> $this->getDbshopLang()->translate('批量导入快递单号'),
                'OrdersexpressNumberList'=> $this->getDbshopLang()->translate('查看快递单号列表'),
                'OrdersexportShip'      => $this->getDbshopLang()->translate('导出发货单'),
                'Ordersmoreshipoper'    => $this->getDbshopLang()->translate('批量订单发货'),

            'Analytics\Controller'      => $this->getDbshopLang()->translate('统计分析'),
            'Analytics'         => $this->getDbshopLang()->translate('统计分析'),
            'AnalyticsuserStats'        => $this->getDbshopLang()->translate('客户统计'),
            'AnalyticsusersOrder'       => $this->getDbshopLang()->translate('客户排行'),
            'AnalyticsorderStats'       => $this->getDbshopLang()->translate('订单统计'),
            'AnalyticssaleStats'        => $this->getDbshopLang()->translate('销售概况'),
            'AnalyticssaleList'         => $this->getDbshopLang()->translate('销售明细'),
            'AnalyticssaleOrder'        => $this->getDbshopLang()->translate('销售排行'),
            'Analyticsindex'            => $this->getDbshopLang()->translate('流量概况'),
            'Analyticstrand'            => $this->getDbshopLang()->translate('趋势分析')
        );
    
        return $purviewLanguage;
    }
}
