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

namespace Admin\FormValidate;

use User\FormValidate\FormMessage;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

class FormAdminValidate
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
	public function checkAdminForm($data, $functionName)
	{
		$functionName = $functionName.'AdminValidate';
		$this->$functionName();
		$this->inputFilter->setData($data);
		 
		if(!$this->inputFilter->isValid()) {
			$formMessage = new FormMessage($this->dbshopLang);
			$formMessage->fromMessageTemplate($this->inputFilter->getMessages());
		}
	}
	private function loginAdminValidate()
	{
		$this->inputFilter->add($this->inputFactory->createInput(array(
				'name'       => 'user_name',
				'validators' => array(
						array('name' => 'notempty', 		'options' => array('message' => $this->dbshopLang->translate('请输入用户名！')))
				)
		)));
		$this->inputFilter->add($this->inputFactory->createInput(array(
				'name'       => 'user_passwd',
				'validators' => array(
						array('name' => 'notempty', 		'options' => array('message' => $this->dbshopLang->translate('请输入用户密码！')))
				)
		)));
		$this->inputFilter->add($this->inputFactory->createInput(array(
				'name'       => 'admin_login_security',
				'validators' => array(
						array('name' => 'notempty', 	'options' => array('message' => $this->dbshopLang->translate('登录时间超时，请重新登录'))),
						array('name' => 'csrf', 		'options' => array('name'=>'admin_login_security', 'salt'=>'a3107e4d4ae0ea783cd1177c52f1e630', 'message' => $this->dbshopLang->translate('登录时间超时，请重新登录')))
				)
		)));
	}
}

?>