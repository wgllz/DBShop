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

class SendPhoneSms
{
    private $smsConfig = array();

    public function __construct()
    {
        //获取email的配置信息
        if(empty($this->smsConfig)) {
            $readerConfig        = new \Zend\Config\Reader\Ini();
            $this->smsConfig   = $readerConfig->fromFile(DBSHOP_PATH . '/data/moduledata/System/phonesms.ini');
        }

    }

    /**
     * 阿里大于发送手机短信
     * @param $data
     * @param $user_phone
     * @param string $phone_template
     * @param string $user_id
     * @return bool
     */
    public function toSendSms($data, $user_phone, $phone_template='', $user_id = '') {
        //判断是否开启了手机短信服务功能，如果未开启则不进行操作
        if($this->smsConfig['shop_phone_sms']['phone_sms_type'] == '' or $this->smsConfig['shop_phone_sms'][$phone_template] == '') return false;

        $user_phone = !empty($user_phone) ? (is_array($user_phone) ? implode(',', $user_phone) : $user_phone) : '';
        if(!empty($this->smsConfig['shop_phone_sms']['admin_phone'])) {
            if($phone_template == 'alidayu_submit_order_template_id' && $this->smsConfig['shop_phone_sms']['admin_submit_order_phone_message'] == 1)    $user_phone = !empty($user_phone) ? $user_phone.','.$this->smsConfig['shop_phone_sms']['admin_phone'] : $this->smsConfig['shop_phone_sms']['admin_phone'];
            if($phone_template == 'alidayu_payment_order_template_id' && $this->smsConfig['shop_phone_sms']['admin_payment_order_phone_message'] == 1)  $user_phone = !empty($user_phone) ? $user_phone.','.$this->smsConfig['shop_phone_sms']['admin_phone'] : $this->smsConfig['shop_phone_sms']['admin_phone'];
            if($phone_template == 'alidayu_finish_order_template_id' && $this->smsConfig['shop_phone_sms']['admin_finish_order_phone_message'] == 1)    $user_phone = !empty($user_phone) ? $user_phone.','.$this->smsConfig['shop_phone_sms']['admin_phone'] : $this->smsConfig['shop_phone_sms']['admin_phone'];
            if($phone_template == 'alidayu_cancel_order_template_id' && $this->smsConfig['shop_phone_sms']['admin_cancel_order_phone_message'] == 1)    $user_phone = !empty($user_phone) ? $user_phone.','.$this->smsConfig['shop_phone_sms']['admin_phone'] : $this->smsConfig['shop_phone_sms']['admin_phone'];
        }
        if(empty($user_phone)) return false;

        $smsJson    = $this->createSmsArray($data);
        //$user_phone = is_array($user_phone) ? implode(',', $user_phone) : $user_phone;

        include(DBSHOP_PATH . '/vendor/alibaba/dayu/TopSdk.php');
        $c = new \TopClient();
        $c->appkey    = $this->smsConfig['shop_phone_sms']['alidayu_app_key'];
        $c->secretKey = $this->smsConfig['shop_phone_sms']['alidayu_app_secret'];

        $req = new \AlibabaAliqinFcSmsNumSendRequest();
        $req->setExtend($user_id);
        $req->setSmsType('normal');
        $req->setSmsFreeSignName($this->smsConfig['shop_phone_sms']['alidayu_sign_name']);
        $req->setSmsParam($smsJson);
        $req->setRecNum($user_phone);
        $req->setSmsTemplateCode($this->smsConfig['shop_phone_sms'][$phone_template]);
        $resp = $c->execute($req);


    }

    /**
     * 对内容进行json处理
     * @param $data
     * @return string
     */
    private function createSmsArray($data) {
        $bodyArray  = array(
            'shopname'   => (isset($data['shopname'])     ? $data['shopname']     : ''),
            'buyname'    => (isset($data['buyname'])      ? $data['buyname']      : ''),
            'ordersn'    => (isset($data['ordersn'])      ? $data['ordersn']      : ''),
            'ordertotal' => (isset($data['ordertotal'])      ? $data['ordertotal']      : ''),
            'expressname'=> (isset($data['expressname'])      ? $data['expressname']      : ''),
            'expressnumber' => (isset($data['expressnumber'])      ? $data['expressnumber']      : ''),
        );
        return json_encode($bodyArray);
    }
}