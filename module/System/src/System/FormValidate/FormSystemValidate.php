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

namespace System\FormValidate;

use User\FormValidate\FormMessage;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

class FormSystemValidate
{
    private $inputFactory = null;
    private $inputFilter = null;
    private $dbshopLang;

    public function __construct($langTranslate)
    {
        $this->inputFactory = new InputFactory();
        $this->inputFilter = new InputFilter();

        $this->dbshopLang = $langTranslate;
    }

    /**
     * 检测调用
     * @param $data
     * @param $functionName
     */
    public function checkSystemForm($data, $functionName)
    {
        $functionName = $functionName . 'SystemValidate';
        $this->$functionName();
        $this->inputFilter->setData($data);

        if (!$this->inputFilter->isValid()) {
            $formMessage = new FormMessage($this->dbshopLang);
            $formMessage->fromMessageTemplate($this->inputFilter->getMessages());
        }
    }
    /**
     * 后台系统设置校验
     */
    private function shopSystemValidate()
    {
        $this->inputFilter->add($this->inputFactory->createInput(array(
            'name' => 'shop_name',
            'validators' => array(
                array('name' => 'notempty', 'options' => array('message' => $this->dbshopLang->translate('网站名称不能为空')))
            )
        )));
        $this->inputFilter->add($this->inputFactory->createInput(array(
            'name' => 'dbshop_timezone',
            'validators' => array(
                array('name' => 'notempty', 'options' => array('message' => $this->dbshopLang->translate('系统时区不能为空')))
            )
        )));
        $this->inputFilter->add($this->inputFactory->createInput(array(
            'name' => 'send_from_mail',
            'validators' => array(
                array('name' => 'notempty', 'options' => array('message' => $this->dbshopLang->translate('发信人邮件地址不能为空'))),
                array('name' => 'emailaddress', 'options' => array('message' => $this->dbshopLang->translate('请正确填写邮件地址'))),
            )
        )));
        $this->inputFilter->add($this->inputFactory->createInput(array(
            'name' => 'send_from_mail',
            'validators' => array(
                array('name' => 'notempty', 'options' => array('message' => $this->dbshopLang->translate('发信人邮件地址不能为空'))),
                array('name' => 'emailaddress', 'options' => array('message' => $this->dbshopLang->translate('请正确填写邮件地址'))),
            )
        )));
    }
    /**
     * 后台客户设置校验
     */
    private function userSystemValidate()
    {
        $this->inputFilter->add($this->inputFactory->createInput(array(
            'name' => 'default_group_id',
            'validators' => array(
                array('name' => 'notempty', 'options' => array('message' => $this->dbshopLang->translate('请选择客户默认组！')))
            )
        )));
    }
    /**
     * 后台消息设置校验
     */
    private function messageSystemValidate()
    {
        $this->inputFilter->add($this->inputFactory->createInput(array(
            'name' => 'admin_receive_email',
            'validators' => array(
                array('name' => 'notempty', 'options' => array('message' => $this->dbshopLang->translate('管理员接收邮件不能为空！'))),
                array('name' => 'emailaddress', 'options' => array('message' => $this->dbshopLang->translate('请正确填写邮件地址！'))),
            )
        )));
        $this->inputFilter->add($this->inputFactory->createInput(array(
            'name' => 'admin_receive_email2',
            'allow_empty' => 'admin_receive_email2',
            'validators' => array(
                array('name' => 'emailaddress', 'options' => array('message' => $this->dbshopLang->translate('请正确填写邮件地址！'))),
            )
        )));
        $this->inputFilter->add($this->inputFactory->createInput(array(
            'name' => 'admin_receive_email3',
            'allow_empty' => 'admin_receive_email3',
            'validators' => array(
                array('name' => 'emailaddress', 'options' => array('message' => $this->dbshopLang->translate('请正确填写邮件地址！'))),
            )
        )));
    }
}