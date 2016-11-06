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

namespace Email\Controller;

use Admin\Controller\BaseController;
use Zend\View\Model\ViewModel;

class EmailController extends BaseController
{
    public function indexAction()
    {
        $array = array();
        $array['group_array'] = $this->getDbshopTable('UserGroupExtendTable')->listUserGroupExtend();
        
        return $array;
    }
    /** 
     * ajax发送邮件
     */
    public function emailsendAction()
    {
        if($this->request->isPost()) {
            $emailArray = $this->request->getPost()->toArray();
            $userArray  = @explode(';', $emailArray['send_user']);
            
            if(is_array($userArray) and !empty($userArray)) {
                $array     = array();
                $array['subject']        = $emailArray['mail_subject'];
                $array['body']           = $emailArray['email_body'];
                $array['send_user_name'] = '';
                
                foreach ($userArray as $value) {
                    $array['send_mail'] = $value;
                    try {
                        $this->getServiceLocator()->get('shop_send_mail')->toSendMail($array);
                    } catch (\Exception $e) {
                        
                    }
                }
            }
            exit('true');
        }
        exit('false');
    }
    /**
     * 电邮配置信息
     * @return \Zend\View\Model\ViewModel
     */
    public function emailconfigAction ()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(TRUE);
        
        $array               = array();
        $readerConfig        = new \Zend\Config\Reader\Ini();
        $emailConfig         = $readerConfig->fromFile(DBSHOP_PATH . '/data/moduledata/Email/config.ini');
        
        $viewModel->setVariables($emailConfig);
        
        return $viewModel;
    }
    /** 
     * 收件人ajax获取
     */
    public function ajaxuserAction ()
    {
        $userType = trim($this->request->getPost('send_user_type'));
        if($userType == 'other') {
            exit('');
        }
        $userArray = $this->getDbshopTable('UserTable')->listUser($this->getDbshopLang()->getLocale(), array('group_id'=>$userType));
        $userHtml  = '';
        if(is_array($userArray) and !empty($userArray)) {
            foreach ($userArray as $value) {
                $userHtml .= $value['user_email'] . ';';
            }
            $userHtml = substr($userHtml, 0, -1);
        }
        exit($userHtml);
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = '')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}
