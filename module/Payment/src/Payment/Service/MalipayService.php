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

namespace Payment\Service;
use Admin\Service\DbshopOpcache;
use Zend\Config\Writer\PhpArray;

/** 
 * 支付宝
 */
class MalipayService
{
    private $paymentConfig;
    private $paymentForm;
    
    /**
     * HTTPS形式消息验证地址
     */
    private $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
    /**
     * HTTP形式消息验证地址
     */
    private $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';
    /**
     *支付宝网关地址（新）
     */
    private $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';
    
    private $alipay_config;
    private $alipay_account;
    
    public function __construct()
    {
        if(!$this->paymentConfig) {
            $this->paymentConfig = include(DBSHOP_PATH . '/data/moduledata/Payment/malipay.php');
        }
        if(!$this->paymentForm) {
            $this->paymentForm = new \Payment\Form\PaymentForm();
        }

        $httpType = ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https' : 'http';
        //设置支付宝接口信息
        $this->alipay_config['partner']       = $this->paymentConfig['alipay_pid']['content'];
        $this->alipay_config['seller_id']     = $this->alipay_config['partner'];
        $this->alipay_config['key']           = $this->paymentConfig['alipay_key']['content'];
        $this->alipay_config['sign_type']     = strtoupper('MD5');      //签名方式
        $this->alipay_config['input_charset'] = strtolower('utf-8');
        //商户的私钥，如果签名方式设置为“0001”时，请设置该参数
        $this->alipay_config['private_key_path']    = DBSHOP_PATH . '/module/Payment/src/Payment/Service/api/malipay/key/rsa_private_key.pem';
        //支付宝公钥，如果签名方式设置为“0001”时，请设置该参数
        $this->alipay_config['ali_public_key_path'] = DBSHOP_PATH . '/module/Payment/src/Payment/Service/api/malipay/key/alipay_public_key.pem';
        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        $this->alipay_config['cacert']        = DBSHOP_PATH . '/module/Payment/src/Payment/Service/api/malipay/cacert.pem';
        $this->alipay_config['transport']     = $httpType;//'http';
        $this->alipay_config['payment_type']  = '1';
        $this->alipay_account                 = $this->paymentConfig['payment_user']['content'];
    }
    /**
     * 保存配置信息
     * @param array $data
     * @return unknown
     */
    public function savePaymentConfig(array $data)
    {
        $phpWriter  = new PhpArray();
        $configArray = $this->paymentForm->setFormValue($this->paymentConfig, $data);
        $phpWriter->toFile(DBSHOP_PATH . '/data/moduledata/Payment/malipay.php', $configArray);

        //废除启用opcache时，在修改时，被缓存的配置
        DbshopOpcache::invalidate(DBSHOP_PATH . '/data/moduledata/Payment/malipay.php');

        return $configArray;
    }
    /**
     * 获取表单数组
     * @return multitype:multitype:string array  Ambigous <\Payment\Service\multitype:string, multitype:string array >
     */
    public function getFromInput()
    {
        $inputArray = $this->paymentForm->createFormInput($this->paymentConfig);
        return $inputArray;
    }
    /*======================================下面为支付处理======================================*/
    /**
     * 支付转向站外
     * @param unknown $dataArray
     */
    public function paymentTo ($data)
    {
        //信息整理
        $goods_name_str = '';
        if(count($data['goods']) == 1) {
            $order_name = $data['goods'][0]['goods_name'].(!empty($data['goods'][0]['goods_extend_info']) ? '('.strip_tags($data['goods'][0]['goods_extend_info']).')' : '');
        } else {
            $order_name = '多商品合并购买';

            foreach($data['goods'] as $goods_value) {
                $array[] = $goods_value['goods_name'].(!empty($goods_value['goods_extend_info']) ? '('.strip_tags($goods_value['goods_extend_info']).')' : '');
            }
            $goods_name_str = implode('+', $array);
        }
        $order_name .= '|'.$data['shop_name'];

        //订单留言
        if(isset($data['order']->order_message) and !empty($data['order']->order_message)) {
            $goods_name_str = (empty($goods_name_str) ? '订单留言：'.$data['order']->order_message : $goods_name_str.'||订单留言：'.$data['order']->order_message);
        }

        //返回url
        $notify_url = $data['notify_url'];
        $return_url = $data['return_url'];
        $out_trade_no       = $data['order']->order_sn;     //商户订单号,商户网站订单系统中唯一订单号，必填
        $subject            = $order_name;                  //订单名称,必填
        $price              = $data['order']->order_amount; //付款金额，必填
        $body               = $goods_name_str;              //商品展示地址
        $show_url           = $data['order_url'];           //需以http://开头的完整路径，如：http://www.xxx.com/myorder.html

        /**
         * 构造要请求的参数数组，无需改动
         * alipay.wap.trade.create.direct 手机网页支付
         */
        $parameter = array(
                "service"             => 'alipay.wap.create.direct.pay.by.user',
                "partner"             => $this->alipay_config['partner'],
                "seller_id"           => $this->alipay_config['seller_id'],
                "payment_type"        => $this->alipay_config['seller_id'],
                "notify_url"	      => $notify_url,
                "return_url"	      => $return_url,
                "_input_charset"	  => $this->alipay_config['input_charset'],
                "out_trade_no"	      => $out_trade_no,
                "subject"	          => $subject,
                "total_fee"	          => $price,
                "show_url"	          => $show_url,
                "app_pay"	          => "Y",//启用此参数能唤起钱包APP支付宝
                "body"	              => $body,
        );
        
        $alipayForm = $this->buildRequestForm($parameter, 'get','Pay To');
        echo $alipayForm;exit;
    }
    /**
     * 发货处理
     * @param $orderInfo
     * @param $express
     * @return bool
     */
    public function toSendOrder ($orderInfo, $express)
    {
        $trade_no		= $orderInfo->order_out_sn;  //支付宝交易号
        $logistics_name = $express->express_name;    //物流公司名称
        $invoice_no		= $express->express_number;  //物流发货单号
        $transport_type = 'EXPRESS';                 //物流运输类型
        
        //构造要请求的参数数组，无需改动
        $parameter = array(
                "service"        => "send_goods_confirm_by_platform",
                "partner"        => $this->alipay_config['partner'],
                "trade_no"	     => $trade_no,
                "logistics_name" => $logistics_name,
                "invoice_no"	 => $invoice_no,
                "transport_type" => $transport_type,
                "_input_charset" => $this->alipay_config['input_charset']
        );
        
        $htmlText = $this->buildRequestHttp($parameter);
        $doc = new \DOMDocument();
        $doc->loadXML($htmlText);
        
        return true;
    }
    /**
     * 支付返回操作
     * @param $orderInfo
     * @param array $language
     * @return multitype
     */
    public function paymentReturn ($orderInfo, array $language=array())
    {
        //验证结果
        $verify_result = $this->verifyReturn();
        if($verify_result) {
            $out_trade_no = $_GET['out_trade_no']; //商户订单号
            $trade_no     = $_GET['trade_no'];     //支付宝交易号
            $trade_status = $_GET['trade_status']; //交易状态
            return $this->getAlipayPayReturnState($trade_status, $language);
        }
    }
    /**
     * 支付通知操作
     * @param $orderInfo
     * @param array $language
     * @return array
     */
    public function paymentNotify ($orderInfo, array $language=array())
    {
        //通知验证结果
        $verify_result = $this->verifyNotify();
        if($verify_result) {
            $out_trade_no = $_POST['out_trade_no'];
            $trade_no     = $_POST['trade_no'];
            $trade_status = $_POST['trade_status'];
            
            return $this->getAlipayPayNotifyState($trade_status, $orderInfo, $language);
        }
    }
    /**
     * url返回状态解析
     * @param unknown $trade_status
     * @return multitype:boolean Ambigous <string, number>
     */
    private function getAlipayPayReturnState($trade_status, array $language=array())
    {
        $payState = false;
        $stateNum = '';
        $message  = '';
        $otState  = '';//处理支付担保支付状态
        switch ($trade_status) {
            case 'WAIT_SELLER_SEND_GOODS'://等待卖家发货，说明支付那边已经付款,担保支付
                $payState = true;
                $stateNum = 20;
                $otState  = 25;
                $message  = $language['pay_finish'];            
                break;
                
            case 'TRADE_FINISHED':        //支付成功，即时到帐，当接口为即时到帐，这里表示钱已经完全付给卖家，如果为担保支付，表示对方确认付款完成了交易
            case 'TRADE_SUCCESS':         //支付成功，高级即时到帐
                
                $payState = true;
                $stateNum = 20;
                $message  = $language['pay_finish'];
                break;
            case '':
                
                break;
        }
        
        $tableHtml = '<table class="table table-bordered"><tbody><tr><td><h3>'.$message.'</h3></td></tr></tbody></table>';
        return array('payFinish'=>$payState, 'otState'=>$otState, 'stateNum'=>$stateNum, 'html'=>$tableHtml);
    }
    /**
     * 付款通知返回
     * @param $trade_status
     * @param $orderInfo
     * @param array $language
     * @return array
     */
    private function getAlipayPayNotifyState($trade_status, $orderInfo, array $language=array())
    {
        $payState = false;
        $stateNum = '';
        $message  = '';
        $otState  = '';//处理支付担保支付状态
        switch ($trade_status) {
            case 'WAIT_BUYER_PAY':          //该判断表示买家已在支付宝交易管理中产生了交易记录，但没有付款    
                exit('success');
                break;
            case 'WAIT_SELLER_SEND_GOODS':  //等待卖家发货，说明支付那边已经付款
                if($orderInfo->order_state >= 20) {//如果已经付款了，结束该通知
                    exit('success');
                } else {
                    $payState = true;
                    $stateNum = 20;
                    $otState  = 25;
                    $message  = 'success';                    
                }
                break;
            case 'WAIT_BUYER_CONFIRM_GOODS'://该判断表示卖家已经发了货，但买家还没有做确认收货的操作
                if($orderInfo->order_state >= 40) {
                    exit('success');
                } else {
                    $payState = false;
                    $stateNum = 40;
                    $message  = 'success';
                }
                break;
            case 'TRADE_FINISHED':          //该判断表示买家已经确认收货，这笔交易完成(担保交易)，付款成功（即时到帐）
            case 'TRADE_SUCCESS':           //付款成功（高级即时到帐）
                $dState = (($orderInfo->order_state < 20) ? 20 : 60);//判断，如果小于20，为即时到帐，付款完成，如果大于20，是交易完成
                if($orderInfo->order_state == $dState) {
                    exit('success');
                } else {
                    $payState = ($dState == 20 ? true : false);
                    $stateNum = $dState;
                    $message  = 'success';
                }
                break;
        }
        
        return array('payFinish'=>$payState, 'otState'=>$otState, 'stateNum'=>$stateNum, 'message'=>$message);
    }
    /*======================================下面为涉及支付的代码======================================*/
    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    private function createLinkstring($para)
    {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key."=".$val."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);
    
        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
        return $arg;
    }
    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    private function createLinkstringUrlencode($para)
    {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key."=".urlencode($val)."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);
    
        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
    
        return $arg;
    }
    /**
     * 除去数组中的空值和签名参数
     * @param $para 签名参数组
     * return 去掉空值与签名参数后的新签名参数组
     */
    private function paraFilter($para) {
        $para_filter = array();
        while (list ($key, $val) = each ($para)) {
            if($key == "sign" || $key == "sign_type" || $val == "")continue;
            else	$para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }
    /**
     * 对数组排序
     * @param $para 排序前的数组
     * return 排序后的数组
     */
    private function argSort($para)
    {
        ksort($para);
        reset($para);
        return $para;
    }
    /**
     * 异步通知时，对参数做固定排序
     * @param $para 排序前的参数组
     * @return 排序后的参数组
     */
    function sortNotifyPara($para) {
        $para_sort['service'] = $para['service'];
        $para_sort['v'] = $para['v'];
        $para_sort['sec_id'] = $para['sec_id'];
        $para_sort['notify_data'] = $para['notify_data'];
        return $para_sort;
    }
    /**
     * 写日志，方便测试（看网站需求，也可以改成把记录存入数据库）
     * 注意：服务器需要开通fopen配置
     * @param $word 要写入日志里的文本内容 默认值：空值
     */
    private function logResult($word='')
    {
        $fp = fopen(DBSHOP_PATH."/data/moduledata/Payment/log.txt","a");
        flock($fp, LOCK_EX) ;
        fwrite($fp,"执行日期：".strftime("%Y%m%d%H%M%S",time())."\n".$word."\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }
    /**
     * 远程获取数据，POST模式
     * 注意：
     * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
     * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
     * @param $url 指定URL完整路径地址
     * @param $cacert_url 指定当前工作目录绝对路径
     * @param $para 请求的数据
     * @param $input_charset 编码格式。默认值：空值
     * return 远程输出的数据
     */
    private function getHttpResponsePOST($url, $cacert_url, $para, $input_charset = '')
    {
    
        if (trim($input_charset) != '') {
            $url = $url."_input_charset=".$input_charset;
        }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
        curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址
        curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl,CURLOPT_POST,true); // post传输数据
        curl_setopt($curl,CURLOPT_POSTFIELDS,$para);// post传输数据
        $responseText = curl_exec($curl);
        //var_dump( curl_error($curl));//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);
    
        return $responseText;
    }
    /**
     * 远程获取数据，GET模式
     * 注意：
     * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
     * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
     * @param $url 指定URL完整路径地址
     * @param $cacert_url 指定当前工作目录绝对路径
     * return 远程输出的数据
     */
    private function getHttpResponseGET($url,$cacert_url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
        curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址
        $responseText = curl_exec($curl);
        //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);
    
        return $responseText;
    }
    /**
     * 实现多种字符编码方式
     * @param $input 需要编码的字符串
     * @param $_output_charset 输出的编码格式
     * @param $_input_charset 输入的编码格式
     * return 编码后的字符串
     */
    private function charsetEncode($input,$_output_charset ,$_input_charset)
    {
        $output = "";
        if(!isset($_output_charset) )$_output_charset  = $_input_charset;
        if($_input_charset == $_output_charset || $input ==null ) {
            $output = $input;
        } elseif (function_exists("mb_convert_encoding")) {
            $output = mb_convert_encoding($input,$_output_charset,$_input_charset);
        } elseif(function_exists("iconv")) {
            $output = iconv($_input_charset,$_output_charset,$input);
        } else die("sorry, you have no libs support for charset change.");
        return $output;
    }
    /**
     * 实现多种字符解码方式
     * @param $input 需要解码的字符串
     * @param $_output_charset 输出的解码格式
     * @param $_input_charset 输入的解码格式
     * return 解码后的字符串
     */
    private function charsetDecode($input,$_input_charset ,$_output_charset)
    {
        $output = "";
        if(!isset($_input_charset) ) $_input_charset  = $_input_charset ;
        if($_input_charset == $_output_charset || $input ==null ) {
            $output = $input;
        } elseif (function_exists("mb_convert_encoding")) {
            $output = mb_convert_encoding($input,$_output_charset,$_input_charset);
        } elseif(function_exists("iconv")) {
            $output = iconv($_input_charset,$_output_charset,$input);
        } else die("sorry, you have no libs support for charset changes.");
        return $output;
    }
    /**
     * 生成签名结果
     * @param $para_sort 已排序要签名的数组
     * return 签名结果字符串
     */
    private function buildRequestMysign($para_sort)
    {
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_sort);
    
        $mysign = "";
        switch (strtoupper(trim($this->alipay_config['sign_type']))) {
            case "MD5" :
                $mysign = $this->md5Sign($prestr, $this->alipay_config['key']);
                break;
            default :
                $mysign = "";
        }
    
        return $mysign;
    }
    
    /**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组
     */
    private function buildRequestPara($para_temp)
    {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($para_temp);
    
        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);
        //生成签名结果
        $mysign = $this->buildRequestMysign($para_sort);
        //签名结果与签名方式加入请求提交参数组中
        $para_sort['sign'] = $mysign;
        $para_sort['sign_type'] = strtoupper(trim($this->alipay_config['sign_type']));
    
        return $para_sort;
    }
    
    /**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组字符串
     */
    private function buildRequestParaToString($para_temp)
    {
        //待请求参数数组
        $para = $this->buildRequestPara($para_temp);
    
        //把参数组中所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
        $request_data = $this->createLinkstringUrlencode($para);
    
        return $request_data;
    }
    
    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param $para_temp 请求参数数组
     * @param $method 提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
     * @return 提交表单HTML文本
     */
    private function buildRequestForm($para_temp, $method, $button_name)
    {
        $para = $this->buildRequestPara($para_temp);
        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$this->alipay_gateway_new."_input_charset=".trim(strtolower($this->alipay_config['input_charset']))."' method='".$method."'>";
        while (list ($key, $val) = each ($para)) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }
    
        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml."<input type='submit' value='".$button_name."'></form>";
        $sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";
    
        return $sHtml;
    }
    
    /**
     * 建立请求，以模拟远程HTTP的POST请求方式构造并获取支付宝的处理结果
     * @param $para_temp 请求参数数组
     * @return 支付宝处理结果
     */
    private function buildRequestHttp($para_temp)
    {
        $sResult = '';
    
        //待请求参数数组字符串
        $request_data = $this->buildRequestPara($para_temp);
        
        //远程获取数据
        $sResult = $this->getHttpResponsePOST($this->alipay_gateway_new, $this->alipay_config['cacert'],$request_data,trim(strtolower($this->alipay_config['input_charset'])));

        return $sResult;
    }
    
    /**
     * 建立请求，以模拟远程HTTP的POST请求方式构造并获取支付宝的处理结果，带文件上传功能
     * @param $para_temp 请求参数数组
     * @param $file_para_name 文件类型的参数名
     * @param $file_name 文件完整绝对路径
     * @return 支付宝返回处理结果
     */
    private function buildRequestHttpInFile($para_temp, $file_para_name, $file_name)
    {
    
        //待请求参数数组
        $para = $this->buildRequestPara($para_temp);
        $para[$file_para_name] = "@".$file_name;
    
        //远程获取数据
        $sResult = $this->getHttpResponsePOST($this->alipay_gateway_new, $this->alipay_config['cacert'],$para,trim(strtolower($this->alipay_config['input_charset'])));
    
        return $sResult;
    }
    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
    private function verifyNotify()
    {
        if(empty($_POST)) {//判断POST来的数组是否为空
            return false;
        }
        else {
            //生成签名结果
            $isSign = $this->getSignVeryfy($_POST, $_POST["sign"]);
            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            $responseTxt = 'false';
            if (! empty($_POST["notify_id"])) {$responseTxt = $this->getResponse($_POST["notify_id"]);}

            //写日志记录
            //if ($isSign) {
            //	$isSignStr = 'true';
            //}
            //else {
            //	$isSignStr = 'false';
            //}
            //$log_text = "responseTxt=".$responseTxt."\n notify_url_log:isSign=".$isSignStr.",";
            //$log_text = $log_text.createLinkString($_POST);
            //logResult($log_text);

            //验证
            //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
            //isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
            if (preg_match("/true$/i",$responseTxt) && $isSign) {
                return true;
            } else {
                return false;
            }
        }
    }
    /**
     * 针对return_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
    private function verifyReturn()
    {
        if(empty($_GET)) {//判断POST来的数组是否为空
            return false;
        }
        else {
            //生成签名结果
            $isSign = $this->getSignVeryfy($_GET, $_GET["sign"]);
            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            $responseTxt = 'false';
            if (! empty($_GET["notify_id"])) {$responseTxt = $this->getResponse($_GET["notify_id"]);}

            //写日志记录
            //if ($isSign) {
            //	$isSignStr = 'true';
            //}
            //else {
            //	$isSignStr = 'false';
            //}
            //$log_text = "responseTxt=".$responseTxt."\n return_url_log:isSign=".$isSignStr.",";
            //$log_text = $log_text.createLinkString($_GET);
            //logResult($log_text);

            //验证
            //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
            //isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
            if (preg_match("/true$/i",$responseTxt) && $isSign) {
                return true;
            } else {
                return false;
            }
        }
    }
    /**
     * 获取返回时的签名验证结果
     * @param $para_temp 通知返回来的参数数组
     * @param $sign 返回的签名结果
     * @return 签名验证结果
     */
    private function getSignVeryfy($para_temp, $sign)
    {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($para_temp);
    
        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);

        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_sort);
    
        $isSgin = false;
        switch (strtoupper(trim($this->alipay_config['sign_type']))) {
            case "MD5" :
                $isSgin = $this->md5Verify($prestr, $sign, $this->alipay_config['key']);
                break;
            default :
                $isSgin = false;
        }
    
        return $isSgin;
    }
    /**
     * 获取远程服务器ATN结果,验证返回URL
     * @param $notify_id 通知校验ID
     * @return 服务器ATN结果
     * 验证结果集：
     * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
     * true 返回正确信息
     * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
    private function getResponse($notify_id)
    {
        $transport = strtolower(trim($this->alipay_config['transport']));
        $partner = trim($this->alipay_config['partner']);
        $veryfy_url = '';
        if($transport == 'https') {
            $veryfy_url = $this->https_verify_url;
        }
        else {
            $veryfy_url = $this->http_verify_url;
        }
        $veryfy_url = $veryfy_url."partner=" . $partner . "&notify_id=" . $notify_id;
        $responseTxt = $this->getHttpResponseGET($veryfy_url, $this->alipay_config['cacert']);
    
        return $responseTxt;
    }
    /**
     * 签名字符串
     * @param $prestr 需要签名的字符串
     * @param $key 私钥
     * return 签名结果
     */
    private function md5Sign($prestr, $key)
    {
        $prestr = $prestr . $key;
        return md5($prestr);
    }
    /**
     * 验证签名
     * @param $prestr 需要签名的字符串
     * @param $sign 签名结果
     * @param $key 私钥
     * return 签名结果
     */
    private function md5Verify($prestr, $sign, $key)
    {
        $prestr = $prestr . $key;
        $mysgin = md5($prestr);
    
        if($mysgin == $sign) {
            return true;
        }
        else {
            return false;
        }
    }
}