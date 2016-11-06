<?php
namespace Email\Common\Service;

/**
 * 邮件发送服务
 */
class SendMail
{
    private $emailConfig = array();
    private $shopEmail;
    private $setMessage;

    /**
     * 邮件发送
     * @param array $data
     */
    public function toSendMail(array $data)
    {
        //获取email的配置信息
        if(empty($this->emailConfig)) {
            $readerConfig        = new \Zend\Config\Reader\Ini();
            $this->emailConfig   = $readerConfig->fromFile(__DIR__ . '/../../../config/ini/config.ini');
        }
        //定义email的发送方式，smtp或者sendmail
        if(empty($this->shopEmail)) {
            if($this->emailConfig['shop_email']['sendmail_type'] == 'smtp') {
                $options = array();
                $options['name'] = 'dbshop.net';
                $options['host'] = $this->emailConfig['shop_email']['smtp_location'];
                $options['port'] = $this->emailConfig['shop_email']['smtp_port'];
                
                if ($this->emailConfig['shop_email']['email_check'] == 1)          $options['connection_class']         = 'login';
                if ($this->emailConfig['shop_email']['email_secure_conn'] != null) $options['connection_config']['ssl'] = $this->emailConfig['shop_email']['email_secure_conn'];
                
                $options['connection_config']['username'] = $this->emailConfig['shop_email']['smtp_name'];
                $options['connection_config']['password'] = $this->emailConfig['shop_email']['smtp_passwd'];
                
                $this->shopEmail = new \Zend\Mail\Transport\Smtp(new \Zend\Mail\Transport\SmtpOptions($options));
                
            } elseif ($this->emailConfig['shop_email']['sendmail_type'] == 'sendmail') {
                $this->shopEmail = new \Zend\Mail\Transport\Sendmail();
            }
        }
        
        $bodyPart = new \Zend\Mime\Message();
        $htmlPart = new \Zend\Mime\Part($data['body']);
        $htmlPart->type = 'text/html';
        $textPart = new \Zend\Mime\Part($data['body']);
        $textPart->type = 'text/plain';
        $bodyPart->setParts(array($htmlPart, $textPart));
        
        //定义email信息发送设置
        $this->setMessage = new \Zend\Mail\Message();
        $this->setMessage->setEncoding('UTF-8');
        $this->setMessage
        ->addTo($data['send_mail'],$data['send_user_name'])
        ->addFrom($this->emailConfig['shop_email']['send_from_mail'], $this->emailConfig['shop_email']['send_name'])
        ->setSubject($data['subject'])
        ->setBody($bodyPart);
        
        $this->shopEmail->send($this->setMessage);
    }
}

?>