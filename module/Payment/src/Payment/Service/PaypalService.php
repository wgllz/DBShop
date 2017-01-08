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

namespace Payment\Service;
use Admin\Service\DbshopOpcache;
use Zend\Config\Writer\PhpArray;

/**
 * PayPal
 */
class PaypalService
{
    private $paymentConfig;
    private $paymentForm;
/*=============================上面为后台支付设置，下面为前台支付需要属性=================================*/
    private $nvpHeaderStr = '';
    
    /**
     * sandbox https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=
     * live    https://www.paypal.com/webscr&cmd=_express-checkout&token=
     */
    private $PAY_URL         = 'https://www.paypal.com/webscr&cmd=_express-checkout&token=';
    private $PAY_SANDBOX_URL = 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=';
    /**
     * sandbox https://api-3t.sandbox.paypal.com/nvp
     * live    https://api-3t.paypal.com/nvp
     */
    private $API_Endpoint         = 'https://api-3t.paypal.com/nvp';
    private $API_Sandbox_Endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
    
    private $API_UserName = '';
    private $API_Password = '';
    private $API_Signature= '';

    private $USE_PROXY    = FALSE;
    private $PROXY_HOST   = '127.0.0.1';
    private $PROXY_PORT   = '808';
    
    private $subject = '';
    private $AUTH_signature = '';
    private $AUTH_token     = '';
    private $AUTH_timestamp = '';
    private $version = '65.1';

    public function __construct ()
    {
        if (!$this->paymentConfig) {
            $this->paymentConfig = include(DBSHOP_PATH . '/data/moduledata/Payment/paypal.php');
        }
        if (!$this->paymentForm) {
            $this->paymentForm = new \Payment\Form\PaymentForm();
        }
        
        //设置paypal接口信息
        $this->API_UserName = $this->paymentConfig['payment_user']['content'];
        $this->API_Password = $this->paymentConfig['payment_passwd']['content'];
        $this->API_Signature = $this->paymentConfig['payment_sign']['content'];
        //当账户类型为sandbox时，进行sandbox账户设置
        if($this->paymentConfig['payment_type']['selected'] == 'sandbox') {
            $this->PAY_URL      = $this->PAY_SANDBOX_URL;
            $this->API_Endpoint = $this->API_Sandbox_Endpoint;
        }
    }
    /** 
     * 保存配置信息
     * @param array $data
     * @return unknown
     */
    public function savePaymentConfig (array $data)
    {
        $phpWriter = new PhpArray();
        $configArray = $this->paymentForm->setFormValue($this->paymentConfig, $data);
        $phpWriter->toFile(DBSHOP_PATH . '/data/moduledata/Payment/paypal.php', $configArray);

        //废除启用opcache时，在修改时，被缓存的配置
        DbshopOpcache::invalidate(DBSHOP_PATH . '/data/moduledata/Payment/paypal.php');

        return $configArray;
    }
    /**
     * 获取表单数组
     * @return multitype:multitype:string array  Ambigous <\Payment\Service\multitype:string, multitype:string array >
     */
    public function getFromInput ()
    {
        $inputArray = $this->paymentForm->createFormInput($this->paymentConfig);
        return $inputArray;
    }
    /*========================================================下面为支付处理=======================================================*/
    /** 
     * 支付转向站外
     * @param unknown $dataArray
     */
    public function paymentTo ($data)
    {
        //返回url
        $returnUrl = urlencode($data['return_url']);
        $cancelUrl = urlencode($data['cancel_url']);

        //货币信息
        $currencyCodeType = $data['order']->currency;
        $paymentType = 'Sale';

        //配送信息
        $personName = $data['address']->delivery_name;
        $SHIPTOSTREET = $data['address']->region_info . $data['address']->region_address;
        $SHIPTOCITY = '';
        $SHIPTOSTATE = '';
        $SHIPTOCOUNTRYCODE = '';
        $SHIPTOZIP = $data['address']->zip_code;
        $SHIPPINGAMT = $data['address']->express_fee + $data['order']->pay_fee;//这里把支付费用和配送费用加在了一起，放在支付费用中
        $SHIPDISCAMT = - $data['order']->user_pre_fee - $data['order']->buy_pre_fee;//这里把折扣和优惠信息放到了运费折扣里
        $INVNUM = $data['order']->order_sn;

        //对修改订单价格后的总价进行处理，因为要与下面的进行匹配，如果总价高了在在运费中添加，如果低了就在折扣中加入
        $dbshopOrderAmount = $data['order']->goods_amount + $SHIPDISCAMT + $SHIPPINGAMT;
        if($dbshopOrderAmount != $data['order']->order_amount) {
            $amountValue = $dbshopOrderAmount - $data['order']->order_amount;
            if($amountValue > 0) $SHIPDISCAMT = $SHIPDISCAMT - $amountValue;
            else $SHIPPINGAMT = $SHIPPINGAMT - $amountValue;
        }

        $nvpstr = "";
        $nvpstr = "&INVNUM=" . $INVNUM;

        $itemamt = 0.00;
        //商品信息
        $_SESSION['goods_num'] = count($data['goods']);
        foreach ($data['goods'] as $key => $val) {
            $itemamt = $itemamt + $val['goods_amount'];

            $nvpstr .= "&L_NAME" . $key . '=' . $val['goods_name'];
            $nvpstr .= "&L_AMT" . $key . '=' . $val['goods_shop_price'];
            $nvpstr .= "&L_QTY" . $key . '=' . $val['buy_num'];
            $nvpstr .= "&L_NUMBER" . $key . '=' . $val['goods_item'];
            $nvpstr .= "&L_DESC" . $key . '=' . strip_tags($val['goods_extend_info']);
        }
        //总费用
        $amt = $itemamt + $SHIPPINGAMT + $SHIPDISCAMT;
        $maxamt = $amt;

        $shiptoAddress = "&SHIPTONAME=$personName&SHIPTOSTREET=$SHIPTOSTREET&SHIPTOCITY=$SHIPTOCITY&SHIPTOSTATE=$SHIPTOSTATE&SHIPTOCOUNTRYCODE=$SHIPTOCOUNTRYCODE&SHIPTOZIP=$SHIPTOZIP";
        $nvpstr.="&ADDRESSOVERRIDE=$shiptoAddress&SHIPPINGAMT=$SHIPPINGAMT&MAXAMT=".(string)$maxamt."&AMT=".(string)$amt."&ITEMAMT=".(string)$itemamt."&CALLBACKTIMEOUT=4&SHIPDISCAMT=".$SHIPDISCAMT."&ReturnUrl=".$returnUrl."&CANCELURL=".$cancelUrl ."&CURRENCYCODE=".$currencyCodeType."&PAYMENTACTION=".$paymentType;
        $nvpstr = $this->nvpHeaderStr . $nvpstr;

        //检查信息
        $resArray = $this->hash_call("SetExpressCheckout", $nvpstr);
        $_SESSION['reshash']=$resArray;
        
        $ack = strtoupper($resArray["ACK"]);
        
        if($ack=="SUCCESS"){
            $token = urldecode($resArray["TOKEN"]);
            $payPalURL = $this->PAY_URL.$token;
            header("Location: ".$payPalURL);
        } else {
            $this->payError($data['cancel_url']);
        }

    }
    /**
     * 支付返回，非完成操作，paypal返回确认信息，在这步进行确认付款
     */
    public function paymentReturn ($orderInfo, array $language=array())
    {
        if(isset($_REQUEST['posttype']) and $_REQUEST['posttype'] == 'paypalto') {
            return $this->paymentFinish($orderInfo, $language);
        } else {
            $token  = urlencode($_REQUEST['token']);
            $nvpstr = "&TOKEN=".$token;
            $nvpstr = $this->nvpHeaderStr . $nvpstr;
            
            $resArray = $this->hash_call("GetExpressCheckoutDetails",$nvpstr);
            $_SESSION['reshash']=$resArray;
            $ack = strtoupper($resArray["ACK"]);
            
            if($ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING'){
                return $this->getPaypalOrderInfo($resArray, $language);
            } else  {
                $this->payError();
            }   
        }
    }
    /**
     * 最后的付款步骤
     * @param unknown $orderInfo
     * @param array $language
     */
    private function paymentFinish ($orderInfo,  array $language=array())
    {
        $token         = urlencode( $_REQUEST['token']);
        $paymentAmount = urlencode ($orderInfo->order_amount);
        $paymentType   = 'Sale';
        $currCodeType  = urlencode($orderInfo->currency);
        $payerID       = urlencode($_REQUEST['PayerID']);
        $serverName    = urlencode($_SERVER['SERVER_NAME']);
        
        $nvpstr='&TOKEN='.$token.'&PAYERID='.$payerID.'&PAYMENTACTION='.$paymentType.'&AMT='.$paymentAmount.'&CURRENCYCODE='.$currCodeType.'&IPADDRESS='.$serverName ;
        
        $resArray=$this->hash_call("DoExpressCheckoutPayment",$nvpstr);
        $ack = strtoupper($resArray["ACK"]);
        
        if($ack != 'SUCCESS' && $ack != 'SUCCESSWITHWARNING'){
            $this->payError();
        } else {
            $_SESSION['goods_num'] = '';
            $tableHtml = '<table class="table table-bordered"><tbody><tr><td><h3>'.$language['pay_finish'].'</h3></td></tr></tbody></table>';
            return array('payFinish'=>true, 'html'=>$tableHtml);
        }
    }
    /**
     * 发货处理
     * @param unknown $orderInfo
     */
    public function toSendOrder ($orderInfo, $express)
    {
        return true;
    }
/*===============================================下面为支付过程中需要的核心方法================================*/
    /** 
     * 第一次paypal返回信息
     * @param array $resArray
     * @param array $language
     * @return string
     */
    private function getPaypalOrderInfo(array $resArray, array $language)
    {
        $tableHtml = '<form action="" method="POST">';
        $tableHtml .= '<input type="hidden" name="posttype" value="paypalto" />';
        $tableHtml .= '<table class="table table-bordered table-striped">';
        $tableHtml .= '<thead><tr><th colspan="5"><b>'.$language['order_total'].':</b>' . $resArray['CURRENCYCODE'] .'&nbsp;'. ($resArray['AMT'] + $resArray['SHIPDISCAMT']) . '</th></tr></thead>';
        $tableHtml .= '<tbody>';
        $tableHtml .= '<tr><td>'.$language['goods_item'].'</td><td>'.$language['goods_name'].'</td><td>'.$language['buy_num'].'</td><td>'.$language['goods_price'].'</td><td>'.$language['goods_spec'].'</td></tr>';
        for($i=0;$i<$_SESSION['goods_num'];$i++) {
            $tableHtml .= '<tr><td>' . $resArray['L_NUMBER'.$i] . '</td><td>' . $resArray['L_NAME'.$i] . '</td>';
            $tableHtml .= '<td>' . $resArray['L_QTY'.$i] . '</td><td>' . $resArray['L_AMT'.$i] . '</td>';
            $tableHtml .= '<td>' . (isset($resArray['L_DESC'.$i]) ? $resArray['L_DESC'.$i] : '&nbsp;') . '</td></tr>';
        }
        $tableHtml .= '<tr><td colspan="5" align="right"><b>'.$language['shipping_fee'].'：</b>'.$resArray['SHIPPINGAMT'].'</td></tr>';
        //$tableHtml .= '收货人：'.$resArray['SHIPTONAME'].'<br>';
        //$tableHtml .= '收货地址：'.$resArray['SHIPTOSTREET'].'<br>';
        //$tableHtml .= '邮政编码：'.$resArray['SHIPTOZIP'].'</td></tr>';
        $tableHtml .= '</tbody></table>';
        $tableHtml .= '<p align="center"><button class="btn btn-primary btn-large" type="submit">'.$language['submit_pay'].'</button></p>';
        $tableHtml .= '</form>';
        
        return array('html'=>$tableHtml);
    }  
    /**
     * 抛出支付的hash_call处理
     * @param unknown $methodName
     * @param unknown $nvpStr
     * @return unknown
     */
    private function hash_call ($methodName, $nvpStr)
    {
        //declaring of global variables
        //global $API_Endpoint, $version, $API_UserName, $API_Password, $API_Signature, $nvp_Header, $subject, $AUTH_token, $AUTH_signature, $AUTH_timestamp;
        // form header string
        $nvpheader = $this->nvpHeader();
        //setting the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->API_Endpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        //turning off the server and peer verification(TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        //in case of permission APIs send headers as HTTPheders
        if (!empty($this->AUTH_token) && !empty($this->AUTH_signature) && !empty($this->AUTH_timestamp)) {
            $headers_array[] = "X-PP-AUTHORIZATION: " . $nvpheader;
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_array);
            curl_setopt($ch, CURLOPT_HEADER, false);
        } else {
            $nvpStr = $nvpheader . $nvpStr;
        }
        //if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
        //Set proxy name to PROXY_HOST and port number to PROXY_PORT in constants.php
        if ($this->USE_PROXY)
            curl_setopt($ch, CURLOPT_PROXY, $this->PROXY_HOST . ":" . $this->PROXY_PORT);

        //check if version is included in $nvpStr else include the version.
        if (strlen(str_replace('VERSION=', '', strtoupper($nvpStr))) == strlen($nvpStr)) {
            $nvpStr = "&VERSION=" . urlencode($this->version) . $nvpStr;
        }
 
        $nvpreq = "METHOD=" . urlencode($methodName) . $nvpStr;
        //setting the nvpreq as POST FIELD to curl
        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
        //getting response from server
        $response = curl_exec($ch);
        //echo CURLOPT_POSTFIELDS;echo '<br>';echo $nvpreq;echo '<br>';echo $response;exit;
        //convrting NVPResponse to an Associative Array
        $nvpResArray = $this->deformatNVP($response);
        $nvpReqArray = $this->deformatNVP($nvpreq);
        $_SESSION['nvpReqArray'] = $nvpReqArray;
        if (curl_errno($ch)) {
            // moving to display page to display curl errors
            $_SESSION['curl_error_no'] = curl_errno($ch);
            $_SESSION['curl_error_msg'] = curl_error($ch);
            $this->payError();
        } else {
            //closing the curl
            curl_close($ch);
        }
        return $nvpResArray;
    }
    /** 
     * 
     * @return string
     */
    private function nvpHeader ()
    {
        //global $API_Endpoint, $version, $API_UserName, $API_Password, $API_Signature, $nvp_Header, $subject, $AUTH_token, $AUTH_signature, $AUTH_timestamp;
        $this->nvpHeaderStr = "";

        if (defined('AUTH_MODE')) {
            //$AuthMode = "3TOKEN"; //Merchant's API 3-TOKEN Credential is required to make API Call.
            //$AuthMode = "FIRSTPARTY"; //Only merchant Email is required to make EC Calls.
            //$AuthMode = "THIRDPARTY";Partner's API Credential and Merchant Email as Subject are required.
            $AuthMode = "AUTH_MODE";
        } else {
            if ((!empty($this->API_UserName)) && (!empty($this->API_Password)) && (!empty($this->API_Signature)) && (!empty($this->subject))) {
                $AuthMode = "THIRDPARTY";
            } else if ((!empty($this->API_UserName)) && (!empty($this->API_Password)) && (!empty($this->API_Signature))) {
                $AuthMode = "3TOKEN";
            } elseif (!empty($this->AUTH_token) && !empty($this->AUTH_signature) && !empty($this->AUTH_timestamp)) {
                $AuthMode = "PERMISSION";
            } elseif (!empty($this->subject)) {
                $AuthMode = "FIRSTPARTY";
            }
        }
        switch ($AuthMode) {

        case "3TOKEN":
            $this->nvpHeaderStr = "&PWD=" . urlencode($this->API_Password) . "&USER=" . urlencode($this->API_UserName) . "&SIGNATURE=" . urlencode($this->API_Signature);
            break;
        case "FIRSTPARTY":
            $this->nvpHeaderStr = "&SUBJECT=" . urlencode($this->subject);
            break;
        case "THIRDPARTY":
            $this->nvpHeaderStr = "&PWD=" . urlencode($this->API_Password) . "&USER=" . urlencode($this->API_UserName) . "&SIGNATURE=" . urlencode($this->API_Signature) . "&SUBJECT=" . urlencode($this->subject);
            break;
        case "PERMISSION":
            $this->nvpHeaderStr = $this->formAutorization($this->AUTH_token, $this->AUTH_signature, $this->AUTH_timestamp);
            break;
        }
        return $this->nvpHeaderStr;
    }
    /** 
     * 
     * @param unknown $nvpstr
     * @return multitype:string
     */
    private function deformatNVP ($nvpstr)
    {

        $intial = 0;
        $nvpArray = array();

        while (strlen($nvpstr)) {
            //postion of Key
            $keypos = strpos($nvpstr, '=');
            //position of value
            $valuepos = strpos($nvpstr, '&') ? strpos($nvpstr, '&') : strlen($nvpstr);

            /*getting the Key and Value values and storing in a Associative Array*/
            $keyval = substr($nvpstr, $intial, $keypos);
            $valval = substr($nvpstr, $keypos + 1, $valuepos - $keypos - 1);
            //decoding the respose
            $nvpArray[urldecode($keyval)] = urldecode($valval);
            $nvpstr = substr($nvpstr, $valuepos + 1, strlen($nvpstr));
        }
        return $nvpArray;
    }
    /** 
     * 
     * @param unknown $auth_token
     * @param unknown $auth_signature
     * @param unknown $auth_timestamp
     * @return string
     */
    private function formAutorization ($auth_token, $auth_signature, $auth_timestamp)
    {
        $authString = "token=" . $auth_token . ",signature=" . $auth_signature . ",timestamp=" . $auth_timestamp;
        return $authString;
    }
    /**
     * 错误信息抛出
     */
    private function payError($backUrl='')
    {
        $outHtml = '<table width="60%" align="center"><tr><td colspan="2" class="header">The PayPal API has returned an error!</td></tr>';
        if(isset($_SESSION['curl_error_no'])) {
            $errorCode= $_SESSION['curl_error_no'] ;
            $errorMessage=$_SESSION['curl_error_msg'] ;
            session_unset();
            
            $outHtml .= '<tr><td>Error Number:</td><td>' . $errorCode . '</td></tr>';
            $outHtml .= '<tr><td>Error Message:</td><td>' . $errorMessage . '</td></tr>';
        } else {
            foreach ($_SESSION['reshash'] as $key => $val) {
                $outHtml .= '<tr><td>' . $key . ':</td><td>' . $val . '</td></tr>';
            }
        }
        if($backUrl != '') $outHtml .= '<tr><td colspan="2"><a href="'.$backUrl.'">BACK</a></td></tr>';
        
        $outHtml .= '</table>';
        echo $outHtml;
        exit;
    }
}

?>