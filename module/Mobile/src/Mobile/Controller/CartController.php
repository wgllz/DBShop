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

namespace Mobile\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class CartController extends AbstractActionController
{
    private $dbTables = array();
    private $translator;

    /**
     * 购物车
     * @return array|multitype
     */
    public function indexAction()
    {
        $array = array();

        $this->layout()->title_name = $this->getDbshopLang()->translate('购物车');

        $array['cart_array'] = $this->getServiceLocator()->get('frontHelper')->getCartSession();

        //用户优惠与积分中的计算
        $array = $this->promotionsOrIntegralFun($array);

        return $array;
    }
    public function setaddressAction()
    {
        //判断是否已经登录或被删除
        $this->checkUserLoginOrDelete();

        $array = array();
        $this->layout()->title_name = $this->getDbshopLang()->translate('选择收货地址');
        //购物车商品
        $array['cart_array'] = $this->getServiceLocator()->get('frontHelper')->getCartSession();
        if(count($array['cart_array']) <= 0) return $this->redirect()->toRoute('mobile/default');

        //收货地址
        $array['address_list'] = $this->getDbshopTable('UserAddressTable')->listAddress(array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));

        return $array;
    }
    public function addAddressAction()
    {
        //判断是否已经登录或被删除
        $this->checkUserLoginOrDelete();

        $array = array();
        $this->layout()->title_name = $this->getDbshopLang()->translate('添加收货地址');

        if($this->request->isPost()) {
            $addressId    = (int) $this->request->getPost('address_id');
            $addressArray = $this->request->getPost()->toArray();
            $addressArray['user_id'] = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
            if($this->getDbshopTable('UserAddressTable')->addAddress($addressArray)) return $this->redirect()->toRoute('m_cart/default', array('action'=>'setaddress'));
        }

        $array['region_array']= $this->getDbshopTable('RegionTable')->listRegion(array('dbshop_region.region_top_id=0'));

        return $array;
    }
    /**
     * 购物车第三步，选择配送方式、支付方式、确认订单
     * @return number|multitype:multitype:Ambigous <multitype:, \Zend\Config\Reader\mixed, string>
     */
    public function stepAction ()
    {
        //判断是否已经登录或被删除
        $this->checkUserLoginOrDelete();
        $array = array();
        $this->layout()->title_name = $this->getDbshopLang()->translate('订单确认');

        //购物车商品
        $array['cart_array'] = $this->getServiceLocator()->get('frontHelper')->getCartSession();
        $step = trim($this->request->getPost('step'));
        if(count($array['cart_array']) <= 0 or $step != 'setaddress') return $this->redirect()->toRoute('mobile/default');
        $cartTotalPrice = $this->getServiceLocator()->get('frontHelper')->getCartTotal();

        /*----------------------配送方式与收货地址----------------------*/
        //商品总重量
        $array['total_weight'] = $this->getServiceLocator()->get('frontHelper')->getCartTotalWeight();
        //获取配送地址信息
        $addressId    = intval($this->request->getPost('user_address_id'));

        $addressInfo  = $this->getDbshopTable('UserAddressTable')->infoAddress(array('address_id'=>$addressId));
        if(!$addressInfo) return $this->redirect()->toRoute('shopfront/default');
        $array['address_info'] = $addressInfo;
        //获取配送地址对应的地区
        $regionInfo   = $this->getDbshopTable('RegionTable')->infoRegion(array('dbshop_region.region_id'=>$addressInfo['region_id']));
        $regionIdArray= explode(',', $regionInfo['region_path']);

        //获取配送方式信息
        $configReader = new \Zend\Config\Reader\Ini();
        $expressArray = array();
        $cashOnDelivery= '';
        $expressArray = $this->getDbshopTable('ExpressTable')->listExpress(array('express_state'=>1));
        if(is_array($expressArray) and !empty($expressArray)) {
            $expressConfigFilePath = DBSHOP_PATH . '/data/moduledata/Express/';
            $i = 0;
            $totalWeight = $array['total_weight'] * 1000;//将千克换算成克
            foreach ($expressArray as $value) {//循环读取配送方式的设置文件
                $expressExist = false;
                if(file_exists($expressConfigFilePath . $value['express_id'] . '.ini')) {
                    $expressConfig = $configReader->fromFile($expressConfigFilePath . $value['express_id'] . '.ini');
                    if($expressConfig['express_set'] == 'T') {//当为统一设置时进行的处理
                        $value['express_price'] = $this->getServiceLocator()->get('shop_express')->calculateCost(trim($expressConfig['express_price']), $totalWeight, $cartTotalPrice);
                        $expressExist = true;
                    } else {//当为个性化设置的时候，进行地区匹配
                        $value['express_price'] = 0;
                        foreach ($expressConfig['express_price'] as $price_value) {
                            $price_value['price'] = $this->getServiceLocator()->get('shop_express')->calculateCost(trim($price_value['price']), $totalWeight, $cartTotalPrice);
                            foreach ($regionIdArray as $regionId) {//循环查询，当前配送地址的地区id及其上级地区，是否在配送地区中有所体现，这里的foreach有点多了，待优化
                                if(in_array($regionId, $price_value['area_id'])) {
                                    $value['express_price'] = ($value['express_price'] > 0 and $value['express_price'] < $price_value['price']) ? $value['express_price'] : $price_value['price'];
                                    $expressExist = true;
                                }
                            }
                        }
                    }
                    //这是获取可以货到付款的配送方式的id字符串相连
                    if($value['cash_on_delivery'] == 1) $cashOnDelivery .= "'".$value['express_id'] . "',";
                }
                if($expressExist) {
                    $array['express_array'][$i] = $value;
                    $i++;
                }
            }
        }
        if(!empty($array['express_array'])) $array['express_array'][0]['selected'] = 1;
        $array['cash_on_delivery_str'] = (!empty($cashOnDelivery) ? substr($cashOnDelivery, 0, -1) : '');
        /*----------------------配送方式与收货地址----------------------*/

        /*----------------------支付方式----------------------*/
        $xmlReader    = new \Zend\Config\Reader\Xml();
        $paymentArray = array();
        $xmlPath      = DBSHOP_PATH . '/data/moduledata/Payment/';
        if(is_dir($xmlPath)) {
            $dh = opendir($xmlPath);
            while (false !== ($fileName = readdir($dh))) {
                if($fileName != '.' and $fileName != '..' and $fileName != '.DS_Store' and $fileName != 'wxpay.xml') {
                    $paymentInfo = $xmlReader->fromFile($xmlPath . '/' . $fileName);

                    //判断是否符合当前的货币要求
                    $currencyState = false;
                    if(isset($paymentInfo['payment_currency']['checked']) and !empty($paymentInfo['payment_currency']['checked'])) {
                        $currencyArray = is_array($paymentInfo['payment_currency']['checked']) ? $paymentInfo['payment_currency']['checked'] : array($paymentInfo['payment_currency']['checked']);
                        $currencyState = in_array($this->getServiceLocator()->get('frontHelper')->getFrontDefaultCurrency(), $currencyArray) ? true : false;
                    } elseif (in_array($paymentInfo['editaction'], array('xxzf', 'hdfk'))) {//线下支付或者货到付款时，不进行货币判断
                        $currencyState = true;
                    }

                    if($paymentInfo['payment_state']['checked'] == 1 and $currencyState) {
                        $paymentInfo['payment_fee']['content'] = ((strpos($paymentInfo['payment_fee']['content'], '%') !== false) ? round($cartTotalPrice * str_replace('%', '', $paymentInfo['payment_fee']['content'])/100, 2) : round($this->getServiceLocator()->get('frontHelper')->shopPrice($paymentInfo['payment_fee']['content']), 2));
                        $paymentArray[] = $paymentInfo;
                    }
                }
            }
        }
        //排序操作
        usort($paymentArray, function ($a, $b) {
            if($a['payment_sort']['content'] == $b['payment_sort']['content']) {
                return 0;
            }
            return ($a['payment_sort']['content'] < $b['payment_sort']['content']) ? -1 : 1;
        });
        $array['payment'] = $paymentArray;
        if(!empty($array['payment'])) $array['payment'][0]['selected'] = 1;
        /*----------------------支付方式----------------------*/

        //用户优惠与积分中的计算
        $array = $this->promotionsOrIntegralFun($array);
        $array['promotionsCost'] = $this->getServiceLocator()->get('frontHelper')->shopPrice($array['promotionsCost']['discountCost']);

        //支付费用已经进行了汇率转换无需再次转换，配送费用未转换需要转换
        $array['order_total'] = $this->getServiceLocator()->get('frontHelper')->shopPrice($array['express_array'][0]['express_price']) + $array['payment'][0]['payment_fee']['content'] + $cartTotalPrice - $array['promotionsCost'];


        return $array;
    }
    /**
     * 订单提交
     */
    public function submitAction()
    {
        //判断是否已经登录或被删除
        $this->checkUserLoginOrDelete();

        $this->layout()->title_name  = $this->getDbshopLang()->translate('订单完成');
        //购物车商品
        $array['cart_array'] = $this->getServiceLocator()->get('frontHelper')->getCartSession();
        $step = trim($this->request->getPost('step'));
        if(count($array['cart_array']) <= 0 or $step != 'step') return $this->redirect()->toRoute('mobile/default');

        $postArray = $this->request->getPost()->toArray();
        $cartTotalPrice = $this->getServiceLocator()->get('frontHelper')->getCartTotal();

        $paymentArray = array();
        //获取支付方式信息
        if(file_exists(DBSHOP_PATH . '/data/moduledata/Payment/' . $postArray['pyament_code'] . '.xml')) {
            $xmlReader    = new \Zend\Config\Reader\Xml();
            $paymentArray = $xmlReader->fromFile(DBSHOP_PATH . '/data/moduledata/Payment/' . $postArray['pyament_code'] . '.xml');
            $postArray['pay_name']    = $paymentArray['payment_name']['content'];
            $postArray['order_state'] = $paymentArray['orders_state'];

            //获取支付方式的手续费用，虽然在上一页面有传值过来，但是因为html有可能被恶意更改，因此这里从新获取计算
            $paymentFee = (!empty($paymentArray['payment_fee']['content']) ? ((strpos($paymentArray['payment_fee']['content'], '%') !== false) ? round($cartTotalPrice * str_replace('%', '', $paymentArray['payment_fee']['content'])/100, 2) : round($this->getServiceLocator()->get('frontHelper')->shopPrice($paymentArray['payment_fee']['content']), 2)) : 0);
        }
        //获取配送方式信息
        $expressArray = array();
        $expressArray = $this->getDbshopTable('ExpressTable')->infoExpress(array('express_id'=>$postArray['express_id'], 'express_state'=>1));
        $postArray['express_id']   = $expressArray->express_id;
        $postArray['express_name'] = $expressArray->express_name;
        //配送费用获取
        $addressInfo  = $this->getDbshopTable('UserAddressTable')->infoAddress(array('address_id'=>$postArray['address_id']));//对应下面的收货地址保存

        $regionInfo   = $this->getDbshopTable('RegionTable')->infoRegion(array('dbshop_region.region_id'=>$addressInfo['region_id']));
        $regionIdArray= explode(',', $regionInfo['region_path']);

        $totalWeight = $this->getServiceLocator()->get('frontHelper')->getCartTotalWeight() * 1000;//将千克换算成克

        $configReader = new \Zend\Config\Reader\Ini();
        $expressConfigFilePath = DBSHOP_PATH . '/data/moduledata/Express/';
        $expressConfig = $configReader->fromFile($expressConfigFilePath . $postArray['express_id'] . '.ini');
        if($expressConfig['express_set'] == 'T') {//当为统一设置时进行的处理
            $expressPrice = $this->getServiceLocator()->get('shop_express')->calculateCost(trim($expressConfig['express_price']), $totalWeight, $cartTotalPrice);
        } else {
            foreach ($expressConfig['express_price'] as $priceValue) {
                $priceValue['price'] = $this->getServiceLocator()->get('shop_express')->calculateCost(trim($priceValue['price']), $totalWeight, $cartTotalPrice);
                foreach ($regionIdArray as $regionId) {
                    $expressPrice = ($expressPrice > 0 and $expressPrice < $priceValue['price']) ? $expressPrice : $priceValue['price'];
                }
            }
        }
        $expressPrice = $this->getServiceLocator()->get('frontHelper')->shopPrice($expressPrice);

        //用户优惠与积分中的计算
        $array = $this->promotionsOrIntegralFun($array);
        $array['promotionsCost'] = $this->getServiceLocator()->get('frontHelper')->shopPrice($array['promotionsCost']['discountCost']);

        /*----------------------订单相关信息保存----------------------*/
        //开启数据库事务处理
        $this->getDbshopTable('dbshopTransaction')->DbshopTransactionBegin();

        //对post过来的价格信息进行重置，以防止恶意使用者修改html中的数值
        $postArray['goods_total_price'] = $cartTotalPrice;
        $postArray['order_total_price'] = $expressPrice + $paymentFee + $cartTotalPrice - $array['promotionsCost'];
        $postArray['pay_price']         = $paymentFee;
        $postArray['express_price']     = $expressPrice;
        $postArray['buy_pre_price']     = $array['promotionsCost'];
        $postArray['user_pre_price']    = $postArray['user_pre_price'];
        $postArray['integral_num']      = $array['integralInfo']['integralNum'];
        $postArray['integral_rule_info']= $array['integralInfo']['integalRuleInfo'];
        $postArray['integral_type_2_num']          = $array['integralInfo1']['integralNum'];
        $postArray['integral_type_2_num_rule_info']= $array['integralInfo1']['integalRuleInfo'];

        $orderArray = $this->orderSave($postArray);
        $orderId    = $orderArray['order_id'];

        //保存收货地址
        $this->orderDeliveryAddressSave($addressInfo, array('order_id'=>$orderId,'shipping_time'=>$postArray['shipping_time'], 'express_name'=>$postArray['express_name'], 'express_id'=>$postArray['express_id'], 'express_fee'=>$postArray['express_price']));

        //保存订单中的商品信息
        $goodsSerialize  = array();
        $goodsStockError = array();
        foreach ($array['cart_array'] as $cart_key => $cart_value) {
            $orderGoodsId = $this->orderGoodsSave($cart_value, array('order_id'=>$orderId));
            if($orderGoodsId != -1) {//库存正确处理
                $goodsSerialize[$orderGoodsId] = array(
                    'goods_id'          => $cart_value['goods_id'],
                    'class_id'          => $cart_value['class_id'],
                    'goods_name'        => $cart_value['goods_name'],
                    'goods_extend_info' => $cart_value['goods_color_name'] . $cart_value['goods_size_name'],
                    'goods_image'       => $cart_value['goods_image'],
                    'goods_shop_price'  => $this->getServiceLocator()->get('frontHelper')->shopPrice($cart_value['goods_shop_price']),
                    'buy_num'           => $cart_value['buy_num'],
                    'goods_color'       => isset($cart_value['goods_color']) ? $cart_value['goods_color'] : '',
                    'goods_size'        => isset($cart_value['goods_size']) ? $cart_value['goods_size'] : ''
                );
            } else {
                $goodsStockError[] = $cart_value['goods_name'];
            }
        }
        //判断库存是否不足，如果不足，则启用事务回滚功能
        if(!empty($goodsStockError)) {
            $this->getDbshopTable('dbshopTransaction')->DbshopTransactionRollback();//事务回滚
            $errorMessage = implode('<br>', $goodsStockError) . '<br>' . $this->getDbshopLang()->translate('商品库存不足') . '<a href="'.$this->url()->fromRoute('m_cart/default').'">' . $this->getDbshopLang()->translate('去购物车中删除库存不足的商品') . '</a>';
            exit($errorMessage);
        } else {
            $this->getDbshopTable('dbshopTransaction')->DbshopTransactionCommit();//事务确认
        }

        $this->getDbshopTable('OrderTable')->updateOrder(array('goods_serialize'=>serialize($goodsSerialize)), array('order_id'=>$orderId));
        //清空购物车操作
        $this->getServiceLocator()->get('frontHelper')->clearCartSession();
        /*----------------------订单相关信息保存----------------------*/

        $array['order_sn']    = $orderArray['order_sn'];
        $array['order_id']    = $orderId;
        $array['order_state'] = $postArray['order_state'];
        $array['order_total'] = $this->getServiceLocator()->get('frontHelper')->shopPriceSymbol() . $postArray['order_total_price'] . $this->getServiceLocator()->get('frontHelper')->shopPriceUnit();

        /*----------------------提醒信息发送----------------------*/
        $sendMessageBody = $this->getServiceLocator()->get('frontHelper')->getSendMessageBody('submit_order');
        if($sendMessageBody != '') {
            $sendArray = array();
            $sendArray['shopname']      = $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name');
            $sendArray['buyname']       = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name');
            $sendArray['ordersn']       = $array['order_sn'];
            $sendArray['submittime']    = $orderArray['order_time'];
            $sendArray['shopurl']       = 'http://' . $this->getRequest()->getServer('SERVER_NAME') . $this->url()->fromRoute('shopfront/default');

            $sendArray['subject']       = $sendArray['shopname'] . $this->getDbshopLang()->translate('提交订单提醒');
            $sendArray['send_mail'][]   = $this->getServiceLocator()->get('frontHelper')->getSendMessageBuyerEmail('submit_order_state', $this->getServiceLocator()->get('frontHelper')->getUserSession('user_email'));
            $sendArray['send_mail'][]   = $this->getServiceLocator()->get('frontHelper')->getSendMessageAdminEmail('submit_order_state');

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
        /*----------------------提醒信息发送----------------------*/

        return $array;
    }
    /**
     * 订单商品保存
     * @param array $goodsArray
     * @param array $data
     */
    private function orderGoodsSave (array $goodsArray, array $data)
    {
        //判断库存是否正常，如不正常，停止该次操作返回 -1
        if(!$this->goodsStockOper($goodsArray)) {
            return -1;
        }
        //正常，继续往下处理
        $array = array();
        $array['order_id']          = $data['order_id'];
        $array['goods_id']          = $goodsArray['goods_id'];
        $array['class_id']          = $goodsArray['class_id'];
        $array['goods_item']        = $goodsArray['goods_item'];
        $array['goods_name']        = $goodsArray['goods_name'];
        $array['goods_extend_info'] = ($goodsArray['goods_color_name'] != '' ? '<p>' . $goodsArray['goods_color_name'] . '</p>' : '') . ($goodsArray['goods_size_name'] != '' ? '<p>' . $goodsArray['goods_size_name'] . '</p>' : '');
        $array['goods_color']       = $goodsArray['goods_color'];
        $array['goods_size']        = $goodsArray['goods_size'];
        $array['goods_shop_price']  = $this->getServiceLocator()->get('frontHelper')->shopPrice($goodsArray['goods_shop_price']);
        $array['buy_num']           = $goodsArray['buy_num'];
        $array['goods_image']       = $goodsArray['goods_image'];
        $array['goods_amount']      = $this->getServiceLocator()->get('frontHelper')->shopPrice($goodsArray['goods_shop_price'] * $goodsArray['buy_num']);
        $array['goods_count_weight']= $goodsArray['goods_weight'] * $goodsArray['buy_num'];
        $array['buyer_id']          = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');

        return $this->getDbshopTable('OrderGoodsTable')->addOrderGoods($array);
    }
    /**
     * 库存操作处理
     * @param array $data
     * @return void|boolean
     */
    private function goodsStockOper(array $data)
    {
        $where       = array();
        $whereExtend = array();
        $stockNum    = 0;
        $extendState = false;//是否有扩展商品信息，默认是无

        $where['goods_id'] = $data['goods_id'];
        $goodsInfo         = $this->getDbshopTable('GoodsTable')->oneGoodsInfo($where);
        //当后台管理开启库存状态显示时，直接返回即可，无需扣除库存
        if($goodsInfo->goods_stock_state_open == 1) return true;
        //否则得到当前商品库存
        $stockNum          = $goodsInfo->goods_stock;
        //判断是否有属性，如果有属性获取扩展表中的商品信息
        if(isset($data['goods_color']) and !empty($data['goods_color']) and isset($data['goods_size']) and !empty($data['goods_size'])) {
            $whereExtend['goods_id']    = $data['goods_id'];
            $whereExtend['goods_color'] = $data['goods_color'];
            $whereExtend['goods_size']  = $data['goods_size'];
            $extendGoods                = $this->getDbshopTable('GoodsPriceExtendGoodsTable')->InfoPriceExtendGoods($whereExtend);
            if($extendGoods) {
                $stockNum    = $extendGoods->goods_extend_stock;//默认库存
                $extendState = true;//有扩展信息
            }
        }
        //判断库存是否符合要求，并于购物数量进行比较
        if($stockNum > 0 and $data['buy_num'] > 0 and $stockNum >= $data['buy_num']) {
            $stockNum = $stockNum - $data['buy_num'];
            if($extendState) {
                $this->getDbshopTable('GoodsPriceExtendGoodsTable')->updatePriceExtendGoods(array('goods_extend_stock'=>$stockNum), $whereExtend);
            } else {
                $this->getDbshopTable('GoodsTable')->oneUpdateGoods(array('goods_stock'=>$stockNum), $where);
            }
            return true;
        }

        return false;
    }
    /**
     * 保存订单收货地址
     * @param unknown $address
     * @param array $data
     */
    private function orderDeliveryAddressSave ($address, array $data)
    {
        $array = array();
        $array['order_id']      = $data['order_id'];
        $array['delivery_name'] = $address['true_name'];
        $array['region_id']     = $address['region_id'];
        $array['region_info']   = $address['region_value'];
        $array['region_address']= $address['address'];
        $array['zip_code']      = substr($address['zip_code'], 0, 6);
        $array['tel_phone']     = ($address['tel_area_code']=='' ? '' : $address['tel_area_code'] . '-') . $address['tel_phone'] . ($address['tel_ext']=='' ? '' : '-' . $address['tel_ext']);
        $array['mod_phone']     = $address['mod_phone'];
        $array['express_name']  = $data['express_name'];
        $array['express_time_info'] = $data['shipping_time'];
        $array['express_fee']   = $data['express_fee'];
        $array['express_id']    = $data['express_id'];

        $this->getDbshopTable('OrderDeliveryAddressTable')->addDeliveryAddress($array);
    }
    /**
     * 订单保存
     * @param array $orderArray
     * @return unknown
     */
    private function orderSave(array $orderArray)
    {
        $array = array();
        $array['order_id']            = '';
        $array['order_sn']            = $this->createOrderSn();
        $array['order_out_sn']        = '';//out' . $array['order_sn'];
        $array['goods_amount']        = (empty($orderArray['goods_total_price']) ? '0.00' : $orderArray['goods_total_price']);
        $array['order_amount']        = (empty($orderArray['order_total_price']) ? '0.00' : $orderArray['order_total_price']);
        $array['pay_fee']             = $orderArray['pay_price'];
        $array['express_fee']         = $orderArray['express_price'];
        $array['user_pre_fee']        = $orderArray['user_pre_price'];
        $array['user_pre_info']       = '';//$orderArray[''];
        $array['buy_pre_fee']         = $orderArray['buy_pre_price'];
        $array['goods_weight_amount'] = $orderArray['goods_count_weight'];
        $array['order_state']         = $orderArray['order_state'];
        $array['pay_code']            = $orderArray['pyament_code'];
        $array['pay_name']            = $orderArray['pay_name'];
        $array['express_id']          = $orderArray['express_id'];
        $array['buyer_id']            = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        $array['buyer_name']          = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name');
        $array['buyer_email']         = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_email');
        $array['express_name']        = $orderArray['express_name'];
        $array['order_time']          = time();
        $array['currency']            = $this->getServiceLocator()->get('frontHelper')->shopCurrency();
        $array['currency_symbol']     = $this->getServiceLocator()->get('frontHelper')->shopPriceSymbol();
        $array['currency_unit']       = $this->getServiceLocator()->get('frontHelper')->shopPriceUnit();
        $array['order_message']       = $orderArray['order_message'];
        $array['integral_num']        = $orderArray['integral_num'];
        $array['integral_rule_info']  = $orderArray['integral_rule_info'];
        $array['integral_type_2_num']            = $orderArray['integral_type_2_num'];
        $array['integral_type_2_num_rule_info']  = $orderArray['integral_type_2_num_rule_info'];

        $orderId = $this->getDbshopTable('OrderTable')->addOrder($array);

        return array('order_id'=>$orderId, 'order_sn'=>$array['order_sn'], 'order_time'=>$array['order_time']);
    }
    /**
     * 订单编号生成
     * @return mixed
     */
    private function createOrderSn()
    {
        /*$chars   = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");
        $orderSn = $chars[rand(0, 25)] . $chars[rand(0, 25)] . str_replace('.', '',microtime(true));*/

        //以数字为主的订单号码
        mt_srand((double) microtime() * 1000000);
        $orderSn = date('Ymd') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

        return $orderSn;
    }
    /**
     * 优惠打折与积分获取的统一方法
     * @param array $goodsCart
     * @return multitype:NULL
     */
    private function promotionsOrIntegralFun(array $array)
    {
        //用户优惠和积分中的计算
        $userGroup = $this->getServiceLocator()->get('frontHelper')->getUserSession('group_id');
        //优惠金额计算结果
        $array['promotionsCost'] = $this->getServiceLocator()->get('PromotionsRuleService')->promotionsRuleCalculation(array('cartGoods'=>$array['cart_array'], 'user_group'=>$userGroup));
        //获取积分计算结果
        $array['integralInfo']   = $this->getServiceLocator()->get('IntegralRuleService')->integralRuleCalculation(array('cartGoods'=>$array['cart_array'], 'user_group'=>$userGroup));     //消费积分
        $array['integralInfo1']   = $this->getServiceLocator()->get('IntegralRuleService')->integralRuleCalculation(array('cartGoods'=>$array['cart_array'], 'user_group'=>$userGroup), 2); //等级积分
        return $array;
    }
    /**
     * 判断会员是否已经登录或者已经被删除
     */
    private function checkUserLoginOrDelete()
    {
        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        //判断是否登录
        if($userId == '') return $this->redirect()->toRoute('m_user/default',array('action'=>'login'));

        //判断该用户是否在登录后，后台被管理员删除
        $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$userId));
        if($userInfo == null) {
            $userSession = new \Zend\Session\Container();
            $userSession->getManager()->getStorage()->clear('user_info');

            return $this->redirect()->toRoute('m_user/default', array('action'=>'login'));
        }
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