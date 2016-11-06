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
namespace Email\Common\Service;
/**
 * 邮件发送服务
 */
class SendMail
{
    private $emailConfig = array();
    private $setEmail;

    public function __construct()
    {
        //获取email的配置信息
        if(empty($this->emailConfig)) {
            $readerConfig        = new \Zend\Config\Reader\Ini();
            $this->emailConfig   = $readerConfig->fromFile(DBSHOP_PATH . '/data/moduledata/Email/config.ini');
        }
        
    }
/*==================================================邮件发送==================================================*/
    /**
     * 邮件发送
     * @param array $data
     * @return bool
     */
    public function toSendMail(array $data)
    {
        //判断是否开启了邮件服务功能，如果未开启则不进行操作
        if($this->emailConfig['shop_email']['email_service_state'] != 'true') return false;
        
        //定义第三方邮件发送组件，之所以不使用zend自带的，因为zend自带组件发送时会有一个文本附件
        if(empty($this->setEmail)) {
            require_once __DIR__ . '/../Plugin/PHPMailer/class.phpmailer.php';
            $this->setEmail = new \PHPMailer();
        }
        
        //定义email的发送方式，smtp或者sendmail
        if($this->emailConfig['shop_email']['sendmail_type'] == 'smtp') {
            $this->setEmail->IsSMTP();
            $this->setEmail->Host       = $this->emailConfig['shop_email']['smtp_location'];
            $this->setEmail->Port       = $this->emailConfig['shop_email']['smtp_port'];
            $this->setEmail->SMTPAuth   = ($this->emailConfig['shop_email']['email_check'] == 1 ? true : false);
            $this->setEmail->Username   = $this->emailConfig['shop_email']['smtp_name'];
            $this->setEmail->Password   = $this->emailConfig['shop_email']['smtp_passwd'];
            $this->setEmail->SMTPSecure = strtolower($this->emailConfig['shop_email']['email_secure_conn']);
        
        } elseif ($this->emailConfig['shop_email']['sendmail_type'] == 'sendmail') {
            $this->setEmail->IsMail();
        }
        
        $this->setEmail->From     = $this->emailConfig['shop_email']['send_from_mail'];
        $this->setEmail->FromName = $this->emailConfig['shop_email']['send_name'];
        $this->setEmail->Encoding = "base64";
        $this->setEmail->CharSet  = "utf-8";
        
        $this->setEmail->AddAddress($data['send_mail'], $data['send_user_name']);
        $this->setEmail->Subject  = $data['subject'];
        $this->setEmail->MsgHTML($data['body']);
        $this->setEmail->IsHTML();
        $this->setEmail->AltBody  = "text/html";
        if(!$this->setEmail->Send()) {
            return false;
        }
        //发送成功后，清除email地址
        $this->setEmail->ClearAddresses();

        return true;
    }
    
/*==================================================邮件发送==================================================*/
    /**
     * 提醒信息发送邮件
     * @param $data
     * @param $messageContent
     * @return bool
     */
    public function SendMesssageMail($data, $messageContent)
    {
        //判断是否开启了邮件服务功能，如果未开启则不进行操作
        if($this->emailConfig['shop_email']['email_service_state'] != 'true') return false;
        
        if(!isset($data['send_mail']) or empty($data['send_mail'])) return ;

        $sendMailArray = $data['send_mail'];
        foreach($sendMailArray as $mailValue) {
            if(strpos($mailValue, ',') !== false) {
                $valueArray = @explode(',', $mailValue);
                foreach($valueArray as $vValue) {
                    $data['send_mail'][] = $vValue;
                }
                break;
            }
        }

        $sendArray = array();
        $sendArray['send_user_name'] = '';
        $sendArray['subject']        = $data['subject'];
        $sendArray['body']           = $messageContent;

        //对于电子邮件使用html邮件模板
        /*$emailView     = new \Zend\View\Renderer\PhpRenderer();
        $eamilResolver = new \Zend\View\Resolver\TemplateMapResolver();
        $eamilResolver->setMap(array(
            'mailTemplate' => __DIR__ . '/../../../view/email/email/template.phtml'
        ));
        $emailView->setResolver($eamilResolver);
        $emailViewModel = new \Zend\View\Model\ViewModel();
        $emailViewModel->setTemplate('mailTemplate')->setVariables(array(
            'mailContent' => $sendArray['body']
        ));
        $sendArray['body'] = $emailView->render($emailViewModel);*/

        $userSendState = false;
        foreach ($data['send_mail'] as $mailKey => $mailAddress) {
            if($mailAddress != '') {
                $sendArray['send_mail'] = $mailAddress;
                if($mailKey == 0) {
                    if($this->toSendMail($sendArray)) $userSendState = true;
                } else {
                    $this->toSendMail($sendArray);
                }
                
            }
        }
        return $userSendState;
    }

}

?>