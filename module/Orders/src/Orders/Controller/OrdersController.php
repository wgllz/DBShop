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

namespace Orders\Controller;

use Admin\Controller\BaseController;
use Upload\Plugin\Phpexcel\PHPExcel;
use Zend\View\Model\ViewModel;

class OrdersController extends BaseController
{
    public function indexAction()
    {
        $array = array();
        
        $searchArray  = array();
        if($this->request->isGet()) {
            $searchArray = $this->request->getQuery()->toArray();
        }
        //订单列表
        $page = $this->params('page',1);
        $array['order_list'] = $this->getDbshopTable()->listOrder(array('page'=>$page, 'page_num'=>20), $searchArray);
        $array['page']= $page;
        $array['searchArray'] = $searchArray;

        
        return $array;
    }
    /** 
     * 编辑查看订单
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:number NULL
     */
    public function editAction ()
    {
        $array = array();
        
        $array['page'] = (int) $this->params('page', 1);
        $orderId       = (int) $this->params('order_id');
        $array['query']= $this->request->getQuery()->toArray();
        //订单信息
        $array['order_info'] = $this->getDbshopTable()->infoOrder(array('order_id'=>$orderId));
        if(!$array['order_info']) return $this->redirect()->toRoute('orders/default');
        
        //订单配送信息
        $array['delivery_address'] = $this->getDbshopTable('OrderDeliveryAddressTable')->infoDeliveryAddress(array('order_id'=>$orderId));
        
        //订单商品
        $array['order_goods'] = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=>$orderId));
        //退货信息
        if($array['order_info']->refund_state == 1) {
            $array['refund_order']=$this->getDbshopTable('OrderRefundTable')->infoOrderRefund(array('order_sn'=>$array['order_info']->order_sn));
        }
        //订单操作历史
        $array['order_log'] = $this->getDbshopTable('OrderLogTable')->listOrderLog(array('order_id'=>$orderId));
        
        //物流状态信息
        if($array['order_info']['order_state'] >= 40 and $array['delivery_address']['express_number'] != '') {
            $iniReader   = new \Zend\Config\Reader\Ini();
            $expressPath = DBSHOP_PATH . '/data/moduledata/Express/';
            if(file_exists($expressPath . $array['order_info']['express_id'] . '.ini')) {
                $expressIni = $iniReader->fromFile($expressPath . $array['order_info']['express_id'] . '.ini');
                $array['express_url'] = $expressIni['express_url'];
                if(is_array($expressIni) and $expressIni['express_name_code'] != '' and file_exists($expressPath . 'express.xml')) {
                    $xmlReader    = new \Zend\Config\Reader\Xml();
                    $expressArray = $xmlReader->fromFile($expressPath . 'express.xml');
                    if(!empty($expressArray)) {
                        $array['express_state_array'] = $this->getServiceLocator()->get('shop_express_state')->getExpressStateContent($expressArray, $expressIni['express_name_code'], $array['delivery_address']['express_number']);
                    }
                }
            }
        }

        //订单总价修改历史
        $array['order_amount_log'] = $this->getDbshopTable('OrderAmountLogTable')->listOrderAmountLog(array('order_id'=>$array['order_info']['order_id']));

        return $array;
    }
    /** 
     * 订单打印
     */
    public function orderprintAction ()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $array     = array();
        
        $orderId = (int) $this->params('order_id');
        //订单信息
        $array['order_info'] = $this->getDbshopTable()->infoOrder(array('order_id'=>$orderId));
        if(!$array['order_info']) return $this->redirect()->toRoute('orders/default');
        
        //订单配送信息
        $array['delivery_address'] = $this->getDbshopTable('OrderDeliveryAddressTable')->infoDeliveryAddress(array('order_id'=>$orderId));
        
        //订单商品
        $array['order_goods'] = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=>$orderId));
                
        $viewModel->setVariables($array);
        return $viewModel;
    }
    /** 
     * 订单支付状态修改
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:number NULL
     */
    public function payoperAction ()
    {
        $array = array();
        
        $array['page'] = (int) $this->params('page', 1);
        $orderId = (int) $this->params('order_id');
        
        //订单信息
        $array['order_info'] = $this->getDbshopTable()->infoOrder(array('order_id'=>$orderId));
        $orderInfo = $array['order_info'];

        if($this->request->isPost()) {
            $stateArray = $this->request->getPost()->toArray();
            if($stateArray['pay_state'] != $array['order_info']->order_state) {
                
                $this->getDbshopTable()->updateOrder(array('order_state'=>$stateArray['pay_state']), array('order_id'=>$orderId));
                if($stateArray['pay_state'] == 20) {//付款完成

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

                    $payTime = time();
                    $this->getDbshopTable()->updateOrder(array('pay_time'=>$payTime), array('order_id'=>$orderId));
                    /*----------------------付款完成提醒信息发送----------------------*/
                    $sendArray['buyer_name']  = $array['order_info']->buyer_name;
                    $sendArray['order_sn']    = $array['order_info']->order_sn;
                    $sendArray['time']        = $payTime;
                    $sendArray['buyer_email'] = $array['order_info']->buyer_email;
                    $sendArray['order_state'] = 'payment_finish';
                    $sendArray['time_type']   = 'paymenttime';
                    $sendArray['subject']     = $this->getDbshopLang()->translate('订单付款完成');
                    $this->changeStateSendMail($sendArray);
                    /*----------------------付款完成提醒信息发送----------------------*/

                    /*----------------------手机提醒信息发送----------------------*/
                    $smsData = array(
                        'buyname'  => $sendArray['buyer_name'],
                        'ordersn'    => $sendArray['order_sn'],
                        'time'        => $sendArray['time'],
                    );
                    try {
                        $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$array['order_info']->buyer_id));
                        $this->getServiceLocator()->get('shop_send_sms')->toSendSms(
                            $smsData,
                            $userInfo->user_phone,
                            'alidayu_payment_order_template_id',
                            $array['order_info']->buyer_id
                        );
                    } catch(\Exception $e) {

                    }
                    /*----------------------手机提醒信息发送----------------------*/
                }
                
                $this->getDbshopTable('OrderLogTable')->addOrderLog(array('order_id'=>$orderId, 'order_state'=>$stateArray['pay_state'], 'state_info'=>$stateArray['state_info'], 'log_time'=>time(), 'log_user'=>$this->getServiceLocator()->get('adminHelper')->returnAuth('admin_name')));
                
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('订单管理'), 'operlog_info'=>$this->getDbshopLang()->translate('更新订单支付状态') . '&nbsp;' . $array['order_info']->order_sn . ' : ' . $this->getServiceLocator()->get('frontHelper')->getOneOrderStateInfo($stateArray['pay_state'])));
                
                return $this->redirect()->toRoute('orders/default/order_id',array('action'=>'edit','controller'=>'Orders','order_id'=>$orderId,'page'=>$array['page']));
            }
        }
        
        return $array;
    }
    /** 
     * 配送状态修改
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:number NULL
     */
    public function shipoperAction ()
    {
        $array = array();
        
        $array['page'] = (int) $this->params('page', 1);
        $orderId = (int) $this->params('order_id');
        $array['url_type'] = $this->request->getQuery('url_type');//此为接收从发货单查看过来的信息
        //订单信息
        $array['order_info'] = $this->getDbshopTable()->infoOrder(array('order_id'=>$orderId));
        //快递单号
        $array['express_number'] = $this->getDbshopTable('ExpressNumberTable')->oneExpressNumber(array('express_id'=>$array['order_info']->express_id, 'express_number_state'=>0));
        //订单配送信息
        $array['delivery_address'] = $this->getDbshopTable('OrderDeliveryAddressTable')->infoDeliveryAddress(array('order_id'=>$orderId));
        
        if($this->request->isPost()) {
            $stateArray = $this->request->getPost()->toArray();
            if($stateArray['ship_state'] != $array['order_info']->order_state) {
                //支付方式中的发货处理
                $array['delivery_address']->express_number = ($stateArray['express_number']? $stateArray['express_number'] : 'empty');
                if(!$this->getServiceLocator()->get($array['order_info']->pay_code)->toSendOrder($array['order_info'], $array['delivery_address'])) exit('false');
                
                $this->getDbshopTable()->updateOrder(array('order_state'=>$stateArray['ship_state']), array('order_id'=>$orderId));
                //当是发货时，编辑发货时间
                $expressTime = time();
                if($stateArray['ship_state'] == 40) $this->getDbshopTable()->updateOrder(array('express_time'=>$expressTime), array('order_id'=>$orderId));
                //当有快递单号时，进行编辑
                if($stateArray['express_number'] != '') {
                    $this->getDbshopTable('OrderDeliveryAddressTable')->updataDeliveryAddress(array('express_number'=>$stateArray['express_number']), array('order_id'=>$orderId, 'express_id'=>$array['delivery_address']->express_id));
                    //判断是否在快递单数据表中有，如果有且未被使用的，使用
                    if($this->getDbshopTable('ExpressNumberTable')->infoExpressNumber(array('express_id'=>$array['order_info']->express_id, 'express_number'=>$stateArray['express_number'], 'express_number_state'=>0))) {
                        $updateExpressNumberArray = array();
                        $updateExpressNumberArray['order_id'] = $array['order_info']->order_id;
                        $updateExpressNumberArray['order_sn'] = $array['order_info']->order_sn;
                        $updateExpressNumberArray['express_number_state'] = 1;
                        $updateExpressNumberArray['express_number_use_time'] = time();
                        $this->getDbshopTable('ExpressNumberTable')->updateExpressNumber($updateExpressNumberArray, array('express_id'=>$array['order_info']->express_id, 'express_number'=>$stateArray['express_number'], 'express_number_state'=>0));
                    }
                }
                //保存订单历史
                $this->getDbshopTable('OrderLogTable')->addOrderLog(array('order_id'=>$orderId, 'order_state'=>$stateArray['ship_state'], 'state_info'=>$stateArray['state_info'], 'log_time'=>$expressTime, 'log_user'=>$this->getServiceLocator()->get('adminHelper')->returnAuth('admin_name')));
                
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('订单管理'), 'operlog_info'=>$this->getDbshopLang()->translate('更新订单配送状态') . '&nbsp;' . $array['order_info']->order_sn . ' : ' . $this->getServiceLocator()->get('frontHelper')->getOneOrderStateInfo($stateArray['ship_state'])));
                
                /*----------------------提醒信息发送----------------------*/
                $sendArray['buyer_name']  = $array['order_info']->buyer_name;
                $sendArray['order_sn']    = $array['order_info']->order_sn;
                $sendArray['time']        = $expressTime;
                $sendArray['buyer_email'] = $array['order_info']->buyer_email;
                $sendArray['order_state'] = 'ship_finish';
                $sendArray['time_type']   = 'shiptime';
                $sendArray['subject']     = $this->getDbshopLang()->translate('订单发货完成|管理员操作');
                $this->changeStateSendMail($sendArray);
                /*----------------------提醒信息发送----------------------*/

                /*----------------------手机提醒信息发送----------------------*/
                $smsData = array(
                    'buyname'  => $sendArray['buyer_name'],
                    'ordersn'    => $sendArray['order_sn'],
                    'time'        => $sendArray['time'],
                );
                try {
                    $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$array['order_info']->buyer_id));
                    $this->getServiceLocator()->get('shop_send_sms')->toSendSms(
                        $smsData,
                        $userInfo->user_phone,
                        'alidayu_ship_order_template_id',
                        $array['order_info']->buyer_id
                    );
                } catch(\Exception $e) {

                }
                /*----------------------手机提醒信息发送----------------------*/

                if($array['url_type'] == 'show_ship') {//从发货单过来的
                    return $this->redirect()->toRoute('orders/default/order_id',array('action'=>'showShip','controller'=>'Orders','order_id'=>$orderId,'page'=>$array['page']));
                } else {
                    return $this->redirect()->toRoute('orders/default/order_id',array('action'=>'edit','controller'=>'Orders','order_id'=>$orderId,'page'=>$array['page']));
                }
            }
        }
        
        return $array;
    }
    /** 
     * 订单完成状态
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:number NULL
     */
    public function finishoperAction ()
    {
        $array = array();
        
        $array['page'] = (int) $this->params('page', 1);
        $orderId = (int) $this->params('order_id');
        
        //订单信息
        $array['order_info'] = $this->getDbshopTable()->infoOrder(array('order_id'=>$orderId));
        if($this->request->isPost()) {
            $stateArray = $this->request->getPost()->toArray();
            if($stateArray['order_state'] != $array['order_info']->order_state) {
                $finishTime =time();
                $this->getDbshopTable()->updateOrder(array('order_state'=>$stateArray['order_state'], 'finish_time'=>$finishTime), array('order_id'=>$orderId));
                //保存订单历史
                $this->getDbshopTable('OrderLogTable')->addOrderLog(array('order_id'=>$orderId, 'order_state'=>$stateArray['order_state'], 'state_info'=>$stateArray['state_info'], 'log_time'=>$finishTime, 'log_user'=>$this->getServiceLocator()->get('adminHelper')->returnAuth('admin_name')));
                
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('订单管理'), 'operlog_info'=>$this->getDbshopLang()->translate('更新订单状态') . '&nbsp;' . $array['order_info']->order_sn . ' : ' . $this->getServiceLocator()->get('frontHelper')->getOneOrderStateInfo($stateArray['order_state'])));

                //积分获取
                if($array['order_info']->integral_num > 0 or $array['order_info']->integral_type_2_num > 0) {
                    $integralLogArray = array();
                    $integralLogArray['user_id']           = $array['order_info']->buyer_id;
                    $integralLogArray['user_name']         = $array['order_info']->buyer_name;
                    $integralLogArray['integral_log_info'] = $this->getDbshopLang()->translate('商品购物，订单号为：') . $array['order_info']->order_sn . '<br>';
                    $integralLogArray['integral_log_time'] = time();

                    if($array['order_info']->integral_num > 0) {//消费积分
                        $integralLogArray['integral_num_log']  = $array['order_info']->integral_num;
                        $integralLogArray['integral_log_info'] .= $array['order_info']->integral_rule_info;
                        if($this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray)) {
                            //会员消费积分更新
                            $this->getDbshopTable('UserTable')->updateUserIntegralNum($integralLogArray, array('user_id'=>$array['order_info']->buyer_id));
                        }
                    }
                    if($array['order_info']->integral_type_2_num > 0) {//等级积分
                        $integralLogArray['integral_num_log']  = $array['order_info']->integral_type_2_num;
                        $integralLogArray['integral_log_info'] .= $array['order_info']->integral_type_2_num_rule_info;
                        $integralLogArray['integral_type_id'] = 2;
                        if($this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray)) {
                            //会员等级积分更新
                            $this->getDbshopTable('UserTable')->updateUserIntegralNum($integralLogArray, array('user_id'=>$array['order_info']->buyer_id), 2);
                        }
                    }
                }
                /*----------------------提醒信息发送----------------------*/
                $sendArray['buyer_name']  = $array['order_info']->buyer_name;
                $sendArray['order_sn']    = $array['order_info']->order_sn;
                $sendArray['time']        = $finishTime;
                $sendArray['buyer_email'] = $array['order_info']->buyer_email;
                $sendArray['order_state'] = 'transaction_finish';
                $sendArray['time_type']   = 'finishtime';
                $sendArray['subject']     = $this->getDbshopLang()->translate('订单交易完成|管理员操作');
                $this->changeStateSendMail($sendArray);
                /*----------------------提醒信息发送----------------------*/

                /*----------------------手机提醒信息发送----------------------*/
                $smsData = array(
                    'buyname'  => $sendArray['buyer_name'],
                    'ordersn'    => $sendArray['order_sn'],
                    'time'        => $sendArray['time'],
                );
                try {
                    $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$array['order_info']->buyer_id));
                    $this->getServiceLocator()->get('shop_send_sms')->toSendSms(
                        $smsData,
                        $userInfo->user_phone,
                        'alidayu_finish_order_template_id',
                        $array['order_info']->buyer_id
                    );
                } catch(\Exception $e) {

                }
                /*----------------------手机提醒信息发送----------------------*/

                return $this->redirect()->toRoute('orders/default/order_id',array('action'=>'edit','controller'=>'Orders','order_id'=>$orderId,'page'=>$array['page']));
            }
        }
        
        return $array;
    }
    /**
     * 订单批量处理，删除
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function editOrderAllAction ()
    {
        if($this->request->isPost()) {
            $orderIdArray = $this->request->getPost('order_id');
            if(is_array($orderIdArray) and !empty($orderIdArray)) {
                $orderSnArray = array();
                foreach ($orderIdArray as $idValue) {
                    $orderInfo  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_id'=>$idValue));
                    if($orderInfo->order_state == 0) {//订单状态为取消状态
                        $this->getDbshopTable('OrderTable')->delOrder(array('order_id'=>$idValue));
                        $orderSnArray[] = $orderInfo->order_sn;
                    }
                }
                if(!empty($orderSnArray)) {
                    //记录操作日志
                    $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('订单管理'), 'operlog_info'=>$this->getDbshopLang()->translate('订单批量删除') . '&nbsp;' . implode(' ', $orderSnArray)));
                }
            }
        }
        return $this->redirect()->toRoute('orders/default');
    }
    /** 
     * 取消订单操作
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function cancelOrderAction ()
    {
        $array['page'] = (int) $this->params('page', 1);
        $orderId       = (int) $this->params('order_id');

        if($orderId == 0) return $this->redirect()->toRoute('orders/default');
        
        $orderInfo  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_id'=>$orderId));
        if($this->request->isPost()) {
            $stateArray = $this->request->getPost()->toArray();

            if($orderInfo and ($orderInfo->order_state == 10 or ($orderInfo->order_state == 30 and $orderInfo->pay_code == 'hdfk'))) {
                $this->getDbshopTable('OrderTable')->updateOrder(array('order_state'=>0), array('order_id'=>$orderId));
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('订单管理'), 'operlog_info'=>$this->getDbshopLang()->translate('更新订单状态') . '&nbsp;' . $orderInfo->order_sn . ' : ' . $this->getServiceLocator()->get('frontHelper')->getOneOrderStateInfo(0)));
                //取消订单对库存进行返回
                $this->operGoodsStock($orderId);
                //检查是否有消费积分付款
                if($orderInfo->integral_buy_num > 0) {
                    $integralLogArray = array();
                    $integralLogArray['user_id']           = $orderInfo->buyer_id;
                    $integralLogArray['user_name']         = $orderInfo->buyer_name;
                    $integralLogArray['integral_log_info'] = $this->getDbshopLang()->translate('取消订单，订单号为：') . $orderInfo->order_sn;
                    $integralLogArray['integral_num_log']  = $orderInfo->integral_buy_num;
                    $integralLogArray['integral_log_time'] = time();
                    if($this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray)) {
                        //会员消费积分更新
                        $this->getDbshopTable('UserTable')->updateUserIntegralNum($integralLogArray, array('user_id'=>$integralLogArray['user_id']));
                    }
                }
                //加入状态记录
                $stateArray['state_info'] = (!empty($stateArray['state_info']) ? trim($stateArray['state_info']) : $this->getDbshopLang()->translate('无说明'));
                $this->getDbshopTable('OrderLogTable')->addOrderLog(array('order_id'=>$orderId, 'order_state'=>'0', 'state_info'=>$stateArray['state_info'], 'log_time'=>time(), 'log_user'=>$this->getServiceLocator()->get('adminHelper')->returnAuth('admin_name')));

                /*----------------------提醒信息发送----------------------*/
                $sendArray['buyer_name']  = $orderInfo->buyer_name;
                $sendArray['order_sn']    = $orderInfo->order_sn;
                $sendArray['time']        = time();
                $sendArray['buyer_email'] = $orderInfo->buyer_email;
                $sendArray['order_state'] = 'cancel_order';
                $sendArray['time_type']   = 'canceltime';
                $sendArray['cancel_info'] = $stateArray['state_info'];
                $sendArray['subject']     = $this->getDbshopLang()->translate('订单取消|管理员操作');
                $this->changeStateSendMail($sendArray);
                /*----------------------提醒信息发送----------------------*/

                /*----------------------手机提醒信息发送----------------------*/
                $smsData = array(
                    'buyname'  => $sendArray['buyer_name'],
                    'ordersn'    => $sendArray['order_sn'],
                    'time'        => $sendArray['time'],
                );
                try {
                    $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$orderInfo->buyer_id));
                    $this->getServiceLocator()->get('shop_send_sms')->toSendSms(
                        $smsData,
                        $userInfo->user_phone,
                        'alidayu_cancel_order_template_id',
                        $orderInfo->buyer_id
                    );
                } catch(\Exception $e) {

                }
                /*----------------------手机提醒信息发送----------------------*/
            }
            return $this->redirect()->toRoute('orders/default/order_id',array('action'=>'edit','controller'=>'Orders','order_id'=>$orderId,'page'=>$array['page']));
        }

        return array('order_info'=>$orderInfo);
    }
    /**
     * 退货管理
     * @return array
     */
    public function refundAction()
    {
        $array = array();
        $array['page'] = (int) $this->params('page', 1);

        $searchArray  = array();
        if($this->request->isGet()) {
            $searchArray = $this->request->getQuery()->toArray();
        }
        $array['searchArray'] = $searchArray;
        $page = $array['page'];
        $array['order_refund_list'] = $this->getDbshopTable('OrderRefundTable')->listOrderRefund(array('page'=>$page, 'page_num'=>20), $searchArray);

        return $array;
    }
    /**
     * 退货处理页面
     */
    public function operRefundAction()
    {
        $array = array();
        $array['page'] = (int) $this->params('page', 1);
        $array['query']= $this->request->getQuery()->toArray();
        $refundId = (int) $this->params('refund_id');

        $array['refund_info'] = $this->getDbshopTable('OrderRefundTable')->infoOrderRefund(array('refund_id'=>$refundId));

        if($this->request->isPost()) {

            if($array['refund_info']->refund_state != 0) return $this->redirect()->toRoute('orders/default/refund-id',array('action'=>'refund', 'controller'=>'Orders', 'page'=>$array['page']));

            $refundArray = $this->request->getPost()->toArray();
            $updateArray['refund_price']   = floatval($refundArray['refund_price']);
            $updateArray['refund_state']   = intval($refundArray['refund_state']);
            $updateArray['re_refund_info'] = trim($refundArray['re_refund_info']);
            $updateArray['finish_refund_time'] = time();
            $updateArray['admin_id']       = $this->getServiceLocator()->get('adminHelper')->returnAuth('admin_id');
            $updateArray['admin_name']     = $this->getServiceLocator()->get('adminHelper')->returnAuth('admin_name');
            $state = $this->getDbshopTable('OrderRefundTable')->updateOrderRefund($updateArray, array('refund_id'=>$refundId));

            if($state) {//退款到账户余额
                $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$array['refund_info']->user_id));

                if($updateArray['refund_state'] == 1 and $array['refund_info']->refund_type == 1) {
                    $moneyLogArray = array();
                    $moneyLogArray['user_id']         = $userInfo->user_id;
                    $moneyLogArray['user_name']       = $userInfo->user_name;
                    $moneyLogArray['money_change_num']= $updateArray['refund_price'];
                    $moneyLogArray['money_pay_state'] = 20;//20是已经处理（充值后者减值，10是待处理）
                    $moneyLogArray['money_pay_type']  = 4;//支付类型，1充值，2消费，3提现，4退款
                    $moneyLogArray['admin_id']        = $updateArray['admin_id'];
                    $moneyLogArray['admin_name']      = $updateArray['admin_name'];
                    $moneyLogArray['money_changed_amount'] = $userInfo->user_money + $moneyLogArray['money_change_num'];
                    $moneyLogArray['money_pay_info']  = $this->getDbshopLang()->translate('订单退货').' '.$this->getDbshopLang()->translate('退货订单编号为：').$array['refund_info']->order_sn;

                    $this->getDbshopTable('UserMoneyLogTable')->addUserMoneyLog($moneyLogArray);

                }
                if($updateArray['refund_state'] == 1) {//如果是同意退货，则对订单进行设置
                    $this->getDbshopTable('OrderTable')->updateOrder(array('refund_state'=>1), array('order_sn'=>$array['refund_info']->order_sn));
                }
                //操作日志记录
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('退货管理'), 'operlog_info'=>$this->getDbshopLang()->translate('退货订单处理').' '.$this->getDbshopLang()->translate('退货状态:').($updateArray['refund_state']==1 ? $this->getDbshopLang()->translate('同意退货') : $this->getDbshopLang()->translate('拒绝退货')).' '.$this->getDbshopLang()->translate('退货的订单编号为:').$array['refund_info']->order_sn));
            }
            return $this->redirect()->toRoute('orders/default/refund-id',array('action'=>'refund', 'controller'=>'Orders', 'page'=>$array['page']));
        }

        $array['order_info']  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_sn'=>$array['refund_info']->order_sn));
        //订单商品
        $array['order_goods'] = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=> $array['order_info']->order_id));

        return $array;
    }
    /**
     * 退货详情查看
     * @return array
     */
    public function showRefundAction()
    {
        $array = array();
        $array['page'] = (int) $this->params('page', 1);
        $array['query']= $this->request->getQuery()->toArray();
        $refundId = (int) $this->params('refund_id');

        $array['refund_info'] = $this->getDbshopTable('OrderRefundTable')->infoOrderRefund(array('refund_id'=>$refundId));

        $array['order_info']  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_sn'=>$array['refund_info']->order_sn));
        //订单商品
        $array['order_goods'] = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=> $array['order_info']->order_id));

        return $array;
    }
    /** 
     * 商品库存操作
     * @param unknown $orderId
     */
    private function operGoodsStock ($orderId)
    {
        $goodsArray = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=>$orderId));
        if(is_array($goodsArray) and !empty($goodsArray)) {
            foreach ($goodsArray as $goodsValue) {
                $goodsInfo = '';
                $goodsInfo = $this->getDbshopTable('GoodsTable')->oneGoodsInfo(array('goods_id'=>$goodsValue['goods_id']));
                if($goodsInfo->goods_stock_state_open != 1) {//如果没有启用库存状态显示
                    if(!empty($goodsValue['goods_color']) and !empty($goodsValue['goods_size'])) {
                        $whereExtend = array('goods_id'=>$goodsValue['goods_id'], 'goods_color'=>$goodsValue['goods_color'], 'goods_size'=>$goodsValue['goods_size']);
                        $extendGoods = $this->getDbshopTable('GoodsPriceExtendGoodsTable')->InfoPriceExtendGoods($whereExtend);
                        $this->getDbshopTable('GoodsPriceExtendGoodsTable')->updatePriceExtendGoods(array('goods_extend_stock'=>($extendGoods->goods_extend_stock + $goodsValue['buy_num'])), $whereExtend);
                    } else {
                        $this->getDbshopTable('GoodsTable')->oneUpdateGoods(array('goods_stock'=>($goodsInfo->goods_stock + $goodsValue['buy_num'])), array('goods_id'=>$goodsValue['goods_id']));
                    }
                }
            }
        }
    }
    /** 
     * 订单删除，必须是取消的订单才可以删除
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function delOrderAction ()
    {
        $orderId    = (int) $this->params('order_id');
        if($orderId == 0) return $this->redirect()->toRoute('orders/default');
        
        $orderInfo  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_id'=>$orderId));
        if($orderInfo->order_state == 0) {
            $this->getDbshopTable('OrderTable')->delOrder(array('order_id'=>$orderId));
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('订单管理'), 'operlog_info'=>$this->getDbshopLang()->translate('订单删除') . '&nbsp;' . $orderInfo->order_sn));
        }
        
        return $this->redirect()->toRoute('orders/default');
    }
    /** 
     * 会员编辑调用订单列表
     * @return string|Ambigous <\Zend\View\Model\ViewModel, \Zend\View\Model\ViewModel>
     */
    public function ajaxOrderListAction ()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        
        $array        = array();
        $searchArray  = array();
        
        $buyerId      = $this->params('buyer_id', 0);
        if($buyerId == 0) {
            return '';
        }
        if($buyerId != 0) {
            $searchArray['buyer_id'] = $buyerId;
            $array['user_id']        = $buyerId;
        }
        $array['show_div_id']    = $this->request->getQuery('show_div_id');
        //订单列表
        $page = $this->params('page',1);
        $array['order_list'] = $this->getDbshopTable()->listOrder(array('page'=>$page, 'page_num'=>20), $searchArray);
        
        return $viewModel->setVariables($array);
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
            $sendArray[$data['time_type']]= $data['time'];
            $sendArray['shopurl']       = 'http://' . $this->getRequest()->getServer('SERVER_NAME') . $this->url()->fromRoute('shopfront/default');

            $sendArray['cancel_info']   = (isset($data['cancel_info']) and !empty($data['cancel_info'])) ? $data['cancel_info'] : '';

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
     * 批量订单发货
     * @return array
     */
    public function moreshipoperAction()
    {
        $array = array();

        $array['express_array'] = $this->getDbshopTable('ExpressTable')->orderExpressList();

        return $array;
    }
    /**
     * 批量订单发货处理
     * @return \Zend\Http\Response
     */
    public function allShipOperAction()
    {
        if($this->request->isPost()) {
            $postArray = $this->request->getPost()->toArray();
            $expressNumberArray = $this->getDbshopTable('ExpressNumberTable')->arrayExpressNumber(array('express_id'=>$postArray['express_id'], 'express_number_state'=>0));
            $orderArray         = $this->getDbshopTable()->allOrder(array('dbshop_order.express_id='.$postArray['express_id'].' and (dbshop_order.order_state=20 or dbshop_order.order_state=30)'));

            $expressNumberCount = count($expressNumberArray);
            $orderCount         = count($orderArray);
            if($expressNumberCount > 0 and $orderCount > 0) {
                $logUser = $this->getServiceLocator()->get('adminHelper')->returnAuth('admin_name');
                foreach($expressNumberArray as $expressNumberKey => $expressNumberValue) {
                    //判断是否在快递单数据表中有，如果有且未被使用的，使用
                    if(isset($orderArray[$expressNumberKey]) and !empty($orderArray[$expressNumberKey])) {
                        $expressTime = time();
                        if($this->getDbshopTable('ExpressNumberTable')->infoExpressNumber(array('express_id'=>$postArray['express_id'], 'express_number'=>$expressNumberValue['express_number'], 'express_number_state'=>0))) {
                            $this->getDbshopTable()->updateOrder(array('order_state'=>40), array('order_id'=>$orderArray[$expressNumberKey]['order_id']));
                            $this->getDbshopTable()->updateOrder(array('express_time'=>$expressTime), array('order_id'=>$orderArray[$expressNumberKey]['order_id']));
                            $this->getDbshopTable('OrderDeliveryAddressTable')->updataDeliveryAddress(array('express_number'=>$expressNumberValue['express_number']), array('order_id'=>$orderArray[$expressNumberKey]['order_id'], 'express_id'=>$postArray['express_id']));

                            $updateExpressNumberArray = array();
                            $updateExpressNumberArray['order_id'] = $orderArray[$expressNumberKey]['order_id'];
                            $updateExpressNumberArray['order_sn'] = $orderArray[$expressNumberKey]['order_sn'];
                            $updateExpressNumberArray['express_number_state'] = 1;
                            $updateExpressNumberArray['express_number_use_time'] = time();
                            $this->getDbshopTable('ExpressNumberTable')->updateExpressNumber($updateExpressNumberArray, array('express_id'=>$orderArray[$expressNumberKey]['express_id'], 'express_number'=>$expressNumberValue['express_number'], 'express_number_state'=>0));

                            //保存订单历史
                            $this->getDbshopTable('OrderLogTable')->addOrderLog(array('order_id'=>$orderArray[$expressNumberKey]['order_id'], 'order_state'=>40, 'state_info'=>'', 'log_time'=>$expressTime, 'log_user'=>$logUser));
                            /*----------------------提醒信息发送----------------------*/
                            $sendArray = array();
                            $sendArray['buyer_name']  = $orderArray[$expressNumberKey]['buyer_name'];
                            $sendArray['order_sn']    = $orderArray[$expressNumberKey]['order_sn'];
                            $sendArray['time']        = $expressTime;
                            $sendArray['buyer_email'] = $orderArray[$expressNumberKey]['buyer_email'];
                            $sendArray['order_state'] = 'ship_finish';
                            $sendArray['time_type']   = 'shiptime';
                            $sendArray['subject']     = $this->getDbshopLang()->translate('订单发货完成|管理员操作');
                            $this->changeStateSendMail($sendArray);
                            /*----------------------提醒信息发送----------------------*/

                            /*----------------------手机提醒信息发送----------------------*/
                            $smsData = array(
                                'buyname'  => $sendArray['buyer_name'],
                                'ordersn'    => $sendArray['order_sn'],
                                'time'        => $sendArray['time'],
                            );
                            try {
                                $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$orderArray[$expressNumberKey]['buyer_id']));
                                $this->getServiceLocator()->get('shop_send_sms')->toSendSms(
                                    $smsData,
                                    $userInfo->user_phone,
                                    'alidayu_ship_order_template_id',
                                    $orderArray[$expressNumberKey]['buyer_id']
                                );
                            } catch(\Exception $e) {

                            }
                            /*----------------------手机提醒信息发送----------------------*/
                        }
                    }
                    if($expressNumberKey == ($orderCount-1)) break;
                }
            }
        }
        return $this->redirect()->toRoute('orders/default',array('action'=>'moreshipoper','controller'=>'Orders'));
    }
    /** 
     * 发货单列表
     * @return multitype:
     */
    public function shiplistAction()
    {
        $array = array();
        //发货列表
        $page = $this->params('page',1);
        $array['ship_list'] = $this->getDbshopTable('OrderDeliveryAddressTable')->listDeliveryAddress(array('page'=>$page, 'page_num'=>20), array('o.order_state >= 20 or o.pay_code="hdfk"'), array('o.order_state ASC'));
        $array['page']= $page;
        
        return $array;
    }
    /** 
     * 发货单查看
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:number NULL Ambigous <multitype:>
     */
    public function showShipAction()
    {
        $array = array();
        
        $array['page'] = (int) $this->params('page', 1);
        $orderId = (int) $this->params('order_id');
        //订单信息
        $array['order_info'] = $this->getDbshopTable()->infoOrder(array('order_id'=>$orderId));
        if(!$array['order_info']) return $this->redirect()->toRoute('orders/default', array('action'=>'shiplist', 'controller'=>'Orders'));
        
        //订单配送信息
        $array['delivery_address'] = $this->getDbshopTable('OrderDeliveryAddressTable')->infoDeliveryAddress(array('order_id'=>$orderId));
        
        //订单商品
        $array['order_goods'] = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=>$orderId));
        
        //订单操作历史
        $array['order_log'] = $this->getDbshopTable('OrderLogTable')->listOrderLog(array('order_id'=>$orderId));
        
        //物流状态信息
        if($array['order_info']['order_state'] >= 40 and $array['delivery_address']['express_number'] != '') {
            $iniReader   = new \Zend\Config\Reader\Ini();
            $expressPath = DBSHOP_PATH . '/data/moduledata/Express/';
            if(file_exists($expressPath . $array['order_info']['express_id'] . '.ini')) {
                $expressIni = $iniReader->fromFile($expressPath . $array['order_info']['express_id'] . '.ini');
                $array['express_url'] = $expressIni['express_url'];
                if(is_array($expressIni) and $expressIni['express_name_code'] != '' and file_exists($expressPath . 'express.xml')) {
                    $xmlReader    = new \Zend\Config\Reader\Xml();
                    $expressArray = $xmlReader->fromFile($expressPath . 'express.xml');
                    if(!empty($expressArray)) {
                        $array['express_state_array'] = $this->getServiceLocator()->get('shop_express_state')->getExpressStateContent($expressArray, $expressIni['express_name_code'], $array['delivery_address']['express_number']);
                    }
                }
            }
        }
        
        return $array;
    }
    /**
     * 导出发货单
     * @return array
     */
    public function exportShipAction()
    {
        $array = array();

        if($this->request->isPost()) {
            $exportArray = $this->request->getPost()->toArray();
            $shipArray   = $this->getExportShipArray($exportArray);
            if(is_array($shipArray) and !empty($shipArray)) {
                $this->exportShipExcel($shipArray, $exportArray);
            }
        }

        $array['express_array'] = $this->getDbshopTable('ExpressTable')->listExpress();

        return $array;
    }
    /** 
     * 支付記錄
     * @return multitype:NULL
     */
    public function paylogAction()
    {
        $array = array();
        
        //支付列表
        $page = $this->params('page',1);
        $array['pay_list'] = $this->getDbshopTable()->listOrder(array('page'=>$page, 'page_num'=>20), array(), array('dbshop_order.order_state>=20'));

        return $array;
    }
    /**
     * 修改订单金额，添加历史
     */
    public function saveorderamountAction()
    {
        $orderId = (int) $this->params('order_id');
        $state   = 'false';
        if($this->request->isPost()) {
            $postArray = $this->request->getPost()->toArray();
            if($postArray['order_id'] == $orderId) {
                $orderInfo = $this->getDbshopTable()->infoOrder(array('order_id'=>$orderId));
                if($orderInfo->order_state < 15 or ($orderInfo->order_state == 30 and $orderInfo->pay_code == 'hdfk')) {
                    if($postArray['order_edit_amount_type'] == '-' and $postArray['order_edit_amount'] > $orderInfo->order_amount) {
                        exit('false');
                    }

                    $postArray['order_edit_amount'] = $postArray['order_edit_amount_type'].$postArray['order_edit_amount'];

                    $orderAmountLog = array();
                    $orderAmountLog['order_edit_amount']     = number_format($orderInfo->order_amount + $postArray['order_edit_amount'], 2, '.', '');
                    $orderAmountLog['order_original_amount'] = $orderInfo->order_amount;
                    $orderAmountLog['order_edit_amount_type']= $postArray['order_edit_amount_type'];
                    $orderAmountLog['order_edit_number']     = $postArray['order_edit_amount'];
                    $orderAmountLog['order_edit_amount_user']= $this->getServiceLocator()->get('adminHelper')->returnAuth('admin_name');
                    $orderAmountLog['order_edit_amount_info']= $postArray['order_edit_amount_info'];
                    $orderAmountLog['order_id']              = $orderId;

                    if($this->getDbshopTable('OrderAmountLogTable')->addOrderAmountLog($orderAmountLog)) {
                        if($this->getDbshopTable('OrderTable')->updateOrder(array('order_amount'=>$orderAmountLog['order_edit_amount']), array('order_id'=>$orderId))) {
                            $state = 'true';
                        }
                    }
                }
            }
        }
        exit($state);
    }
    /**
     * 快递单号管理
     * @return array
     */
    public function expressNumberAction()
    {
        $array = array();

        $array['express_array'] = $this->getDbshopTable('ExpressTable')->orderExpressList();

        return $array;
    }
    /**
     * 单独添加快递单号
     * @return \Zend\Http\Response
     */
    public function addExpressNumberAction()
    {
        if($this->request->isPost()) {
            $postArray           = $this->request->getPost()->toArray();
            $expressNumberArray  = explode("\r\n", $postArray['express_number']);
            if(is_array($expressNumberArray) and !empty($expressNumberArray)) {
                foreach($expressNumberArray as $expressValue) {
                    $expressArray = array();
                    $expressArray['express_number'] = $expressValue;
                    $expressArray['express_id']     = $postArray['express_id'];
                    $this->getDbshopTable('ExpressNumberTable')->addExpressNumber($expressArray);
                }
            }
        }
        return $this->redirect()->toRoute('orders/default', array('action'=>'expressNumber', 'controller'=>'Orders'));
    }
    /**
     * 快递单号列表
     * @return array
     */
    public function expressNumberListAction()
    {
        $expressId = (int)$this->params('express_id', 0);
        if($expressId == 0) return $this->redirect()->toRoute('orders/default', array('action'=>'expressNumber', 'controller'=>'Orders'));

        $array = array();

        $array['express_info'] = $this->getDbshopTable('ExpressTable')->infoExpress(array('express_id'=>$expressId));

        //快递单号列表
        $page = $this->params('page',1);
        $array['number_list'] = $this->getDbshopTable('ExpressNumberTable')->listExpressNumber(array('page'=>$page, 'page_num'=>20), array('express_id'=>$expressId));

        return $array;
    }
    /**
     * 删除快递单号
     */
    public function delExpressNumberAction()
    {
        $expressNumberId = (int) $this->request->getPost('express_number_id');
        if($expressNumberId) {
            $expressNumberInfo = $this->getDbshopTable('ExpressNumberTable')->infoExpressNumber(array('express_number_id'=>$expressNumberId));
            if($expressNumberInfo->express_number_state == 1) exit('false');

            if($this->getDbshopTable('ExpressNumberTable')->delExpressNumber(array('express_number_id'=>$expressNumberId))) exit('true');
        }
        exit('false');
    }
    /**
     * 批量导入快递订单号
     * @return array
     */
    public function importExpressNumberAction()
    {
        $expressId = $this->request->getQuery('express_id');

        if(!empty($_FILES['import_excel_file']['tmp_name'])) {
            $importExpressId = $this->request->getPost('express_id');
            require_once DBSHOP_PATH . '/module/Upload/src/Upload/Plugin/Phpexcel/PHPExcel/Reader/Excel2007.php';
            $excelReader = new \PHPExcel_Reader_Excel2007();
            $objPHPExcel = $excelReader->load($_FILES['import_excel_file']['tmp_name']);
            $currentSheet= $objPHPExcel->getSheet(0);
            $lineNum     = $currentSheet->getHighestRow();
            for($num=2;$num<=$lineNum;$num++) {
                $expressNumberArray = array();
                $expressNumberArray['express_number'] = $currentSheet->getCell('A'.$num)->getValue();
                $expressNumberArray['express_id']     = $importExpressId;
                if($this->getDbshopTable('ExpressNumberTable')->infoExpressNumber($expressNumberArray)) {
                    continue;
                }
                $this->getDbshopTable('ExpressNumberTable')->addExpressNumber($expressNumberArray);
            }
            return $this->redirect()->toRoute('orders/default', array('action'=>'expressNumber', 'controller'=>'Orders'));
        }

        $array = array();
        $array['express_id']    = $expressId;
        $array['express_array'] = $this->getDbshopTable('ExpressTable')->listExpress();

        return $array;
    }
    /**
     * 批量导入例子文档下载
     */
    public function exampleExcelAction()
    {
        $downloadFile = DBSHOP_PATH . '/data/moduledata/Express/Example.xlsx';
        $fileName     = basename($downloadFile);

        $file = fopen($downloadFile,"r");
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: ".filesize($downloadFile));
        Header("Content-Disposition: attachment; filename=".$fileName);
        echo fread($file, filesize($downloadFile));
        fclose($file);
        exit;
    }
    /**
     * 获取需要导出的发货单信息
     * @param array $exportArray
     * @return array
     */
    private function getExportShipArray(array $exportArray)
    {
        $shipArray = array();
        $whereArray= array();

        //发货状态
        $shipState = array(
            'all_ship'=> '(o.order_state >= 20 or o.pay_code="hdfk")',//全部发货单
            'no_ship' => '(o.order_state >= 20 or o.pay_code="hdfk") and o.order_state < 40',//未发货的发货单
            'yes_ship'=> 'o.order_state = 40'//已发货，但未收货
        );
        $whereArray[] = $shipState[$exportArray['order_ship_state']];

        //开始时间与结束时间
        $whereArray[] = (isset($exportArray['export_start_time']) and !empty($exportArray['export_start_time'])) ? 'o.order_time >= ' . strtotime($exportArray['export_start_time']) : '';
        $whereArray[] = (isset($exportArray['export_end_time'])   and !empty($exportArray['export_end_time']))   ? 'o.order_time <= ' . strtotime($exportArray['export_end_time'])   : '';

        //配送方式
        $whereArray[] = (isset($exportArray['express_id']) and !empty($exportArray['express_id'])) ? 'o.express_id=' . $exportArray['express_id'] : '';

        //去除数组中的空值
        $whereArray = array_filter($whereArray);

        $shipArray = $this->getDbshopTable('OrderDeliveryAddressTable')->listExportDeliveryaddressArray($whereArray);

        return $shipArray;
    }
    /**
     * 导出发货单的Excel形式
     * @param array $shipArray
     * @param array $exportArray
     */
    private function exportShipExcel(array $shipArray, array $exportArray)
    {
        if(empty($shipArray)) return ;

        require_once DBSHOP_PATH . '/module/Upload/src/Upload/Plugin/Phpexcel/PHPExcel.php';
        $dbshopExcel = new \PHPExcel();

        //设置一般属性，在excel表中无法看到类似信息
        $dbshopExcel->getProperties()
        ->setCreator('DBShop')
        ->setLastModifiedBy('DBShop')
        ->setTitle($this->getDbshopLang()->translate('发货单'))
        ->setSubject('Office 2007 XLSX DBShop Document')
        ->setDescription('DBShop document for Office 2007 XLSX, generated using PHP classes.')
        ->setKeywords('office 2007 openxml php')
        ->setCategory('DBShop result file');

        /*----------------------excel内容-------------------------*/
        $sheetIndex = $dbshopExcel->setActiveSheetIndex(0);//设置使用第一个表
        //设置宽度
        $sheetIndex->getColumnDimension('B')->setWidth(40);
        $sheetIndex->getColumnDimension('C')->setWidth(30);
        $sheetIndex->getColumnDimension('E')->setWidth(20);
        $sheetIndex->getColumnDimension('F')->setWidth(20);
        $sheetIndex->getColumnDimension('G')->setWidth(20);
        //设置首行字体
        $sheetIndex->getStyle('A1:Z1')->applyFromArray(
            array('font'=>array(
                'bold'=>true
            ))
        );
        $sheetIndex->getStyle('A:Z')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheetIndex->getStyle('A:Z')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);;

        $sheetIndex->setCellValue('A1', $this->getDbshopLang()->translate('收货人'));
        $sheetIndex->setCellValue('B1', $this->getDbshopLang()->translate('收货地址'));
        $sheetIndex->setCellValue('C1', $this->getDbshopLang()->translate('联系方式'));
        $sheetIndex->setCellValue('D1', $this->getDbshopLang()->translate('邮政编码'));
        $sheetIndex->setCellValue('E1', $this->getDbshopLang()->translate('送货时间'));
        $sheetIndex->setCellValue('F1', $this->getDbshopLang()->translate('付款方式'));
        $sheetIndex->setCellValue('G1', $this->getDbshopLang()->translate('应收货款(包括运费)'));
        //可选导出
        $selectAzArray = array('H', 'I', 'J', 'K', 'L', 'M', 'N');
        $languageArray = array(
            'express_name'  => $this->getDbshopLang()->translate('快递信息'),
            'express_fee'   => $this->getDbshopLang()->translate('配送费用'),
            'express_number'=> $this->getDbshopLang()->translate('快递单号'),
            'goods_name'    => $this->getDbshopLang()->translate('商品名称'),
            'goods_item'    => $this->getDbshopLang()->translate('商品货号'),
            'goods_extend_info' => $this->getDbshopLang()->translate('商品属性'),
            'buy_num'       => $this->getDbshopLang()->translate('购买数量')
        );
        $selectExportState = false;//是否有可选值
        $array = array();//用于获取可导出属性对应的大写字母
        $goodsExport = array();

        if(isset($exportArray['select_export_value']) and !empty($exportArray['select_export_value'])) {
            foreach($exportArray['select_export_value'] as $selectKey => $selectValue) {
                $sheetIndex->setCellValue($selectAzArray[$selectKey].'1', $languageArray[$selectValue]);

                $array[$selectValue] = $selectAzArray[$selectKey];//用于下面的循环

                if(in_array($selectValue, array('goods_name', 'goods_item', 'goods_extend_info', 'buy_num'))) {//用户下面的商品信息
                    $goodsExport[] = $selectValue;
                }
            }
            $selectExportState = true;
        }

        //设置个别宽度
        if(isset($array['goods_name'])) $sheetIndex->getColumnDimension($array['goods_name'])->setWidth(40);
        if(isset($array['goods_extend_info'])) $sheetIndex->getColumnDimension($array['goods_extend_info'])->setWidth(20);
        if(isset($array['express_number'])) $sheetIndex->getColumnDimension($array['express_number'])->setWidth(20);
        //设置对齐
        if(isset($array['buy_num'])) $sheetIndex->getStyle($array['buy_num'])->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $indexM = 2;
        foreach($shipArray as $shipValue) {
            $telStr = '';
            if(!empty($shipValue['tel_phone'])) $telStr .= $this->getDbshopLang()->translate('座机').':'.$shipValue['tel_phone'];
            if(!empty($shipValue['mod_phone'])) $telStr .= ($telStr != '' ? "\r\n" : '') . $this->getDbshopLang()->translate('手机').':'.$shipValue['mod_phone'];

            $address = '';
            $address = $shipValue['region_address'].', ';
            $regionArray = @explode(' ', $shipValue['region_info']);
            $regionArray = array_reverse($regionArray);
            $address .= implode(', ', $regionArray);

            $sheetIndex->setCellValue("A{$indexM}", $shipValue['delivery_name']);
            $sheetIndex->setCellValue("B{$indexM}", $address);
            $sheetIndex->setCellValue("C{$indexM}", $telStr);
            $sheetIndex->setCellValue("D{$indexM}", $shipValue['zip_code']);
            $sheetIndex->setCellValue("E{$indexM}", $shipValue['express_time_info']);
            $sheetIndex->setCellValue("F{$indexM}", $shipValue['pay_name'].'['.(!empty($shipValue['pay_time']) ? $this->getDbshopLang()->translate('已付款') : $this->getDbshopLang()->translate('未付款')).']');
            $sheetIndex->setCellValue("G{$indexM}", $shipValue['currency_symbol'].$shipValue['order_amount']);

            if($selectExportState) {//非商品信息的可选内容
                if(isset($array['express_name'])) {//配送名称
                    $sheetIndex->setCellValue($array['express_name'].$indexM, $shipValue['express_name']);
                }
                if(isset($array['express_fee'])) {//配送费用
                    $sheetIndex->setCellValue($array['express_fee'].$indexM, $shipValue['express_fee']);
                }
                if(isset($array['express_number'])) {//配送单号
                    $sheetIndex->setCellValueExplicit($array['express_number'].$indexM, $shipValue['express_number'], \PHPExcel_Cell_DataType::TYPE_STRING);
                }
                //商品信息获取
                if(!empty($goodsExport)) {
                    $goodsArray = array();
                    $goodsArray = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=>$shipValue['order_id']));
                    if(is_array($goodsArray) and !empty($goodsArray)) {
                        $goodsNum = count($goodsArray)-1;
                        $indexAddM = $indexM + $goodsNum;
                        if($goodsNum > 0) {
                            $sheetIndex->mergeCells("A{$indexM}:A".$indexAddM);
                            $sheetIndex->mergeCells("B{$indexM}:B".$indexAddM);
                            $sheetIndex->mergeCells("C{$indexM}:C".$indexAddM);
                            $sheetIndex->mergeCells("D{$indexM}:D".$indexAddM);
                            $sheetIndex->mergeCells("E{$indexM}:E".$indexAddM);
                            $sheetIndex->mergeCells("F{$indexM}:F".$indexAddM);
                            $sheetIndex->mergeCells("G{$indexM}:G".$indexAddM);
                            if(isset($array['express_name'])) $sheetIndex->mergeCells($array['express_name'].$indexM.':'.$array['express_name'].$indexAddM);//对于行的上下合并
                            if(isset($array['express_fee'])) $sheetIndex->mergeCells($array['express_fee'].$indexM.':'.$array['express_fee'].$indexAddM);
                            if(isset($array['express_number'])) $sheetIndex->mergeCells($array['express_number'].$indexM.':'.$array['express_number'].$indexAddM);
                        }

                        $goodsIndex = $indexM;
                        foreach($goodsArray as $goodsVlue) {
                            if(isset($array['goods_name']))         $sheetIndex->setCellValue($array['goods_name'].$goodsIndex, $goodsVlue['goods_name']);
                            if(isset($array['goods_item']))         $sheetIndex->setCellValue($array['goods_item'].$goodsIndex, $goodsVlue['goods_item']);
                            if(isset($array['goods_extend_info']))  $sheetIndex->setCellValue($array['goods_extend_info'].$goodsIndex, strip_tags($goodsVlue['goods_extend_info']));
                            if(isset($array['buy_num']))            $sheetIndex->setCellValue($array['buy_num'].$goodsIndex, $goodsVlue['buy_num']);
                            $goodsIndex++;
                        }
                        $indexM = $indexAddM;
                    }
                }
            }
            $indexM++;
        }
        /*----------------------excel内容-------------------------*/

        //生成excel表格
        $dbshopExcel->getActiveSheet()->setTitle($this->getDbshopLang()->translate('发货单'));

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Ship_'.date("Y-m-d", time()).'.xlsx"');
        header('Cache-Control: max-age=0');
        \PHPExcel_IOFactory::createWriter($dbshopExcel, 'Excel2007')->save('php://output');
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName='OrderTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}
