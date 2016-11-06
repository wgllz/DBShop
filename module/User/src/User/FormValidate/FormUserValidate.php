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

namespace User\FormValidate;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

class FormUserValidate
{
    private $inputFactory = null;
    private $inputFilter  = null;
    private $dbshopLang;
    
    public function __construct($langTranslate)
    {
        $this->inputFactory = new InputFactory();
        $this->inputFilter  = new InputFilter();
        
        $this->dbshopLang   = $langTranslate;
    }
    /**
     * 检测调用
     * @param unknown $data
     * @param unknown $functionName
     */
    public function checkUserForm($data, $functionName)
    {
    	$functionName = $functionName.'UserValidate';
    	$this->$functionName();
    	$this->inputFilter->setData($data);
    	
    	if(!$this->inputFilter->isValid()) {
    		$formMessage = new FormMessage($this->dbshopLang);
    		$formMessage->fromMessageTemplate($this->inputFilter->getMessages());
    	}
    }
    /** 
     * 前台添加会员校验
     */
    private function registerUserValidate()
    {   
        $this->inputFilter->add($this->inputFactory->createInput(array(
            	'name'       => 'user_name',
        		/*'filters'  => array(
        				array('name' => 'StringTrim')
        		),*/
        		'validators' => array(
        				array('name' => 'notempty', 		'options' => array('message' => $this->dbshopLang->translate('请输入客户登录名称！')))
        		)
        )));
        $this->inputFilter->add($this->inputFactory->createInput(array(
        		'name'       => 'user_password',
        		'validators' => array(
        				array('name' => 'notempty', 		'options' => array('message' => $this->dbshopLang->translate('请输入密码！')))
        		)
        )));
        $this->inputFilter->add($this->inputFactory->createInput(array(
        		'name'       => 'user_com_passwd',
        		'validators' => array(
        				array('name' => 'notempty', 		'options' => array('message' => $this->dbshopLang->translate('请输入确认密码！'))),
        		        array('name' => 'identical', 		'options' => array('token' => 'user_password', 'message' => $this->dbshopLang->translate('两次输入的密码不一致！')))
        		)
        )));
        $this->inputFilter->add($this->inputFactory->createInput(array(
            	'name'       => 'user_email',
            	'validators' => array(
            			array('name' => 'notempty', 		'options' => array('message' => $this->dbshopLang->translate('请输入电子邮箱！'))),
                		array('name' => 'emailaddress',	    'options' => array('message' => $this->dbshopLang->translate('电子邮箱格式错误！'))),
            	)
        )));
        $this->inputFilter->add($this->inputFactory->createInput(array(
        		'name'       => 'register_security',
        		'validators' => array(
        				array('name' => 'notempty', 	'options' => array('message' => $this->dbshopLang->translate('注册时间超时，请重新注册'))),
        				array('name' => 'csrf', 		'options' => array('name'=>'register_security', 'salt'=>'9de4a97425678c5b1288aa70c1669a64', 'message' => $this->dbshopLang->translate('注册时间超时，请重新注册')))
        		)
        )));
    }
    /** 
     * 前台会员登录校验
     */
    private function loginUserValidate()
    {
    	$this->inputFilter->add($this->inputFactory->createInput(array(
    			'name'       => 'user_name',
    			'validators' => array(
    					array('name' => 'notempty', 		'options' => array('message' => $this->dbshopLang->translate('请输入客户登录名称！')))
    			)
    	)));
    	$this->inputFilter->add($this->inputFactory->createInput(array(
    			'name'       => 'user_password',
    			'validators' => array(
    					array('name' => 'notempty', 		'options' => array('message' => $this->dbshopLang->translate('请输入密码！')))
    			)
    	)));
    	$this->inputFilter->add($this->inputFactory->createInput(array(
    			'name'       => 'login_security',
    			'validators' => array(
    					array('name' => 'notempty', 	'options' => array('message' => $this->dbshopLang->translate('登录时间超时，请重新登录'))),
    					array('name' => 'csrf', 		'options' => array('name'=>'login_security', 'salt'=>'d56b699830e77ba53855679cb1d252da', 'message' => $this->dbshopLang->translate('登录时间超时，请重新登录')))
    			)
    	)));
    }
    /**
     * 第三方会员补充信息操作
     */
    private function otherregisterUserValidate()
    {
        $this->inputFilter->add($this->inputFactory->createInput(array(
            'name'       => 'user_name',
            'validators' => array(
                array('name' => 'notempty', 		'options' => array('message' => $this->dbshopLang->translate('请输入客户登录名称！')))
            )
        )));
        $this->inputFilter->add($this->inputFactory->createInput(array(
            'name'       => 'user_email',
            'validators' => array(
                array('name' => 'notempty', 		'options' => array('message' => $this->dbshopLang->translate('请输入电子邮箱！'))),
                array('name' => 'emailaddress',	    'options' => array('message' => $this->dbshopLang->translate('电子邮箱格式错误！'))),
            )
        )));
    }
    /**
     * 前台会员中心会员编辑
     */
    private function homeUserEditUserValidate()
    {
        $this->inputFilter->add($this->inputFactory->createInput(array(
            'name'       => 'user_email',
            'validators' => array(
                array('name' => 'notempty', 		'options' => array('message' => $this->dbshopLang->translate('请输入电子邮箱！'))),
                array('name' => 'emailaddress',	    'options' => array('message' => $this->dbshopLang->translate('电子邮箱格式错误！'))),
            )
        )));
    }
    /**
 * 前台会员中心会员密码校验
 */
    private function homeUserPasswdUserValidate()
    {
        $this->inputFilter->add($this->inputFactory->createInput(array(
            'name'       => 'old_user_password',
            'validators' => array(
                array('name' => 'notempty', 		'options' => array('message' => $this->dbshopLang->translate('请输入原始密码！')))
            )
        )));
        $this->inputFilter->add($this->inputFactory->createInput(array(
            'name'       => 'user_password',
            'validators' => array(
                array('name' => 'notempty', 		'options' => array('message' => $this->dbshopLang->translate('请输入密码！'))),
                array('name' => 'stringlength',     'options' => array('min' => 6, 'max' => 20, 'message' => $this->dbshopLang->translate('至少输入6位新密码，至多输入20位新密码！')))
            )
        )));
        $this->inputFilter->add($this->inputFactory->createInput(array(
            'name'       => 'user_password_con',
            'validators' => array(
                array('name' => 'notempty', 		'options' => array('message' => $this->dbshopLang->translate('请输入确认密码！'))),
                array('name' => 'identical', 		'options' => array('token' => 'user_password', 'message' => $this->dbshopLang->translate('两次输入的密码不一致！')))
            )
        )));
    }
    /**
     * 前台会员中心会员密码校验
     */
    private function homeOtherPasswdUserValidate()
    {
        $this->inputFilter->add($this->inputFactory->createInput(array(
            'name'       => 'user_password',
            'validators' => array(
                array('name' => 'notempty', 		'options' => array('message' => $this->dbshopLang->translate('请输入密码！'))),
                array('name' => 'stringlength',     'options' => array('min' => 6, 'max' => 20, 'message' => $this->dbshopLang->translate('至少输入6位新密码，至多输入20位新密码！')))
            )
        )));
        $this->inputFilter->add($this->inputFactory->createInput(array(
            'name'       => 'user_password_con',
            'validators' => array(
                array('name' => 'notempty', 		'options' => array('message' => $this->dbshopLang->translate('请输入确认密码！'))),
                array('name' => 'identical', 		'options' => array('token' => 'user_password', 'message' => $this->dbshopLang->translate('两次输入的密码不一致！')))
            )
        )));
    }
    /**
     * 前台会员中心提现校验
     */
    private function withdrawLogUserValidate()
    {
        $this->inputFilter->add($this->inputFactory->createInput(array(
            'name'       => 'money_change_num',
            'validators' => array(
                array('name' => 'notempty', 		'options' => array('message' => $this->dbshopLang->translate('提现金额不能为空！')))
            )
        )));
        $this->inputFilter->add($this->inputFactory->createInput(array(
            'name'       => 'bank_name',
            'validators' => array(
                array('name' => 'notempty', 		'options' => array('message' => $this->dbshopLang->translate('支付名称/开户银行 不能为空！')))
            )
        )));
        $this->inputFilter->add($this->inputFactory->createInput(array(
            'name'       => 'bank_account',
            'validators' => array(
                array('name' => 'notempty', 		'options' => array('message' => $this->dbshopLang->translate('账户名称/开户名称 不能为空！')))
            )
        )));
        $this->inputFilter->add($this->inputFactory->createInput(array(
            'name'       => 'bank_card_number',
            'validators' => array(
                array('name' => 'notempty', 		'options' => array('message' => $this->dbshopLang->translate('账号/卡号 不能为空！')))
            )
        )));
    }
}