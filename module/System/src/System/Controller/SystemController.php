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

namespace System\Controller;

use Admin\Controller\BaseController;
use System\FormValidate\FormSystemValidate;
use Zend\Config\Reader\Ini;

class SystemController extends BaseController
{
    /**
     * 系统设置
     */
    public function indexAction()
    {
        $array = array();
        if($this->request->isPost()) {
            //服务器端验证系统配置信息
            $systemValidate = new FormSystemValidate($this->getDbshopLang());
            $systemValidate->checkSystemForm($this->request->getPost(), 'shop');

            $configWriter   = new \Zend\Config\Writer\Ini();
            $systemArray    = $this->request->getPost()->toArray();
            try {
                $this->saveSystemConfig($systemArray, $configWriter);
                $this->saveEmailConfig($systemArray, $configWriter);
                $this->saveSystemContent($systemArray);
                $this->saveGoodsConfig($systemArray, $configWriter);
                $this->saveCaptchaConfig($systemArray, $configWriter);

                //时区设置
                $this->getServiceLocator()->get('adminHelper')->setDbshopSetshopFile(array('DBSHOP_TIMEZONE'=>$systemArray['dbshop_timezone']));
                
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('系统设置'), 'operlog_info'=>$this->getDbshopLang()->translate('更新系统设置')));
                
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
            //查看缓存是否开启，如果开启则进行缓存清除
            if(defined('FRONT_CACHE_STATE') and FRONT_CACHE_STATE == 'true') {
                $this->getServiceLocator()->get('frontCache')->flush();
            }
            //return $this->redirect()->toRoute('system/default',array('controller'=>'system'));
            $array['success_msg'] = $this->getDbshopLang()->translate('系统设置保存成功！');
        }
        
        $systemReader = new \Zend\Config\Reader\Ini();
        //系统配置信息
        $array['system_config'] = $systemReader->fromFile(DBSHOP_PATH . '/data/moduledata/System/config.ini');
        //商品配置信息
        $array['goods_config']  = $systemReader->fromFile(DBSHOP_PATH . '/data/moduledata/System/goods/goods.ini');
        //系统验证码设置(下面之所以使用判断，是为了让老用户进行平滑过渡，之前的配置信息时写入system的config的)
        $captchaConfigFile = DBSHOP_PATH . '/data/moduledata/User/CaptchaConfig.ini';
        if(file_exists($captchaConfigFile)) $array['captcha_config'] = $systemReader->fromFile($captchaConfigFile);
        else $array['captcha_config'] = $array['system_config']['shop_system'];
        //内容信息
        $array['buy_service_body']      = @file_get_contents(DBSHOP_PATH . '/data/moduledata/System/buy_service.ini');
        $array['buy_body']              = @file_get_contents(DBSHOP_PATH . '/data/moduledata/System/buy.ini');
        $array['goods_quality']         = @file_get_contents(DBSHOP_PATH . '/data/moduledata/System/goods_quality.ini');
        $array['shop_statistics_code']  = @file_get_contents(DBSHOP_PATH . '/data/moduledata/System/statistics.ini');

        //时区
        $timeZoneFile = 'zh_CN';
        if(file_exists(DBSHOP_PATH . '/data/moduledata/System/timezone/' . $this->getDbshopLang()->getLocale() . '.php') and $timeZoneFile != $this->getDbshopLang()->getLocale()) {
            $timeZoneFile = $this->getDbshopLang()->getLocale();
        }
        $array['selected_timezone'] = isset($systemArray['dbshop_timezone']) ? $systemArray['dbshop_timezone'] : DBSHOP_TIMEZONE;
        $array['time_zone_array']   = include DBSHOP_PATH . '/data/moduledata/System/timezone/' . $timeZoneFile . '.php';

        //系统统计中的站点信息
        $siteListArray= array();
        $siteListFile = DBSHOP_PATH . '/data/moduledata/Analytics/SiteArray.php';
        if(file_exists($siteListFile)) {
            $siteListArray = include $siteListFile;
        }
        $array['site_list'] = $siteListArray;

        return $array;
    }
    /**
     * 附件设置
     */
    public function uploadsetAction ()
    {
        $array = array();
        if($this->request->isPost()) {
            $configWriter = new \Zend\Config\Writer\Ini();
            $uploadArray = $this->request->getPost()->toArray();
            try {
                $this->saveGoodsUploadConfig($uploadArray, $configWriter);
                $this->saveWatermarkConfig($uploadArray, $configWriter);
                $this->saveUploadConfig($uploadArray, $configWriter);
                $this->saveStorageConfig($uploadArray, $configWriter);
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('附件设置'), 'operlog_info'=>$this->getDbshopLang()->translate('更新附件设置')));
                
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
            $array['success_msg'] = $this->getDbshopLang()->translate('附件设置保存成功！');
            //return $this->redirect()->toRoute('system/default',array('controller'=>'system', 'action'=>'uploadset'));
        }
        
        $configReader       = new \Zend\Config\Reader\Ini();
        $array['goods']     = $configReader->fromFile(DBSHOP_PATH . '/data/moduledata/Upload/Goods.ini');
        $array['watermark'] = $configReader->fromFile(DBSHOP_PATH . '/data/moduledata/Upload/Watermark.ini');
        $array['upload']    = $configReader->fromFile(DBSHOP_PATH . '/data/moduledata/Upload/Upload.ini');
        $array['storage']   = $configReader->fromFile(DBSHOP_PATH . '/data/moduledata/Upload/Storage.ini');
        
        return $array;
    }
    /** 
     * 客户设置
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:NULL
     */
    public function usersetAction ()
    {
        $array = array();
        if($this->request->isPost()) {
            //服务器端验证客户设置信息
            $systemValidate = new FormSystemValidate($this->getDbshopLang());
            $systemValidate->checkSystemForm($this->request->getPost(), 'user');

            $configWriter = new \Zend\Config\Writer\Ini();
            $usersetArray = $this->request->getPost()->toArray();
            try{
                $this->saveUserConfig($usersetArray, $configWriter);
                
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('客户设置'), 'operlog_info'=>$this->getDbshopLang()->translate('更新客户设置')));
                
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
            $array['success_msg'] = $this->getDbshopLang()->translate('客户设置保存成功！');
        }
        $array['group_array'] = $this->getDbshopTable('UserGroupExtendTable')->listUserGroupExtend();
        $configReader         = new \Zend\Config\Reader\Ini();
        $array['user_config'] = $configReader->fromFile(DBSHOP_PATH . '/data/moduledata/User/User.ini');

        $array['login_config']= $configReader->fromFile(DBSHOP_PATH . '/data/moduledata/User/OtherLogin.ini');

        $array['reg_or_login']= $configReader->fromFile(DBSHOP_PATH . '/data/moduledata/User/RegOrLogin.ini');

        return $array;
    }
    /**
     * 手机短信提醒设置
     * @return array
     */
    public function phoneMessageSetAction()
    {
        $array = array();

        if($this->request->isPost()) {
            $configWriter   = new \Zend\Config\Writer\Ini();
            $systemArray    = $this->request->getPost()->toArray();
            $this->savePhoneSmsConfig($systemArray, $configWriter);

            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('手机短信提醒设置'), 'operlog_info'=>$this->getDbshopLang()->translate('更新设置')));

            $array['success_msg'] = $this->getDbshopLang()->translate('手机短信提醒设置保存成功！');
        }
        $systemReader = new \Zend\Config\Reader\Ini();
        //短信通知设置
        $array['phonesms_config']  = $systemReader->fromFile(DBSHOP_PATH . '/data/moduledata/System/phonesms.ini');

        return $array;
    }
    /**
     * 邮件提醒设置
     * @return array
     */
    public function sendMessageSetAction ()
    {
        $array = array();
        
        $messageArray = array('submit_order', 'payment_finish', 'ship_finish', 'transaction_finish', 'cancel_order', 'goods_ask', 'goods_ask_reply');
        $toPath       = DBSHOP_PATH . '/data/moduledata/System/sendmessage/';
        if($this->request->isPost()) {
            //服务器端验证消息提醒设置
            $systemValidate = new FormSystemValidate($this->getDbshopLang());
            $systemValidate->checkSystemForm($this->request->getPost(), 'message');

            $messageSetArray = $this->request->getPost()->toArray();

            $adminReceiveEmail = isset($messageSetArray['admin_receive_email'])            ? $messageSetArray['admin_receive_email']            : '';
            if(!empty($messageSetArray['admin_receive_email2'])) $adminReceiveEmail .= !empty($adminReceiveEmail) ? ','.$messageSetArray['admin_receive_email2'] : $messageSetArray['admin_receive_email2'];
            if(!empty($messageSetArray['admin_receive_email3'])) $adminReceiveEmail .= !empty($adminReceiveEmail) ? ','.$messageSetArray['admin_receive_email3'] : $messageSetArray['admin_receive_email3'];

            $stateArray      = array();
            $stateArray['admin_receive_email']            = $adminReceiveEmail;
            $stateArray['submit_order_state']             = isset($messageSetArray['submit_order_state'])             ? $messageSetArray['submit_order_state']             : '';
            $stateArray['payment_finish_state']           = isset($messageSetArray['payment_finish_state'])           ? $messageSetArray['payment_finish_state']           : '';
            $stateArray['ship_finish_state']              = isset($messageSetArray['ship_finish_state'])              ? $messageSetArray['ship_finish_state']              : '';
            $stateArray['transaction_finish_state']       = isset($messageSetArray['transaction_finish_state'])       ? $messageSetArray['transaction_finish_state']       : '';
            $stateArray['cancel_order_state']             = isset($messageSetArray['cancel_order_state'])             ? $messageSetArray['cancel_order_state']             : '';
            $stateArray['buyer_submit_order_state']       = isset($messageSetArray['buyer_submit_order_state'])       ? $messageSetArray['buyer_submit_order_state']       : '';
            $stateArray['buyer_payment_finish_state']     = isset($messageSetArray['buyer_payment_finish_state'])     ? $messageSetArray['buyer_payment_finish_state']     : '';
            $stateArray['buyer_ship_finish_state']        = isset($messageSetArray['buyer_ship_finish_state'])        ? $messageSetArray['buyer_ship_finish_state']        : '';
            $stateArray['buyer_transaction_finish_state'] = isset($messageSetArray['buyer_transaction_finish_state']) ? $messageSetArray['buyer_transaction_finish_state'] : '';
            $stateArray['buyer_cancel_order_state']       = isset($messageSetArray['buyer_cancel_order_state'])       ? $messageSetArray['buyer_cancel_order_state']       : '';
            $stateArray['goods_ask_state']                = isset($messageSetArray['goods_ask_state'])                ? $messageSetArray['goods_ask_state']                : '';
            $stateArray['buyer_goods_ask_state']          = isset($messageSetArray['buyer_goods_ask_state'])          ? $messageSetArray['buyer_goods_ask_state']          : '';
            $stateArray['goods_ask_reply_state']          = isset($messageSetArray['goods_ask_reply_state'])          ? $messageSetArray['goods_ask_reply_state']          : '';
            $stateArray['buyer_goods_ask_reply_state']    = isset($messageSetArray['buyer_goods_ask_reply_state'])    ? $messageSetArray['buyer_goods_ask_reply_state']    : '';

            $configWriter = new \Zend\Config\Writer\Ini();
            $configWriter->toFile($toPath . 'sendmessage.ini', $stateArray);
            foreach ($messageArray as $mValue) {
                file_put_contents($toPath . $mValue . '.php', $messageSetArray[$mValue]);
            }

            $array['success_msg'] = $this->getDbshopLang()->translate('邮件提醒设置保存成功！');
        }
        
        $configReader = new \Zend\Config\Reader\Ini();
        $array['message_set'] = $configReader->fromFile($toPath . 'sendmessage.ini');
        //设置了多个管理员邮件接收
        $array['admin_receive_email_array'] = @explode(',', $array['message_set']['admin_receive_email']);
        //获取邮件服务设置信息，如果没有开启，提示用户去开启
        $array['email_config'] = $configReader->fromFile(DBSHOP_PATH . '/data/moduledata/Email/config.ini');

        foreach ($messageArray as $mValue) {
            $array[$mValue] = @file_get_contents($toPath . $mValue . '.php');
        }

        return $array;
    }
    /**
     * 系统配置信息保存
     * @param array $data
     * @param $e
     */
    private function saveSystemConfig(array $data,$e) {
        $systemConfig = array();
        $systemConfig['shop_name']        = isset($data['shop_name'])        ? trim($data['shop_name'])        : '';
        $systemConfig['shop_extend_name'] = isset($data['shop_extend_name']) ? trim($data['shop_extend_name']) : '';
        $systemConfig['shop_keywords']    = isset($data['shop_keywords'])    ? trim($data['shop_keywords'])    : '';
        $systemConfig['shop_hot_keywords']= isset($data['shop_hot_keywords'])? trim($data['shop_hot_keywords']): '';
        $systemConfig['shop_description'] = isset($data['shop_description']) ? trim($data['shop_description']) : '';
        $systemConfig['shop_QRcode']      = isset($data['shop_QRcode'])      ? $data['shop_QRcode']      : '';
        $systemConfig['shop_close']       = isset($data['shop_close'])       ? $data['shop_close']       : '';
        $systemConfig['shop_close_info']  = isset($data['shop_close_info'])  ? trim($data['shop_close_info'])  : '';
        $systemConfig['shop_icp']         = isset($data['shop_icp'])         ? $data['shop_icp']         : '';
        $systemConfig['shop_invoice']     = isset($data['shop_invoice'])     ? $data['shop_invoice']     : '';
        $systemConfig['shop_tongji_type'] = isset($data['shop_tongji_type']) ? $data['shop_tongji_type'] : '';
        $systemConfig['shop_tongji_baidu_user'] = isset($data['shop_tongji_baidu_user']) ? trim($data['shop_tongji_baidu_user']) : '';
        $systemConfig['shop_tongji_baidu_passwd']= isset($data['shop_tongji_baidu_passwd']) ? trim($data['shop_tongji_baidu_passwd']) : '';
        $systemConfig['shop_site_id']    = isset($data['shop_site_id'])      ? intval($data['shop_site_id']) : '';
        $systemConfig['shop_tongji_token']= isset($data['shop_tongji_token'])? trim($data['shop_tongji_token']): '';
        $systemConfig['shop_address']     = isset($data['shop_address'])     ? $data['shop_address']     : '';
        $systemConfig['shop_call']        = isset($data['shop_call'])        ? $data['shop_call']        : '';
        $systemConfig['shop_zip']         = isset($data['shop_zip'])         ? $data['shop_zip']         : '';
        //$systemConfig['shop_email']       = isset($data['shop_email'])       ? $data['shop_email']       : '';
        
        //系统logo上传处理
        
        $shopLogo = $this->getServiceLocator()->get('shop_system_upload')->systemWebLogoUpload('shop_logo', (isset($data['old_shop_logo']) ? $data['old_shop_logo'] : ''), $this->getServiceLocator()->get('adminHelper')->defaultShopSet('shop_logo_width'), $this->getServiceLocator()->get('adminHelper')->defaultShopSet('shop_logo_height'));
        $systemConfig['shop_logo'] = $shopLogo['image'];
        //系统ico上传处理
        $shopIco  = $this->getServiceLocator()->get('shop_system_upload')->systemWebIcoUpload('shop_ico', (isset($data['old_shop_ico']) ? $data['old_shop_ico'] : ''), $this->getServiceLocator()->get('adminHelper')->defaultShopSet('shop_ico_width'), $this->getServiceLocator()->get('adminHelper')->defaultShopSet('shop_ico_height'));
        $systemConfig['shop_ico'] = $shopIco['image'];

    
        $e->toFile(DBSHOP_PATH . '/data/moduledata/System/config.ini', array('shop_system'=>$systemConfig));
    }
    /**
     * 邮件配置信息保存
     * @param array $data
     * @param $e
     */
    private function saveEmailConfig(array $data,$e) {
        $emailConfig = array();
        $emailConfig['email_service_state'] = isset($data['email_service_state']) ? $data['email_service_state']  : '';
        $emailConfig['send_from_mail']      = isset($data['send_from_mail'])      ? $data['send_from_mail']       : '';
        $emailConfig['send_name']           = isset($data['send_name'])           ? $data['send_name']            : '';
        $emailConfig['sendmail_type']       = isset($data['sendmail_type'])       ? $data['sendmail_type']        : '';
        $emailConfig['smtp_location']       = isset($data['smtp_location'])       ? $data['smtp_location']        : '';
        $emailConfig['smtp_port']           = isset($data['smtp_port'])           ? $data['smtp_port']            : '';
        $emailConfig['smtp_name']           = isset($data['smtp_name'])           ? $data['smtp_name']            : '';
        $emailConfig['smtp_passwd']         = isset($data['smtp_passwd'])         ? $data['smtp_passwd']          : '';
        $emailConfig['email_check']         = isset($data['email_check'])         ? $data['email_check']          : '';
        $emailConfig['email_secure_conn']   = isset($data['email_secure_conn'])   ? $data['email_secure_conn']    : '';
    
        $e->toFile(DBSHOP_PATH . '/data/moduledata/Email/config.ini', array('shop_email'=>$emailConfig));
    }
    /**
     * 系统设置之内容设置
     * @param array $data
     */
    private function saveSystemContent(array $data)
    {
        if(isset($data['goods_quality']))        file_put_contents(DBSHOP_PATH . '/data/moduledata/System/goods_quality.ini', $data['goods_quality']);
        if(isset($data['buy_service']))          file_put_contents(DBSHOP_PATH . '/data/moduledata/System/buy_service.ini', $data['buy_service']);
        if(isset($data['buy']))                  file_put_contents(DBSHOP_PATH . '/data/moduledata/System/buy.ini', $data['buy']);
        if(isset($data['shop_statistics_code'])) file_put_contents(DBSHOP_PATH . '/data/moduledata/System/statistics.ini', $data['shop_statistics_code']);
    }
    /**
     * 系统设置之商品设置
     * @param array $data
     * @param $e
     */
    private function saveGoodsConfig(array $data, $e)
    {
        $configRead = new Ini();
        $goodsConfig = $configRead->fromFile(DBSHOP_PATH . '/data/moduledata/System/goods/goods.ini');

        $goodsConfig['shop_goods']['dbshop_goods_share']      = isset($data['dbshop_goods_share']) ? $data['dbshop_goods_share']  : '';
        $goodsConfig['shop_goods']['dbshop_goods_sn_prefix']  = isset($data['dbshop_goods_sn_prefix']) ? $data['dbshop_goods_sn_prefix']  : '';
        $goodsConfig['shop_goods']['dbshop_goods_QRcode']     = isset($data['dbshop_goods_QRcode']) ? $data['dbshop_goods_QRcode']  : '';

        $e->toFile(DBSHOP_PATH . '/data/moduledata/System/goods/goods.ini', $goodsConfig);
    }

    private function savePhoneSmsConfig(array $data, $e) {
        $phonesmsConfig = array();
        $phonesmsConfig['phone_sms_type']       = isset($data['phone_sms_type'])     ? $data['phone_sms_type']           : '';
        $phonesmsConfig['alidayu_sign_name']    = isset($data['alidayu_sign_name'])  ? trim($data['alidayu_sign_name'])    : '';
        $phonesmsConfig['alidayu_app_key']      = isset($data['alidayu_app_key'])    ? trim($data['alidayu_app_key'])    : '';
        $phonesmsConfig['alidayu_app_secret']   = isset($data['alidayu_app_secret']) ? trim($data['alidayu_app_secret']) : '';

        $phonesmsConfig['admin_phone']   = isset($data['admin_phone'])  ? trim($data['admin_phone']) : '';
        $phonesmsConfig['admin_submit_order_phone_message']   = isset($data['admin_submit_order_phone_message'])  ? intval($data['admin_submit_order_phone_message']) : '';
        $phonesmsConfig['admin_payment_order_phone_message']  = isset($data['admin_payment_order_phone_message']) ? intval($data['admin_payment_order_phone_message']) : '';
        $phonesmsConfig['admin_finish_order_phone_message']   = isset($data['admin_finish_order_phone_message'])  ? intval($data['admin_finish_order_phone_message']) : '';
        $phonesmsConfig['admin_cancel_order_phone_message']   = isset($data['admin_cancel_order_phone_message'])  ? intval($data['admin_cancel_order_phone_message']) : '';

        $phonesmsConfig['alidayu_submit_order_template_id']   = isset($data['alidayu_submit_order_template_id'])  ? trim($data['alidayu_submit_order_template_id']) : '';
        $phonesmsConfig['alidayu_payment_order_template_id']  = isset($data['alidayu_payment_order_template_id']) ? trim($data['alidayu_payment_order_template_id']) : '';
        $phonesmsConfig['alidayu_ship_order_template_id']     = isset($data['alidayu_ship_order_template_id'])    ? trim($data['alidayu_ship_order_template_id']) : '';
        $phonesmsConfig['alidayu_finish_order_template_id']   = isset($data['alidayu_finish_order_template_id'])  ? trim($data['alidayu_finish_order_template_id']) : '';
        $phonesmsConfig['alidayu_cancel_order_template_id']   = isset($data['alidayu_cancel_order_template_id'])  ? trim($data['alidayu_cancel_order_template_id']) : '';

        $phonesmsConfig['alidayu_phone_captcha_template_id']   = isset($data['alidayu_phone_captcha_template_id'])  ? trim($data['alidayu_phone_captcha_template_id']) : '';

        $e->toFile(DBSHOP_PATH . '/data/moduledata/System/phonesms.ini', array('shop_phone_sms'=>$phonesmsConfig));
    }
    /**
     * 附件设置之商品图片设置
     * @param array $uploadArray
     * @param $e
     */
    private function saveGoodsUploadConfig (array $uploadArray,$e)
    {
        $goodsIni          = array();
        $goodsIni['goods'] = array(
                'goods_thumb_width'  => (int) $uploadArray['goods_thumb_width'],
                'goods_thumb_heigth' => (int) $uploadArray['goods_thumb_heigth'],
                'goods_image_crop'   => $uploadArray['goods_image_crop'],
                'goods_watermark_state'=> (int) $uploadArray['goods_watermark_state'],
                'goods_image_name_type'=> $uploadArray['goods_image_name_type'],
        );
        /*$goodsIni['class'] = array(
                'class_ico_width'    => (int) $uploadArray['class_ico_width'],
                'class_ico_height'   => (int) $uploadArray['class_ico_height'],
                'class_ico_crop'     => $uploadArray['class_ico_crop'],
                'class_image_width'  => (int) $uploadArray['class_image_width'],
                'class_image_height' => (int) $uploadArray['class_image_height'],
                'class_image_crop'   => $uploadArray['class_image_crop'],
        );*/
        $goodsIni['brand'] = array(
                'brand_logo_width'  => (int) $uploadArray['brand_logo_width'],
                'brand_logo_height' => (int) $uploadArray['brand_logo_height'],
                'brand_logo_crop'   => $uploadArray['brand_logo_crop'],
        );
        //商品默认图片上传操作
        $goodsDefaultImage = $this->getServiceLocator()->get('shop_system_upload')->systemGoodsDefaultUpload('goods_image_default', (isset($uploadArray['old_goods_image_default']) ? $uploadArray['old_goods_image_default'] : ''), $this->getServiceLocator()->get('adminHelper')->defaultShopSet('shop_goods_width'), $this->getServiceLocator()->get('adminHelper')->defaultShopSet('shop_goods_height'));
        $goodsIni['goods']['goods_image_default'] = $goodsDefaultImage['image'];
        //品牌上传操作
        $brandDefaultImage = $this->getServiceLocator()->get('shop_system_upload')->systemBrandDefaultUpload('brand_logo_default', (isset($uploadArray['old_brand_logo_default']) ? $uploadArray['old_brand_logo_default'] : ''), $this->getServiceLocator()->get('adminHelper')->defaultShopSet('shop_brand_width'), $this->getServiceLocator()->get('adminHelper')->defaultShopSet('shop_brand_height'));
        $goodsIni['brand']['brand_image_default'] = $brandDefaultImage['image'];        
        
        $e->toFile(DBSHOP_PATH . '/data/moduledata/Upload/Goods.ini', $goodsIni);
    }
    /**
     * 附件设置之水印设置
     * @param array $uploadArray
     * @param $e
     */
    private function saveWatermarkConfig (array $uploadArray,$e)
    {
        $watermarkIni    = array();
        $watermarkIni['config'] = array(
              'watermark_state'    => $uploadArray['watermark_state'], 
              'watermark_type'     => $uploadArray['watermark_type'],
              'watermark_trans'    => $uploadArray['watermark_trans'],
              'watermark_location' => $uploadArray['watermark_location'],
        );
        $watermarkIni['image']  = array(
              'watermark_image_width'  => $uploadArray['watermark_image_width'],
              'watermark_image_height' => $uploadArray['watermark_image_height'],
        );
        $watermarkIni['text']  = array(
              'watermark_text'       => $uploadArray['watermark_text'],
              'watermark_text_size'  => $uploadArray['watermark_text_size'],
              'watermark_text_color' => $uploadArray['watermark_text_color'],
        );
        $watermarkImage = $this->getServiceLocator()->get('shop_system_upload')->systemWatermarkImageUpload('watermark_image', (isset($uploadArray['old_watermark_image']) ? $uploadArray['old_watermark_image'] : ''), array('width'=>$uploadArray['watermark_image_width'], 'height'=>$uploadArray['watermark_image_height']));
        $watermarkIni['image']['watermark_image'] = $watermarkImage['image'];
        
        $e->toFile(DBSHOP_PATH . '/data/moduledata/Upload/Watermark.ini', $watermarkIni);
    }
    /**
     * 附件设置之基础设置
     * @param array $uploadArray
     * @param $e
     */
    private function saveUploadConfig (array $uploadArray,$e)
    {

        $uploadIni     = array();
        $uploadIni['image'] = array(
              'upload_image_max'  => (int) $uploadArray['upload_image_max'],
              'upload_image_type' => array(
                          'jpg' => (isset($uploadArray['jpg']) ? $uploadArray['jpg'] : ''),
                          'gif' => (isset($uploadArray['gif']) ? $uploadArray['gif'] : ''),
                          'png' => (isset($uploadArray['png']) ? $uploadArray['png'] : ''),
                          'bmp' => (isset($uploadArray['bmp']) ? $uploadArray['bmp'] : ''),
                          'ico' => (isset($uploadArray['ico']) ? $uploadArray['ico'] : ''),
              )
        );
        $e->toFile(DBSHOP_PATH . '/data/moduledata/Upload/Upload.ini', $uploadIni);
    }

    private function saveStorageConfig (array $storageArray, $e)
    {
        $storageIni = array(
            'storage_type'      => trim($storageArray['storage_type']),      //存储类型，Local（本地）、Qiniu（七牛云存储）

            'qiniu_space_name'  => trim($storageArray['qiniu_space_name']),  //七牛空间名称
            'qiniu_ak'          => trim($storageArray['qiniu_ak']),          //七牛AK
            'qiniu_sk'          => trim($storageArray['qiniu_sk']),          //七牛SK
            'qiniu_domain'      => trim($storageArray['qiniu_domain']),      //七牛域名
            'qiniu_http_type'   => trim($storageArray['qiniu_http_type']),  //http类型

            'aliyun_space_name' => trim($storageArray['aliyun_space_name']),  //阿里云空间名称
            'aliyun_ak'         => trim($storageArray['aliyun_ak']),          //阿里云AK
            'aliyun_sk'         => trim($storageArray['aliyun_sk']),          //阿里云SK
            'aliyun_oss_domain' => trim($storageArray['aliyun_oss_domain']),  //阿里云节点域名
            'aliyun_domain'     => trim($storageArray['aliyun_domain']),      //阿里云访问域名
            'aliyun_http_type'  => trim($storageArray['aliyun_http_type']),   //http类型
            'aliyun_domain_type'=> trim($storageArray['aliyun_domain_type']), //访问域名类型，true为自定义域名，false为官方域名
        );
        $e->toFile(DBSHOP_PATH . '/data/moduledata/Upload/Storage.ini', $storageIni);
    }
    /**
     * 客户设置
     * @param array $usersetArray
     * @param $e
     */
    private function saveUserConfig (array $usersetArray, $e)
    {
        $userIni = array();
        $userIni['user_register_state']     = $usersetArray['user_register_state'];
        $userIni['register_close_message']  = trim($usersetArray['register_close_message']);
        $userIni['default_group_id']        = $usersetArray['default_group_id'];
        $userIni['register_audit']          = $usersetArray['register_audit'];
        $userIni['register_retain']         = $usersetArray['register_retain'];
        $userIni['welcomeemail']            = (isset($usersetArray['welcomeemail']) ? $usersetArray['welcomeemail'] : '');
        $userIni['welcome_email_title']     = trim($usersetArray['welcome_email_title']);
        $userIni['welcome_email_body']      = trim(str_replace('"', "'", $usersetArray['welcome_email_body']));
        $userIni['user_register_body']      = trim(str_replace('"', "'", $usersetArray['user_register_body']));
        $userIni['front_other_login_show']  = $usersetArray['front_other_login_show'];
        $userIni['qq_login_state']          = $usersetArray['qq_login_state'];
        $userIni['weixin_login_state']      = $usersetArray['weixin_login_state'];
        $userIni['alipay_login_state']      = $usersetArray['alipay_login_state'];
        $userIni['weixin_phone_login_state']= $usersetArray['weixin_phone_login_state'];

        $userDefaultAvatar = $this->getServiceLocator()->get('shop_other_upload')->userDefaultAvatar('default_avatar', (isset($usersetArray['old_default_avatar']) ? $usersetArray['old_default_avatar'] : ''), $this->getServiceLocator()->get('adminHelper')->defaultShopSet('shop_user_avatar_width'), $this->getServiceLocator()->get('adminHelper')->defaultShopSet('shop_user_avatar_height'));
        $userIni['default_avatar'] = $userDefaultAvatar['image'];

        $e->toFile(DBSHOP_PATH . '/data/moduledata/User/User.ini', $userIni);

        //第三方登录设置保存
        $otherLoginIni = array();
        //QQ互联
        $otherLoginIni['QQ']['login_state']  = $usersetArray['qq_login_state'];
        $otherLoginIni['QQ']['app_id']       = $usersetArray['qq_app_id'];
        $otherLoginIni['QQ']['app_key']      = $usersetArray['qq_app_key'];
        //微信网站
        $otherLoginIni['Weixin']['login_state']     = $usersetArray['weixin_login_state'];
        $otherLoginIni['Weixin']['app_id']          = $usersetArray['weixin_app_id'];
        $otherLoginIni['Weixin']['app_secret']      = $usersetArray['weixin_app_secret'];
        //微信内登录
        $otherLoginIni['Weixinphone']['login_state']     = $usersetArray['weixin_phone_login_state'];
        $otherLoginIni['Weixinphone']['app_id']          = $usersetArray['weixin_phone_app_id'];
        $otherLoginIni['Weixinphone']['app_secret']      = $usersetArray['weixin_phone_app_secret'];
        //支付宝
        $otherLoginIni['Alipay']['login_state']     = $usersetArray['alipay_login_state'];
        $otherLoginIni['Alipay']['alipay_pid']      = $usersetArray['alipay_pid'];
        $otherLoginIni['Alipay']['alipay_key']      = $usersetArray['alipay_key'];

        $e->toFile(DBSHOP_PATH . '/data/moduledata/User/OtherLogin.ini', $otherLoginIni);

        //注册与登录
        $registerOrLoginIni = array();
        $registerOrLoginIni['register_email_state']     = $usersetArray['register_email_state'];
        $registerOrLoginIni['register_phone_state']     = $usersetArray['register_phone_state'];

        $registerOrLoginIni['login_email_state']        = $usersetArray['login_email_state'];
        $registerOrLoginIni['login_phone_state']        = $usersetArray['login_phone_state'];

        $registerOrLoginIni['other_login_email_state']  = $usersetArray['other_login_email_state'];

        $e->toFile(DBSHOP_PATH . '/data/moduledata/User/RegOrLogin.ini', $registerOrLoginIni);
    }
    /**
     * 将验证码的设置，单独提出来（之前是放在系统基础设置的config内）
     * @param array $data
     * @param $e
     */
    private function saveCaptchaConfig(array $data, $e)
    {
        $captchaConfig['user_register_captcha'] = (isset($data['user_register_captcha']) ? $data['user_register_captcha'] : '');
        $captchaConfig['user_login_captcha']    = (isset($data['user_login_captcha'])    ? $data['user_login_captcha'] : '');
        $captchaConfig['goods_ask_captcha']     = (isset($data['goods_ask_captcha'])    ? $data['goods_ask_captcha'] : '');

        $captchaConfig['phone_user_register_captcha']                = (isset($data['phone_user_register_captcha'])                 ? $data['phone_user_register_captcha']              : '');;
        $captchaConfig['phone_captcha_time']                         = (isset($data['phone_captcha_time'])                          ? $data['phone_captcha_time']                       : '60');;

        $e->toFile(DBSHOP_PATH . '/data/moduledata/User/CaptchaConfig.ini', $captchaConfig);
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName)
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}
