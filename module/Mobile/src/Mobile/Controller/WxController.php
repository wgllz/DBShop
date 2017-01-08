<?php
/**
 * DBShop 电子商务系统
 *
 * ==========================================================================
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2016 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 * ==========================================================================
 *
 * @author    静静的风
 *
 */

namespace Mobile\Controller;

use Zend\View\Model\ViewModel;

class WxController extends MobileHomeController
{
    private $dbTables = array();
    private $translator;

    /**
     * 微信内付款地址
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        //判读是否是微信浏览器
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false) return $this->redirect()->toRoute('mobile/default');

        $orderId = (int) $this->params('order_id');
        //订单基本信息
        $orderInfo  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_id'=>$orderId));
        if($orderInfo->pay_code == '' or $orderInfo->buyer_id != $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')) { return $this->redirect()->toRoute('mobile/default'); }

        //订单配送信息
        $deliveryAddress = $this->getDbshopTable('OrderDeliveryAddressTable')->infoDeliveryAddress(array('order_id'=>$orderId));
        //订单商品
        $orderGoods = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=>$orderId));
        //打包数据，传给下面的支付输出
        $httpHost = $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost();
        $httpType = $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps();
        $paymentData = array(
            'shop_name' => $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name'),
            'order'     => $orderInfo,
            'address'   => $deliveryAddress,
            'goods'     => $orderGoods,
            'notify_url'=> $httpType . $httpHost . $this->url()->fromRoute('m_wx/default/wx_order_id', array('action'=>'notify', 'order_id'=>$orderId)),
            'wxreturn_url'=> $httpType . $httpHost . $this->url()->fromRoute('m_wx/default/wx_order_id', array('action'=>'index', 'order_id'=>$orderId))
        );

        $result = $this->getServiceLocator()->get($orderInfo->pay_code)->paymentTo($paymentData);

        $view = new ViewModel();
        $view->setTemplate('/mobile/home/order_pay.phtml');

        $array = array(
            'jsApiParameters' => $result,
            'order_id' => $orderId
        );
        $view->setVariables($array);
        return $view;
    }
    /**
     * 微信内支付返回地址
     */
    public function notifyAction()
    {

        $orderId = (int) $this->params('order_id');
        //订单基本信息
        $orderInfo  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_id'=>$orderId));
        if($orderInfo->pay_code == '') exit();

        //语言包及支付处理，在支付模块中进行
        $language      = $this->paymentLanguage();
        $array = $this->getServiceLocator()->get($orderInfo->pay_code)->paymentNotify($orderInfo, $language);

        //付款成功
        if(isset($array['payFinish']) and $array['payFinish']) {
            $updateOrderArray = array('order_state'=>20, 'order_out_sn'=>(isset($_REQUEST['trade_no']) ? $_REQUEST['trade_no'] : ''), 'pay_time'=>time());

            if($updateOrderArray['order_state'] == 20 and $orderInfo->order_state != 20) {

                $this->getDbshopTable('OrderTable')->updateOrder($updateOrderArray, array('order_id'=>$orderId));
                //保存订单历史
                $this->getDbshopTable('OrderLogTable')->addOrderLog(array('order_id'=>$orderId, 'order_state'=>20, 'state_info'=>$this->getDbshopLang()->translate('支付完成'), 'log_time'=>time(), 'log_user'=>$orderInfo->buyer_name));

                //查看是否有虚拟商品，如果有进行虚拟商品处理
                $virtualGoods = $this->getDbshopTable('OrderGoodsTable')->InfoOrderGoods(array('order_id'=>$orderId, 'buyer_id'=>$orderInfo->buyer_id, 'goods_type'=>2));
                if($virtualGoods and !empty($virtualGoods)) {
                    for($i=0; $i<$virtualGoods->buy_num; $i++) {
                        $virtualGoodsInfo = $this->getDbshopTable('VirtualGoodsTable')->infoVirtualGoods(array('goods_id'=>$virtualGoods->goods_id, 'virtual_goods_state'=>1));
                        if(is_array($virtualGoodsInfo[0]) and !empty($virtualGoodsInfo[0])) {
                            $updateVirtualGoods = array();
                            $updateVirtualGoods['order_sn'] = $orderInfo->order_sn;
                            $updateVirtualGoods['virtual_goods_state'] = 2;
                            $updateVirtualGoods['order_id'] = $orderInfo->order_id;
                            $updateVirtualGoods['user_id']  = $orderInfo->buyer_id;
                            $updateVirtualGoods['user_name']= $orderInfo->buyer_name;

                            if($virtualGoodsInfo[0]['virtual_goods_account_type'] != 1 or $virtualGoodsInfo[0]['virtual_goods_password_type'] != 1) {
                                mt_srand((double) microtime() * 1000000);
                                if($virtualGoodsInfo[0]['virtual_goods_account_type'] == 2) $updateVirtualGoods['virtual_goods_account'] = 'U' . date('Ymd') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
                                if(in_array($virtualGoodsInfo[0]['virtual_goods_account_type'], array(1,3))) $updateVirtualGoods['virtual_goods_account'] = $virtualGoodsInfo[0]['virtual_goods_account'];

                                $chars = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");
                                if($virtualGoodsInfo[0]['virtual_goods_password_type'] == 2) $updateVirtualGoods['virtual_goods_password'] = $chars[rand(0, 25)] . $chars[rand(0, 25)] . str_replace('.', '',microtime(true));;
                                if(in_array($virtualGoodsInfo[0]['virtual_goods_password_type'], array(1,3))) $updateVirtualGoods['virtual_goods_password'] = $virtualGoodsInfo[0]['virtual_goods_password'];

                                $updateVirtualGoods['virtual_goods_account_type'] = $virtualGoodsInfo[0]['virtual_goods_account_type'];
                                $updateVirtualGoods['virtual_goods_password_type'] = $virtualGoodsInfo[0]['virtual_goods_password_type'];
                                $updateVirtualGoods['goods_id'] = $virtualGoodsInfo[0]['goods_id'];
                                if($virtualGoodsInfo[0]['virtual_goods_end_time'] != 0) $updateVirtualGoods['virtual_goods_end_time'] = $virtualGoodsInfo[0]['virtual_goods_end_time'];

                                $this->getDbshopTable('VirtualGoodsTable')->addVirtualGoods($updateVirtualGoods);
                            } else {
                                $this->getDbshopTable('VirtualGoodsTable')->updateVirtualGoods($updateVirtualGoods, array('virtual_goods_id'=>$virtualGoodsInfo[0]['virtual_goods_id']));
                            }
                        }
                    }
                    //如果订单中没有实物商品，则发货处理
                    $vGoodsInfo = $this->getDbshopTable('OrderGoodsTable')->InfoOrderGoods(array('order_id'=>$orderId, 'buyer_id'=>$orderInfo->buyer_id, 'goods_type'=>1));
                    if(empty($vGoodsInfo)) $this->getDbshopTable('OrderTable')->updateOrder(array('order_state'=>40, 'express_time'=>time()), array('order_id'=>$orderId));
                }

                /*----------------------提醒信息发送----------------------*/
                $sendArray['buyer_name']  = $orderInfo->buyer_name;
                $sendArray['order_sn']    = $orderInfo->order_sn;
                $sendArray['order_total'] = $orderInfo->order_amount;
                $sendArray['time']        = time();
                $sendArray['buyer_email'] = $orderInfo->buyer_email;
                $sendArray['order_state'] = 'payment_finish';
                $sendArray['time_type']   = 'paymenttime';
                $sendArray['subject']     = $this->getDbshopLang()->translate('订单付款完成');
                $this->changeStateSendMail($sendArray);
                /*----------------------提醒信息发送----------------------*/

                /*----------------------手机提醒信息发送----------------------*/
                $smsData = array(
                    'buyname'   => $sendArray['buyer_name'],
                    'ordersn'    => $sendArray['order_sn'],
                    'ordertotal'=> $sendArray['order_total'],
                    'time'    => $sendArray['time'],
                );
                try {
                    $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$orderInfo->buyer_id));
                    $this->getServiceLocator()->get('shop_send_sms')->toSendSms(
                        $smsData,
                        $userInfo->user_phone,
                        'alidayu_payment_order_template_id',
                        $orderInfo->buyer_id
                    );
                } catch(\Exception $e) {

                }
                /*----------------------手机提醒信息发送----------------------*/
            }

        } elseif (isset($array['payFinish']) and !$array['payFinish']) {//未成功，可能在进行中

        }

        exit($array['message']);
    }
    /**
     * 微信支付完成后返回页面（无论成功或者失败）
     */
    public function wxpayfinishAction()
    {
        //判读是否是微信浏览器
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false) return $this->redirect()->toRoute('mobile/default');

        $orderId    = (int) $this->params('order_id');
        $state      = trim($this->request->getQuery('state'));

        $view = new ViewModel();
        $view->setTemplate('/mobile/home/wx_pay_finish.phtml');
        $array = array(
            'state'     => $state,
            'order_id'  => $orderId
        );
        $view->setVariables($array);
        return $view;
    }
    /**
     * 订单变更发送邮件
     * @param array $data
     */
    private function changeStateSendMail(array $data)
    {
        $sendMessageBody = $this->getServiceLocator()->get('frontHelper')->getSendMessageBody($data['order_state']);
        if($sendMessageBody != '') {
            $sendArray = array();
            $sendArray['shopname']      = $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name');
            $sendArray['buyname']       = $data['buyer_name'];
            $sendArray['ordersn']       = $data['order_sn'];
            $sendArray['ordertotal']    = isset($data['order_total'])    ? $data['order_total'] : '';
            $sendArray['expressname']   = isset($data['express_name'])   ? $data['express_name'] : '';
            $sendArray['expressnumber'] = isset($data['express_number']) ? $data['express_number'] : '';
            $sendArray[$data['time_type']]= $data['time'];
            $sendArray['shopurl']       = $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost() . $this->url()->fromRoute('shopfront/default');

            $sendArray['subject']       = $sendArray['shopname'] . $data['subject'];
            $sendArray['send_mail'][]   = $this->getServiceLocator()->get('frontHelper')->getSendMessageBuyerEmail($data['order_state'] . '_state', $data['buyer_email']);
            $sendArray['send_mail'][]   = $this->getServiceLocator()->get('frontHelper')->getSendMessageAdminEmail($data['order_state'] . '_state');

            $sendMessageBody            = $this->getServiceLocator()->get('frontHelper')->createSendMessageContent($sendArray, $sendMessageBody);
            try {
                $sendState = $this->getServiceLocator()->get('shop_send_mail')->SendMesssageMail($sendArray, $sendMessageBody);
                $sendState = ($sendState ? 1 : 2);
            } catch (\Exception $e) {
                $sendState = 2;
            }
            //记录给用户发的电邮
            if($sendArray['send_mail'][0] != '') {
                $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_name'=>$sendArray['buyname']));
                $sendLog = array(
                    'mail_subject' => $sendArray['subject'],
                    'mail_body'    => $sendMessageBody,
                    'send_time'    => time(),
                    'user_id'      => $userInfo->user_id,
                    'send_state'   => $sendState
                );
                $this->getDbshopTable('UserMailLogTable')->addUserMailLog($sendLog);
            }
        }
    }
    /**
     * 在上面支付中需要用到的语言包
     * @return multitype:string NULL Ambigous <string, string, NULL, multitype:NULL , multitype:string NULL >
     */
    private function paymentLanguage()
    {
        $array = array(
            'order_total'       =>$this->getDbshopLang()->translate('订单总金额'),

            'return_order'      =>$this->url()->fromRoute('frontorder/default'),
        );
        return $array;
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
    /**
     * 语言包调用
     * @return Ambigous <object, multitype:, \Zend\I18n\Translator\Translator>
     */
    private function getDbshopLang ()
    {
        if (! $this->translator) {
            $this->translator = $this->getServiceLocator()->get('translator');
        }
        return $this->translator;
    }
}