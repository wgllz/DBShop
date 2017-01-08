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

use Zend\Filter\HtmlEntities;
use Zend\View\Model\ViewModel;
use Admin\Controller\BaseController;

class UserController extends BaseController
{
    /**
     * 会员列表
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
        //会员列表
        $page = $this->params('page',1);
        $array['user_list'] = $this->getDbshopTable()->userPageList(array('page'=>$page, 'page_num'=>20), $this->getDbshopLang()->getLocale(),$searchArray);
        $array['page']      = $page;

        //会员组列表
        $array['group_array'] = $this->getDbshopTable('UserGroupExtendTable')->listUserGroupExtend();
        
        return $array;
    }
    /**
     * 会员添加
     */
    public function addAction ()
    {
        if($this->request->isPost()) {
            $userArray = $this->request->getPost()->toArray();
            $userArray['user_time']     = time();
            $userArray['user_password'] = $this->getServiceLocator()->get('frontHelper')->getPasswordStr($userArray['user_password']);
          
            $userId = $this->getDbshopTable()->addUser($userArray);

            //初始积分处理
            $userIntegralType = $this->getDbshopTable('UserIntegralTypeTable')->listUserIntegralType(array('e.language'=>$this->getDbshopLang()->getLocale()));
            if(is_array($userIntegralType) and !empty($userIntegralType)) {
                foreach($userIntegralType as $integralTypeValue) {
                    if($integralTypeValue['default_integral_num'] > 0) {
                        $integralLogArray = array();
                        $integralLogArray['user_id']           = $userId;
                        $integralLogArray['user_name']         = $userArray['user_name'];
                        $integralLogArray['integral_log_info'] = $this->getDbshopLang()->translate('会员注册默认起始积分数：') . $integralTypeValue['default_integral_num'];
                        $integralLogArray['integral_num_log']  = $integralTypeValue['default_integral_num'];
                        $integralLogArray['integral_log_time'] = time();
                        //默认消费积分
                        if($integralTypeValue['integral_type_mark'] == 'integral_type_1') {
                            $this->getDbshopTable('UserTable')->updateUserIntegralNum(array('integral_num_log'=>$integralTypeValue['default_integral_num']), array('user_id'=>$userId));

                            $integralLogArray['integral_type_id'] = 1;
                            $this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray);
                        }
                        //默认等级积分
                        if($integralTypeValue['integral_type_mark'] == 'integral_type_2') {
                            $this->getDbshopTable('UserTable')->updateUserIntegralNum(array('integral_num_log'=>$integralTypeValue['default_integral_num']), array('user_id'=>$userId), 2);

                            $integralLogArray['integral_type_id'] = 2;
                            $this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray);
                        }
                    }
                }
            }

            //上传头像，更新信息
            $userAvatar = $this->getServiceLocator()->get('shop_other_upload')->userAvatarUpload($userId, 'user_avatar', (isset($userArray['old_user_avatar']) ? $userArray['old_user_avatar'] : ''), $this->getServiceLocator()->get('adminHelper')->defaultShopSet('shop_user_avatar_width'), $this->getServiceLocator()->get('adminHelper')->defaultShopSet('shop_user_avatar_height'));
            $userAvatarImage = $userAvatar['image'];
            if(!empty($userAvatarImage)) {
                $this->getDbshopTable()->updateUser(array('user_avatar'=>$userAvatarImage),array('user_id'=>$userId));
            }
            
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理客户'), 'operlog_info'=>$this->getDbshopLang()->translate('添加客户') . '&nbsp;' . $userArray['user_name']));
            
            return $this->redirect()->toRoute('user/default',array('controller'=>'user'));
        }
        $array = array();
        $array['group_array'] = $this->getDbshopTable('UserGroupExtendTable')->listUserGroupExtend();
        
        return $array;
    }
    /**
     * 会员编辑
     */
    public function editAction ()
    {
        $userId = (int) $this->params('user_id',0);
        if(!$userId) {
            return $this->redirect()->toRoute('user/default',array('controller'=>'user'));
        }
        $array = array();

        //用于返回对应的分页数
        $page = $this->params('page',1);
        $array['page']     = $page;

        $array['query']    = $this->request->getQuery()->toArray();

        if($this->request->isPost()) {
            $userArray = $this->request->getPost()->toArray();
            
            //会员头像上传
            $userAvatar = $this->getServiceLocator()->get('shop_other_upload')->userAvatarUpload($userId, 'user_avatar', (isset($userArray['old_user_avatar']) ? $userArray['old_user_avatar'] : ''), $this->getServiceLocator()->get('adminHelper')->defaultShopSet('shop_user_avatar_width'), $this->getServiceLocator()->get('adminHelper')->defaultShopSet('shop_user_avatar_height'));
            $userArray['user_avatar'] = $userAvatar['image'];
            
            if(isset($userArray['user_password']) and !empty($userArray['user_password']) and isset($userArray['user_password_con']) and !empty($userArray['user_password_con']) and $userArray['user_password'] == $userArray['user_password_con']) {
                $userArray['user_password'] = $this->getServiceLocator()->get('frontHelper')->getPasswordStr($userArray['user_password']);
                //判断是否需要发送邮件告知
                if($userArray['password_email_notice'] == 1) {
                    try {
                        $sendState = $this->getServiceLocator()->get('shop_send_mail')->toSendMail(
                                array(
                                        'send_mail'      => $userArray['user_email'],
                                        'send_user_name' => (isset($userArray['user_name']) ? $userArray['user_name'] : ''),
                                        'subject'        => $this->getDbshopLang()->translate('修改密码通知'),
                                        'body'           => sprintf($this->getDbshopLang()->translate('您在%s修改了新密码为：'), $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name')) . $this->request->getPost('user_password')
                                )
                        );
                        $sendState = ($sendState ? 1 : 2);
                    } catch (\Exception $e) {
                        $sendState = 2;
                    }
                    //邮件发送历史记录
                    $sendLog = array(
                        'mail_subject' => $this->getDbshopLang()->translate('修改密码通知'),
                        'mail_body'    => sprintf($this->getDbshopLang()->translate('您在%s修改了新密码为：'), $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name')) . $this->request->getPost('user_password'),
                        'send_time'    => time(),
                        'user_id'      => $userId,
                        'send_state'   => $sendState
                    );
                    $this->getDbshopTable('UserMailLogTable')->addUserMailLog($sendLog);
                    
                }
            }
            //手工审核通过后，给通过人发送电子邮件
            if(isset($userArray['hidden_user_state']) and $userArray['hidden_user_state'] == 3 and $userArray['user_state'] == 1) {
                try {
                    $shopUrl = '<a href="'. $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost() . $this->url()->fromRoute('shopfront/default').'" target="_blank">'.$this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name').'</a>';
                    $sendState = $this->getServiceLocator()->get('shop_send_mail')->toSendMail(
                        array(
                            'send_mail'      => $userArray['user_email'],
                            'send_user_name' => (isset($userArray['user_name']) ? $userArray['user_name'] : ''),
                            'subject'        => $this->getDbshopLang()->translate('会员审核通知'),
                            'body'           => sprintf($this->getDbshopLang()->translate('您在%s注册的会员 '), $shopUrl) . $userArray['user_name'] . $this->getDbshopLang()->translate(' 已经审核通过。')
                        )
                    );
                    $sendState = ($sendState ? 1 : 2);
                } catch (\Exception $e) {
                    $sendState = 2;
                }
                //邮件发送历史记录
                $sendLog = array(
                    'mail_subject' => $this->getDbshopLang()->translate('会员审核通知'),
                    'mail_body'    => sprintf($this->getDbshopLang()->translate('您在%s注册的会员 '), $shopUrl) . $userArray['user_name'] . $this->getDbshopLang()->translate(' 已经审核通过。'),
                    'send_time'    => time(),
                    'user_id'      => $userId,
                    'send_state'   => $sendState
                );
                $this->getDbshopTable('UserMailLogTable')->addUserMailLog($sendLog);
            }

            $this->getDbshopTable()->updateUser($userArray,array('user_id'=>$userId));
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理客户'), 'operlog_info'=>$this->getDbshopLang()->translate('更新客户') . '&nbsp;' . $userArray['user_name']));
            
            if($userArray['user_save_type'] == 'save_return_edit') {
                $array['success_msg'] = $this->getDbshopLang()->translate('客户信息编辑成功！');
            } else {
                return $this->redirect()->toRoute('user/default/page',array('action'=>'index', 'controller'=>'user', 'page'=>$array['page']), array('query'=>$array['query']));
            }
        }
        
        $array['user_info']   = $this->getDbshopTable()->infoUser(array('user_id'=>$userId));
        $array['group_array'] = $this->getDbshopTable('UserGroupExtendTable')->listUserGroupExtend();
        $array['region_array']= $this->getDbshopTable('RegionTable')->listRegion(array('dbshop_region.region_top_id=0'));
        
        return $array;
    }
    /**
     * 会员删除
     */
    public function delAction ()
    {
        $userId = intval($this->request->getPost('user_id'));
        if(!$userId) {
            echo 'false';
            exit();
        }
        //为了记录操作日志
        $userInfo = $this->getDbshopTable()->infoUser(array('user_id'=>$userId));
        
        $delState = $this->getDbshopTable()->delUser(array('user_id'=>$userId));
        if($delState) {
            //删除会员头像
            if(!empty($userInfo->user_avatar)) @unlink(DBSHOP_PATH . $userInfo->user_avatar);
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理客户'), 'operlog_info'=>$this->getDbshopLang()->translate('删除客户') . '&nbsp;' . $userInfo->user_name));
            
            echo 'true';
        } else {
            echo 'flase';
        }
        exit();
    }
    /**
     * 批量编辑
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function editallAction ()
    {
        if($this->request->isPost()) {
            $allEdit = $this->request->getPost('allEdit');
            $userId  = $this->request->getPost('user_id');
            switch ($allEdit) {
                case 'del':
                    if(isset($userId) and !empty($userId)) $this->getDbshopTable()->delUser(array('user_id IN (' . @implode(',', $userId) . ')'));
                    break;
                case 'editState':
                    break;
            }
        }
        return $this->redirect()->toRoute('user/default',array('controller'=>'user'));
    }
    /**
     * 收货地址列表
     * @return \Zend\View\Model\ViewModel
     */
    public function addressAction ()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(TRUE);
        
        $userId    = (int) $this->params('user_id',0);
        $userArray = $this->getDbshopTable('UserAddressTable')->listAddress(array('user_id'=>$userId));
        $viewModel->setVariable('addressArray', $userArray);
        
        return $viewModel;
    }
    /**
     * 收货地址添加保存
     */
    public function saveaddressAction ()
    {
        $addState = 'false';
        if($this->request->isPost()) {
            $addressId    = (int) $this->request->getPost('address_id');
            $addressArray = $this->request->getPost()->toArray();
            $addressArray['user_id'] = $addressArray['address_user_id'];
            if($addressId == 0) {
                $this->getDbshopTable('UserAddressTable')->addAddress($addressArray);
            } else {
                $this->getDbshopTable('UserAddressTable')->updateAddress($addressArray,array('address_id'=>$addressId));
            }
            
            $addState = 'true';
        }
        exit($addState);
    }
    /**
     * 收货地址信息
     */
    public function infoaddressAction ()
    {
        $addressId   = (int) $this->request->getPost('address_id');
        $addressInfo = $this->getDbshopTable('UserAddressTable')->infoAddress(array('address_id'=>$addressId));
        if(!empty($addressInfo)) {
            $filter = new HtmlEntities();
            $addressInfo['region_value']    = $filter->filter($addressInfo['region_value']);//对输出的省份名称进行转义处理

            $addressInfo['default_value']   = $addressInfo['addr_default'];//从新赋值，html页面js对default在低版本浏览器会报js错误
            echo json_encode($addressInfo);
        }
        exit();
    }
    /**
     * 删除收货地址
     */
    public function deladdressAction ()
    {
        $addressId = (int) $this->request->getPost('address_id');
        $delState  = 'false';
        if($this->getDbshopTable('UserAddressTable')->delAddress(array('address_id'=>$addressId))) {
            $delState = 'true';
        }
        exit($delState);
    }
    /**
     * 会员邮件发送历史
     * @return \Zend\View\Model\ViewModel
     */
    public function maillogAction ()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(TRUE);
        
        $array        = array();
        $userId       = (int) $this->params('user_id',0);
        
        $page = $this->params('page',1);
        $array['user_mail_log_list'] = $this->getDbshopTable('UserMailLogTable')->listMailLog(array('page'=>$page, 'page_num'=>20), array('user_id'=>$userId));
        
        $array['user_id']            = $userId;
        $array['show_div_id']        = $this->request->getQuery('show_div_id');
        $viewModel->setVariables($array);
        
        return $viewModel;
    }
    /**
     * 删除邮件发送历史
     */
    public function delmaillogAction ()
    {
        $mailLogId = (int) $this->request->getPost('mail_log_id');
        $delState  = 'false';
        if($this->getDbshopTable('UserMailLogTable')->delMailLog(array('mail_log_id'=>$mailLogId))) {
            $delState = 'true';
        }
        exit($delState);
    }
    /** 
     * ajax收藏商品
     * @return \Zend\View\Model\ViewModel
     */
    public function ajaxfavoritesAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(TRUE);
        
        $array        = array();
        $userId       = (int) $this->params('user_id',0);
        //分页
        $page 		  = $this->params('page',1);
        $array['favorites_list'] = $this->getDbshopTable('UserFavoritesTable')->listFavorites(array('page'=>$page, 'page_num'=>20), array('user_id'=>$userId));
        
        $array['user_id']        = $userId;
        $array['show_div_id']    = $this->request->getQuery('show_div_id');
        $viewModel->setVariables($array);
        
        return $viewModel;
        
    }
    /**
     * 根据ip获得城市信息
     */
    public function getcityAction ()
    {
        $cityName = 'Unkown';
        $geoData  = __DIR__ . '/../plugins/GeoIP/data/GeoLiteCity.dat';
        /*new \GeoIP();
        $ip       = $_SERVER['REMOTE_ADDR'];
        $geoIp    = geoip_open($geoData, GEOIP_STANDARD);
        $record   = geoip_record_by_addr($geoIp, $ip);
        if(isset($record->city)) {
            $cityName = $record->city;
        }
        echo $cityName;*/
        exit;
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
}
