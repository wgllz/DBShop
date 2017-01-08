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

class WxmpayService {

    private $xmlReader;
    private $paymentConfig;
    private $paymentForm;
    private $values = array();

    private $wxpay_config;
    private $http_type;

    public function __construct()
    {
        if(!$this->xmlReader) {
            $this->xmlReader = new \Zend\Config\Reader\Xml();
        }
        if(!$this->paymentConfig) {
            $this->paymentConfig = $this->xmlReader->fromFile(DBSHOP_PATH . '/data/moduledata/Payment/wxmpay.xml');
        }
        if(!$this->paymentForm) {
            $this->paymentForm = new \Payment\Form\PaymentForm();
        }

        $this->http_type = ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https' : 'http';

        //设置微信接口信息
        $this->wxpay_config['appid'] = $this->paymentConfig['wxpay_appid']['content']; //绑定支付的APPID（必须配置，开户邮件中可查看）
        $this->wxpay_config['mchid'] = $this->paymentConfig['wxpay_mchid']['content']; //商户号（必须配置，开户邮件中可查看）
        $this->wxpay_config['key']   = $this->paymentConfig['wxpay_key']['content'];   //商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）
        $this->wxpay_config['type']  = $this->paymentConfig['payment_type']['selected'];

        $this->wxpay_config['appsecret'] = $this->paymentConfig['wxpay_appsecret']['content'];  //公众帐号secert（仅JSAPI支付的时候需要配置， 登录公众平台，进入开发者中心可设置）

        /**
         * 证书路径设置
         *
         * 设置商户证书路径
         * 证书路径,注意应该填写绝对路径（仅退款、撤销订单时需要，可登录商户平台下载，
         * API证书下载地址：https://pay.weixin.qq.com/index.php/account/api_cert，下载之前需要安装商户操作证书）
         */
        $this->wxpay_config['sslcert_path'] = DBSHOP_PATH . '/module/Payment/src/Payment/Service/api/wxpay/apiclient_cert.pem';
        $this->wxpay_config['sslkey_path'] = DBSHOP_PATH . '/module/Payment/src/Payment/Service/api/wxpay/apiclient_key.pem';

        /**
         * curl 代理设置
         *
         * 这里设置代理机器，只有需要代理的时候才设置，不需要代理，请设置为0.0.0.0和0
         * 本例程通过curl使用HTTP POST方法，此处可修改代理服务器，
         * 默认CURL_PROXY_HOST=0.0.0.0和CURL_PROXY_PORT=0，此时不开启代理（如有需要才设置）
         */
        $this->wxpay_config['curl_proxy_host'] = "0.0.0.0";
        $this->wxpay_config['curl_proxy_port'] = 0;

        /**
         * 上报信息配置
         *
         * 接口调用上报等级，默认紧错误上报（注意：上报超时间为【1s】，上报无论成败【永不抛出异常】，
         * 不会影响接口调用流程），开启上报之后，方便微信监控请求调用的质量，建议至少
         * 开启错误上报。
         * 上报等级，0.关闭上报; 1.仅错误出错上报; 2.全量上报
         */
        $this->wxpay_config['report_levenl'] = 1;

        //初始化日志
        //$logHandler= new CLogFileHandler(DBSHOP_PATH . '/module/Payment/src/Payment/Service/api/wxpay/logs/'.date('Y-m-d').'.log');
        //$log = Log::Init($logHandler, 15);
    }
    public function savePaymentConfig(array $data)
    {
        $xmlWriter   = new \Zend\Config\Writer\Xml();
        $configArray = $this->paymentForm->setFormValue($this->paymentConfig, $data);
        $xmlWriter->toFile(DBSHOP_PATH . '/data/moduledata/Payment/wxmpay.xml', $configArray);
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
    /*======================================下面为前台会员充值支付处理======================================*/
    /**
     * 充值支付转向站外
     * @param $data
     * @return 成功时返回
     */
    public function paycheckPaymentTo($data)
    {
        $this->wxpay_config['notify_url'] = $data['notify_url'];

        $openid = $this->GetOpenid();

        $this->SetBody($data['shop_name'].' '.$data['order_name']);
        $this->SetAttach($data['goods_name']);
        $this->SetOut_trade_no(md5('dbshop'.$data['paycheck']->paycheck_id));
        $this->SetTotal_fee($data['paycheck']->money_change_num * 100); //微信支付接口金额单位是 分
        $this->SetTime_start(date("YmdHis"));
        $this->SetTime_expire(date("YmdHis", time() + 600));
        $this->SetNotify_url($data['notify_url']);
        $this->SetTrade_type($this->wxpay_config['type']);
        $this->SetOpenid($openid);
        $result = $this->GetPayUrl();

        if($result['result_code'] == 'FAIL') exit($result['err_code_des']);

        $jsApiParameters = $this->GetJsApiParameters($result);

        return $jsApiParameters;
    }
    /**
     * 充值支付通知操作
     * @param $paycheckInfo
     * @param array $language
     * @return array
     */
    public function paycheckPaymentNotify ($paycheckInfo, array $language=array())
    {
        //Log::DEBUG("begin notify");
        $array = $this->Handle(false);
        if(isset($array['state']) and $array['state'] and $paycheckInfo->pay_state < 20) {
            return array('payFinish'=>true, 'stateNum'=>20, 'message'=>$array['message']);
        }
    }
    /*======================================下面为支付处理======================================*/
    /**
     * 支付转向站外
     *
     */
    public function paymentTo ($data)
    {
        $this->wxpay_config['notify_url'] = $data['notify_url'];

        //信息整理
        $goods_name_str = '';
        $order_name     = '';
        if(count($data['goods']) == 1) {
            $order_name = $data['goods'][0]['goods_name'];
            $goods_name_str = $order_name;
        } else {
            $order_name = '多商品合并购买';
            foreach($data['goods'] as $goods_value) {
                $array[] = $goods_value['goods_name'];
            }
            $goods_name_str = implode('+', $array);
        }
        $order_name .= '|'.$data['shop_name'];

        $openid = $this->GetOpenid();

        $this->SetBody($order_name);
        $this->SetAttach($goods_name_str);
        $this->SetOut_trade_no($data['order']->order_sn);
        $this->SetTotal_fee($data['order']->order_amount * 100); //微信支付接口金额单位是 分
        $this->SetTime_start(date("YmdHis"));
        $this->SetTime_expire(date("YmdHis", time() + 600));
        $this->SetNotify_url($data['notify_url']);
        $this->SetTrade_type($this->wxpay_config['type']);
        $this->SetOpenid($openid);
        $result = $this->GetPayUrl();

        if($result['result_code'] == 'FAIL') exit($result['err_code_des']);

        $jsApiParameters = $this->GetJsApiParameters($result);

        return $jsApiParameters;
    }
    /**
     * 支付通知操作
     * @param unknown $orderInfo
     * @param array $language
     */
    public function paymentNotify ($orderInfo, array $language=array())
    {
            //Log::DEBUG("begin notify");
        $array = $this->Handle(false);
        if(isset($array['state']) and $array['state'] and $orderInfo->order_state < 20) {
            return array('payFinish'=>true, 'stateNum'=>20, 'message'=>$array['message']);
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
    /*======================================下面为核心代码======================================*/
    /**
     *
     * 通过跳转获取用户的openid，跳转流程如下：
     * 1、设置自己需要调回的url及其其他参数，跳转到微信服务器https://open.weixin.qq.com/connect/oauth2/authorize
     * 2、微信服务处理完成之后会跳转回用户redirect_uri地址，此时会带上一些参数，如：code
     *
     * @return 用户的openid
     */
    public function GetOpenid()
    {
        //通过code获得openid
        if (!isset($_GET['code'])){
            //触发微信返回code码
            $baseUrl = urlencode($this->http_type.'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].$_SERVER['QUERY_STRING']);
            $url = $this->__CreateOauthUrlForCode($baseUrl);
            Header("Location: $url");
            exit();
        } else {
            //获取code码，以获取openid
            $code = $_GET['code'];
            $openid = $this->getOpenidFromMp($code);
            return $openid;
        }
    }
    /**
     *
     * 获取jsapi支付的参数
     * @param array $UnifiedOrderResult 统一支付接口返回的数据
     * @throws WxPayException
     *
     * @return json数据，可直接填入js函数作为参数
     */
    public function GetJsApiParameters($UnifiedOrderResult)
    {
        if(!array_key_exists("appid", $UnifiedOrderResult)
            || !array_key_exists("prepay_id", $UnifiedOrderResult)
            || $UnifiedOrderResult['prepay_id'] == "")
        {
           exit("参数错误");
        }
        $this->SetAppid($UnifiedOrderResult["appid"]);
        $timeStamp = time();
        $this->SetTimeStamp("$timeStamp");
        $this->SetNonceStr($this->getNonceStr());
        $this->SetPackage("prepay_id=" . $UnifiedOrderResult['prepay_id']);
        $this->SetSignType("MD5");
        $this->SetPaySign($this->MakeSign());
        $parameters = json_encode($this->GetValues());
        return $parameters;
    }
    /**
     *
     * 构造获取code的url连接
     * @param string $redirectUrl 微信服务器回跳的url，需要url编码
     *
     * @return 返回构造好的url
     */
    private function __CreateOauthUrlForCode($redirectUrl)
    {
        $urlObj["appid"] = $this->wxpay_config['appid'];
        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = "snsapi_base";
        $urlObj["state"] = "STATE"."#wechat_redirect";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
    }
    /**
     *
     * 通过code从工作平台获取openid机器access_token
     * @param string $code 微信跳转回来带上的code
     *
     * @return openid
     */
    public function GetOpenidFromMp($code)
    {
        $url = $this->__CreateOauthUrlForOpenid($code);
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if($this->wxpay_config['curl_proxy_host'] != "0.0.0.0"
            && $this->wxpay_config['curl_proxy_port'] != 0){
            curl_setopt($ch,CURLOPT_PROXY, $this->wxpay_config['curl_proxy_host']);
            curl_setopt($ch,CURLOPT_PROXYPORT, $this->wxpay_config['curl_proxy_port']);
        }
        //运行curl，结果以jason形式返回
        $res = curl_exec($ch);
        curl_close($ch);
        //取出openid
        $data = json_decode($res,true);
        $this->values = $data;
        $openid = $data['openid'];
        return $openid;
    }
    /**
     *
     * 构造获取open和access_toke的url地址
     * @param string $code，微信跳转带回的code
     *
     * @return 请求的url
     */
    private function __CreateOauthUrlForOpenid($code)
    {
        $urlObj["appid"] = $this->wxpay_config['appid'];
        $urlObj["secret"] = $this->wxpay_config['appsecret'];
        $urlObj["code"] = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
    }
    /**
     *
     * 回调入口
     * @param bool $needSign  是否需要签名输出
     */
    final public function Handle($needSign = true)
    {
        $msg = "OK";
        //当返回false的时候，表示notify中调用NotifyCallBack回调失败获取签名校验失败，此时直接回复失败
        $result = $this->notify(array($this, 'NotifyCallBack'), $msg);
        if($result == false){
            $this->SetReturn_code("FAIL");
            $this->SetReturn_msg($msg);
            $this->ReplyNotify(false);
            return;
        } else {
            //该分支在成功回调到NotifyCallBack方法，处理完成之后流程
            $this->SetReturn_code("SUCCESS");
            $this->SetReturn_msg("OK");
        }
        return $this->ReplyNotify($needSign);
    }
    /**
     *
     * 支付结果通用通知
     * @param function $callback
     * 直接回调函数使用方法: notify(you_function);
     * 回调类成员函数方法:notify(array($this, you_function));
     * $callback  原型为：function function_name($data){}
     */
    public function notify($callback, &$msg)
    {
        //获取通知的数据
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        //如果返回成功则验证签名
        try {
            $result = $this->Init($xml);
        } catch (\Exception $e){
            $msg = $e->errorMessage();
            return false;
        }

        return call_user_func($callback, $result);
    }
    /**
     *
     * notify回调方法，该方法中需要赋值需要输出的参数,不可重写
     * @param array $data
     * @return true回调出来完成不需要继续回调，false回调处理未完成需要继续回调
     */
    final public function NotifyCallBack($data)
    {
        $msg = "OK";
        $result = $this->NotifyProcess($data, $msg);

        if($result == true){
            $this->SetReturn_code("SUCCESS");
            $this->SetReturn_msg("OK");
        } else {
            $this->SetReturn_code("FAIL");
            $this->SetReturn_msg($msg);
        }
        return $result;
    }
    //重写回调处理函数
    public function NotifyProcess($data, &$msg)
    {
        //Log::DEBUG("call back:" . json_encode($data));
        $notfiyOutput = array();

        if(!array_key_exists("transaction_id", $data)){
            $msg = "输入参数不正确";
            //Log::DEBUG("call back:" . $msg);
            return false;
        }
        //查询订单，判断订单真实性
        if(!$this->Queryorder($data)){
            $msg = "订单查询失败";
            //Log::DEBUG("call back:" . $msg);
            return false;
        }
        return true;
    }
    //查询订单
    public function Queryorder($data)
    {
        $this->values = array();

        $this->SetTransaction_id($data["transaction_id"]);
        $result = $this->orderQuery();
        //Log::DEBUG("query:" . json_encode($result));
        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            return true;
        }
        return false;
    }
    /**
     *
     * 回复通知
     * @param bool $needSign 是否需要签名输出
     */
    final private function ReplyNotify($needSign = true)
    {
        //如果需要签名
        if($needSign == true &&
            $this->GetReturn_code() == "SUCCESS")
        {
            $this->SetSign();
        }
        //Log::DEBUG("replynotify");
        return $this->oreplyNotify($this->ToXml());
    }
    /**
     * 直接输出xml
     * @param string $xml
     */
    public function oreplyNotify($xml)
    {
        //Log::DEBUG("oreplynotify");
        return array('state'=>true, 'message'=>$xml);
        //echo $xml;
    }
    /**
     *
     * 查询订单，WxPayOrderQuery中out_trade_no、transaction_id至少填一个
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param WxPayOrderQuery $inputObj
     * @param int $timeOut
     * @throws WxPayException
     * @return 成功时返回，其他抛异常
     */
    public function orderQuery($timeOut = 6)
    {
        $url = "https://api.mch.weixin.qq.com/pay/orderquery";
        //检测必填参数
        if(!$this->IsOut_trade_noSet() && !$this->IsTransaction_idSet()) {
            //Log::DEBUG("call back:订单查询接口中，out_trade_no、transaction_id至少填一个！");
            exit("订单查询接口中，out_trade_no、transaction_id至少填一个！");
        }
        $this->SetAppid($this->wxpay_config['appid']);//公众账号ID
        $this->SetMch_id($this->wxpay_config['mchid']);//商户号
        $this->SetNonce_str($this->getNonceStr());//随机字符串

        $this->SetSign();//签名
        $xml = $this->ToXml();

        $startTimeStamp = $this->getMillisecond();//请求开始时间
        $response = $this->postXmlCurl($xml, $url, false, $timeOut);
        $result = $this->Init($response);
        $this->reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

        return $result;
    }
    /**
     *
     * 统一下单，WxPayUnifiedOrder中out_trade_no、body、total_fee、trade_type必填
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param WxPayUnifiedOrder $inputObj
     * @param int $timeOut
     * @throws WxPayException
     * @return 成功时返回，其他抛异常
     */
    public function unifiedOrder($timeOut = 6)
    {
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        //检测必填参数
        if(!$this->IsOut_trade_noSet()) {
            throw new \Exception("缺少统一支付接口必填参数out_trade_no！");
        }else if(!$this->IsBodySet()){
            throw new \Exception("缺少统一支付接口必填参数body！");
        }else if(!$this->IsTotal_feeSet()) {
            throw new \Exception("缺少统一支付接口必填参数total_fee！");
        }else if(!$this->IsTrade_typeSet()) {
            throw new \Exception("缺少统一支付接口必填参数trade_type！");
        }

        //关联参数
        if($this->GetTrade_type() == "JSAPI" && !$this->IsOpenidSet()){
            throw new \Exception("统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！");
        }

        //异步通知url未设置，则使用配置文件中的url
        if(!$this->IsNotify_urlSet()){
            $this->SetNotify_url($this->wxpay_config['notify_url']);//异步通知url
        }

        $this->SetAppid($this->wxpay_config['appid']);//公众账号ID
        $this->SetMch_id($this->wxpay_config['mchid']);//商户号
        $this->SetSpbill_create_ip($_SERVER['REMOTE_ADDR']);//终端ip
        //$inputObj->SetSpbill_create_ip("1.1.1.1");
        $this->SetNonce_str($this->getNonceStr());//随机字符串

        //签名
        $this->SetSign();
        $xml = $this->ToXml();

        $startTimeStamp = $this->getMillisecond();//请求开始时间
        $response = $this->postXmlCurl($xml, $url, false, $timeOut);

        $result = $this->Init($response);
        $this->reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

        return $result;
    }
    /**
     * 判断微信的订单号，优先使用是否存在
     * @return true 或 false
     **/
    public function IsTransaction_idSet()
    {
        return array_key_exists('transaction_id', $this->values);
    }
    /**
     *
     * 生成直接支付url，支付url有效期为2小时,模式二
     * @param UnifiedOrderInput $input
     */
    public function GetPayUrl()
    {
        $result = $this->unifiedOrder();
            return $result;
    }
    /**
     * 设置微信的订单号，优先使用
     * @param string $value
     **/
    public function SetTransaction_id($value)
    {
        $this->values['transaction_id'] = $value;
    }
    /**
     * 设置签名，详见签名生成算法
     * @param string $value
     **/
    public function SetSign()
    {
        $sign = $this->MakeSign();
        $this->values['sign'] = $sign;
        return $sign;
    }

    /**
     * 获取签名，详见签名生成算法的值
     * @return 值
     **/
    public function GetSign()
    {
        return $this->values['sign'];
    }

    /**
     * 判断签名，详见签名生成算法是否存在
     * @return true 或 false
     **/
    public function IsSignSet()
    {
        return array_key_exists('sign', $this->values);
    }
    /**
     * 获取SUCCESS/FAIL此字段是通信标识，非交易标识，交易是否成功需要查看trade_state来判断的值
     * @return 值
     **/
    public function GetReturn_code()
    {
        return $this->values['return_code'];
    }
    /**
     * 随机字符串
     * @param string $value
     **/
    public function SetNonceStr($value)
    {
        $this->values['nonceStr'] = $value;
    }
    /**
     * 设置订单详情扩展字符串
     * @param string $value
     **/
    public function SetPackage($value)
    {
        $this->values['package'] = $value;
    }
    /**
     * 设置签名方式
     * @param string $value
     **/
    public function SetSignType($value)
    {
        $this->values['signType'] = $value;
    }
    /**
     * 设置签名方式
     * @param string $value
     **/
    public function SetPaySign($value)
    {
        $this->values['paySign'] = $value;
    }
    /**
     * 输出xml字符
     * @throws WxPayException
     **/
    public function ToXml()
    {
        if(!is_array($this->values)
            || count($this->values) <= 0)
        {
            throw new \Exception("数组数据异常！");
        }

        $xml = "<xml>";
        foreach ($this->values as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public function FromXml($xml)
    {
        if(!$xml){
            throw new \Exception("xml数据异常！");
        }
        //将XML转为array
        //禁止引用外部xml实体

        //libxml_disable_entity_loader(true);

        $this->values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $this->values;
    }

    /**
     * 格式化参数格式化成url参数
     */
    public function ToUrlParams()
    {
        $buff = "";
        foreach ($this->values as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function MakeSign()
    {
        //签名步骤一：按字典序排序参数
        ksort($this->values);
        $string = $this->ToUrlParams();
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$this->wxpay_config['key'];
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 获取设置的值
     */
    public function GetValues()
    {
        return $this->values;
    }

    /**
     * 设置微信分配的公众账号ID
     * @param string $value
     **/
    public function SetAppid($value)
    {
        $this->values['appid'] = $value;
    }
    /**
     * 获取微信分配的公众账号ID的值
     * @return 值
     **/
    public function GetAppid()
    {
        return $this->values['appid'];
    }
    /**
     * 判断微信分配的公众账号ID是否存在
     * @return true 或 false
     **/
    public function IsAppidSet()
    {
        return array_key_exists('appid', $this->values);
    }


    /**
     * 设置微信支付分配的商户号
     * @param string $value
     **/
    public function SetMch_id($value)
    {
        $this->values['mch_id'] = $value;
    }
    /**
     * 获取微信支付分配的商户号的值
     * @return 值
     **/
    public function GetMch_id()
    {
        return $this->values['mch_id'];
    }
    /**
     * 设置支付时间戳
     * @param string $value
     **/
    public function SetTimeStamp($value)
    {
        $this->values['timeStamp'] = $value;
    }
    /**
     * 获取支付时间戳的值
     * @return 值
     **/
    public function GetTimeStamp()
    {
        return $this->values['timeStamp'];
    }
    /**
     * 判断微信支付分配的商户号是否存在
     * @return true 或 false
     **/
    public function IsMch_idSet()
    {
        return array_key_exists('mch_id', $this->values);
    }

    /**
     * 设置微信支付分配的终端设备号，商户自定义
     * @param string $value
     **/
    public function SetDevice_info($value)
    {
        $this->values['device_info'] = $value;
    }
    /**
     * 获取微信支付分配的终端设备号，商户自定义的值
     * @return 值
     **/
    public function GetDevice_info()
    {
        return $this->values['device_info'];
    }
    /**
     * 判断微信支付分配的终端设备号，商户自定义是否存在
     * @return true 或 false
     **/
    public function IsDevice_infoSet()
    {
        return array_key_exists('device_info', $this->values);
    }


    /**
     * 设置随机字符串，不长于32位。推荐随机数生成算法
     * @param string $value
     **/
    public function SetNonce_str($value)
    {
        $this->values['nonce_str'] = $value;
    }
    /**
     * 获取随机字符串，不长于32位。推荐随机数生成算法的值
     * @return 值
     **/
    public function GetNonce_str()
    {
        return $this->values['nonce_str'];
    }
    /**
     * 判断随机字符串，不长于32位。推荐随机数生成算法是否存在
     * @return true 或 false
     **/
    public function IsNonce_strSet()
    {
        return array_key_exists('nonce_str', $this->values);
    }

    /**
     * 设置商品或支付单简要描述
     * @param string $value
     **/
    public function SetBody($value)
    {
        $this->values['body'] = $value;
    }
    /**
     * 获取商品或支付单简要描述的值
     * @return 值
     **/
    public function GetBody()
    {
        return $this->values['body'];
    }
    /**
     * 判断商品或支付单简要描述是否存在
     * @return true 或 false
     **/
    public function IsBodySet()
    {
        return array_key_exists('body', $this->values);
    }


    /**
     * 设置商品名称明细列表
     * @param string $value
     **/
    public function SetDetail($value)
    {
        $this->values['detail'] = $value;
    }
    /**
     * 获取商品名称明细列表的值
     * @return 值
     **/
    public function GetDetail()
    {
        return $this->values['detail'];
    }
    /**
     * 判断商品名称明细列表是否存在
     * @return true 或 false
     **/
    public function IsDetailSet()
    {
        return array_key_exists('detail', $this->values);
    }


    /**
     * 设置附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
     * @param string $value
     **/
    public function SetAttach($value)
    {
        $this->values['attach'] = $value;
    }
    /**
     * 获取附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据的值
     * @return 值
     **/
    public function GetAttach()
    {
        return $this->values['attach'];
    }
    /**
     * 判断附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据是否存在
     * @return true 或 false
     **/
    public function IsAttachSet()
    {
        return array_key_exists('attach', $this->values);
    }


    /**
     * 设置商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号
     * @param string $value
     **/
    public function SetOut_trade_no($value)
    {
        $this->values['out_trade_no'] = $value;
    }
    /**
     * 获取商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号的值
     * @return 值
     **/
    public function GetOut_trade_no()
    {
        return $this->values['out_trade_no'];
    }
    /**
     * 判断商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号是否存在
     * @return true 或 false
     **/
    public function IsOut_trade_noSet()
    {
        return array_key_exists('out_trade_no', $this->values);
    }


    /**
     * 设置符合ISO 4217标准的三位字母代码，默认人民币：CNY，其他值列表详见货币类型
     * @param string $value
     **/
    public function SetFee_type($value)
    {
        $this->values['fee_type'] = $value;
    }
    /**
     * 获取符合ISO 4217标准的三位字母代码，默认人民币：CNY，其他值列表详见货币类型的值
     * @return 值
     **/
    public function GetFee_type()
    {
        return $this->values['fee_type'];
    }
    /**
     * 判断符合ISO 4217标准的三位字母代码，默认人民币：CNY，其他值列表详见货币类型是否存在
     * @return true 或 false
     **/
    public function IsFee_typeSet()
    {
        return array_key_exists('fee_type', $this->values);
    }


    /**
     * 设置订单总金额，只能为整数，详见支付金额
     * @param string $value
     **/
    public function SetTotal_fee($value)
    {
        $this->values['total_fee'] = $value;
    }
    /**
     * 获取订单总金额，只能为整数，详见支付金额的值
     * @return 值
     **/
    public function GetTotal_fee()
    {
        return $this->values['total_fee'];
    }
    /**
     * 判断订单总金额，只能为整数，详见支付金额是否存在
     * @return true 或 false
     **/
    public function IsTotal_feeSet()
    {
        return array_key_exists('total_fee', $this->values);
    }


    /**
     * 设置APP和网页支付提交用户端ip，Native支付填调用微信支付API的机器IP。
     * @param string $value
     **/
    public function SetSpbill_create_ip($value)
    {
        $this->values['spbill_create_ip'] = $value;
    }
    /**
     * 获取APP和网页支付提交用户端ip，Native支付填调用微信支付API的机器IP。的值
     * @return 值
     **/
    public function GetSpbill_create_ip()
    {
        return $this->values['spbill_create_ip'];
    }
    /**
     * 判断APP和网页支付提交用户端ip，Native支付填调用微信支付API的机器IP。是否存在
     * @return true 或 false
     **/
    public function IsSpbill_create_ipSet()
    {
        return array_key_exists('spbill_create_ip', $this->values);
    }


    /**
     * 设置订单生成时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。其他详见时间规则
     * @param string $value
     **/
    public function SetTime_start($value)
    {
        $this->values['time_start'] = $value;
    }
    /**
     * 获取订单生成时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。其他详见时间规则的值
     * @return 值
     **/
    public function GetTime_start()
    {
        return $this->values['time_start'];
    }
    /**
     * 判断订单生成时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。其他详见时间规则是否存在
     * @return true 或 false
     **/
    public function IsTime_startSet()
    {
        return array_key_exists('time_start', $this->values);
    }


    /**
     * 设置订单失效时间，格式为yyyyMMddHHmmss，如2009年12月27日9点10分10秒表示为20091227091010。其他详见时间规则
     * @param string $value
     **/
    public function SetTime_expire($value)
    {
        $this->values['time_expire'] = $value;
    }
    /**
     * 获取订单失效时间，格式为yyyyMMddHHmmss，如2009年12月27日9点10分10秒表示为20091227091010。其他详见时间规则的值
     * @return 值
     **/
    public function GetTime_expire()
    {
        return $this->values['time_expire'];
    }
    /**
     * 判断订单失效时间，格式为yyyyMMddHHmmss，如2009年12月27日9点10分10秒表示为20091227091010。其他详见时间规则是否存在
     * @return true 或 false
     **/
    public function IsTime_expireSet()
    {
        return array_key_exists('time_expire', $this->values);
    }


    /**
     * 设置商品标记，代金券或立减优惠功能的参数，说明详见代金券或立减优惠
     * @param string $value
     **/
    public function SetGoods_tag($value)
    {
        $this->values['goods_tag'] = $value;
    }
    /**
     * 获取商品标记，代金券或立减优惠功能的参数，说明详见代金券或立减优惠的值
     * @return 值
     **/
    public function GetGoods_tag()
    {
        return $this->values['goods_tag'];
    }
    /**
     * 判断商品标记，代金券或立减优惠功能的参数，说明详见代金券或立减优惠是否存在
     * @return true 或 false
     **/
    public function IsGoods_tagSet()
    {
        return array_key_exists('goods_tag', $this->values);
    }


    /**
     * 设置接收微信支付异步通知回调地址
     * @param string $value
     **/
    public function SetNotify_url($value)
    {
        $this->values['notify_url'] = $value;
    }
    /**
     * 获取接收微信支付异步通知回调地址的值
     * @return 值
     **/
    public function GetNotify_url()
    {
        return $this->values['notify_url'];
    }
    /**
     * 判断接收微信支付异步通知回调地址是否存在
     * @return true 或 false
     **/
    public function IsNotify_urlSet()
    {
        return array_key_exists('notify_url', $this->values);
    }


    /**
     * 设置取值如下：JSAPI，NATIVE，APP，详细说明见参数规定
     * @param string $value
     **/
    public function SetTrade_type($value)
    {
        $this->values['trade_type'] = $value;
    }
    /**
     * 获取取值如下：JSAPI，NATIVE，APP，详细说明见参数规定的值
     * @return 值
     **/
    public function GetTrade_type()
    {
        return $this->values['trade_type'];
    }
    /**
     * 判断取值如下：JSAPI，NATIVE，APP，详细说明见参数规定是否存在
     * @return true 或 false
     **/
    public function IsTrade_typeSet()
    {
        return array_key_exists('trade_type', $this->values);
    }


    /**
     * 设置trade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义。
     * @param string $value
     **/
    public function SetProduct_id($value)
    {
        $this->values['product_id'] = $value;
    }
    /**
     * 获取trade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义。的值
     * @return 值
     **/
    public function GetProduct_id()
    {
        return $this->values['product_id'];
    }
    /**
     * 判断trade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义。是否存在
     * @return true 或 false
     **/
    public function IsProduct_idSet()
    {
        return array_key_exists('product_id', $this->values);
    }


    /**
     * 设置trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识。下单前需要调用【网页授权获取用户信息】接口获取到用户的Openid。
     * @param string $value
     **/
    public function SetOpenid($value)
    {
        $this->values['openid'] = $value;
    }
    /**
     * 判断trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识。下单前需要调用【网页授权获取用户信息】接口获取到用户的Openid。 是否存在
     * @return true 或 false
     **/
    public function IsOpenidSet()
    {
        return array_key_exists('openid', $this->values);
    }
    /**
     * 设置上报对应的接口的完整URL，类似：https://api.mch.weixin.qq.com/pay/unifiedorder对于被扫支付，为更好的和商户共同分析一次业务行为的整体耗时情况，对于两种接入模式，请都在门店侧对一次被扫行为进行一次单独的整体上报，上报URL指定为：https://api.mch.weixin.qq.com/pay/micropay/total关于两种接入模式具体可参考本文档章节：被扫支付商户接入模式其它接口调用仍然按照调用一次，上报一次来进行。
     * @param string $value
     **/
    public function SetInterface_url($value)
    {
        $this->values['interface_url'] = $value;
    }
    /**
     * 设置接口耗时情况，单位为毫秒
     * @param string $value
     **/
    public function SetExecute_time_($value)
    {
        $this->values['execute_time_'] = $value;
    }
    /**
     * 设置SUCCESS/FAIL此字段是通信标识，非交易标识，交易是否成功需要查看trade_state来判断
     * @param string $value
     **/
    public function SetReturn_code($value)
    {
        $this->values['return_code'] = $value;
    }
    /**
     * 设置返回信息，如非空，为错误原因签名失败参数格式校验错误
     * @param string $value
     **/
    public function SetReturn_msg($value)
    {
        $this->values['return_msg'] = $value;
    }
    /**
     * 设置SUCCESS/FAIL
     * @param string $value
     **/
    public function SetResult_code($value)
    {
        $this->values['result_code'] = $value;
    }
    /**
     * 设置ORDERNOTEXIST—订单不存在SYSTEMERROR—系统错误
     * @param string $value
     **/
    public function SetErr_code($value)
    {
        $this->values['err_code'] = $value;
    }
    /**
     * 设置结果信息描述
     * @param string $value
     **/
    public function SetErr_code_des($value)
    {
        $this->values['err_code_des'] = $value;
    }
    /**
     * 判断上报对应的接口的完整URL，类似：https://api.mch.weixin.qq.com/pay/unifiedorder对于被扫支付，为更好的和商户共同分析一次业务行为的整体耗时情况，对于两种接入模式，请都在门店侧对一次被扫行为进行一次单独的整体上报，上报URL指定为：https://api.mch.weixin.qq.com/pay/micropay/total关于两种接入模式具体可参考本文档章节：被扫支付商户接入模式其它接口调用仍然按照调用一次，上报一次来进行。是否存在
     * @return true 或 false
     **/
    public function IsInterface_urlSet()
    {
        return array_key_exists('interface_url', $this->values);
    }
    /**
     * 判断随机字符串是否存在
     * @return true 或 false
     **/
    public function IsReturn_codeSet()
    {
        return array_key_exists('nonceStr', $this->values);
    }
    /**
     * 判断SUCCESS/FAIL是否存在
     * @return true 或 false
     **/
    public function IsResult_codeSet()
    {
        return array_key_exists('result_code', $this->values);
    }
    /**
     * 判断发起接口调用时的机器IP 是否存在
     * @return true 或 false
     **/
    public function IsUser_ipSet()
    {
        return array_key_exists('user_ip', $this->values);
    }
    /**
     * 判断接口耗时情况，单位为毫秒是否存在
     * @return true 或 false
     **/
    public function IsExecute_time_Set()
    {
        return array_key_exists('execute_time_', $this->values);
    }
    /**
     * 设置发起接口调用时的机器IP
     * @param string $value
     **/
    public function SetUser_ip($value)
    {
        $this->values['user_ip'] = $value;
    }
    /**
     * 设置系统时间，格式为yyyyMMddHHmmss，如2009年12月27日9点10分10秒表示为20091227091010。其他详见时间规则
     * @param string $value
     **/
    public function SetTime($value)
    {
        $this->values['time'] = $value;
    }
    /**
     *
     * 上报数据， 上报的时候将屏蔽所有异常流程
     * @param string $usrl
     * @param int $startTimeStamp
     * @param array $data
     */
    private function reportCostTime($url, $startTimeStamp, $data)
    {
        //如果不需要上报数据
        if($this->wxpay_config['report_levenl'] == 0){
            return;
        }
        //如果仅失败上报
        if($this->wxpay_config['report_levenl'] == 1 &&
            array_key_exists("return_code", $data) &&
            $data["return_code"] == "SUCCESS" &&
            array_key_exists("result_code", $data) &&
            $data["result_code"] == "SUCCESS")
        {
            return;
        }

        //上报逻辑
        $endTimeStamp = $this->getMillisecond();
        $this->SetInterface_url($url);
        $this->SetExecute_time_($endTimeStamp - $startTimeStamp);
        //返回状态码
        if(array_key_exists("return_code", $data)){
            $this->SetReturn_code($data["return_code"]);
        }
        //返回信息
        if(array_key_exists("return_msg", $data)){
            $this->SetReturn_msg($data["return_msg"]);
        }
        //业务结果
        if(array_key_exists("result_code", $data)){
            $this->SetResult_code($data["result_code"]);
        }
        //错误代码
        if(array_key_exists("err_code", $data)){
            $this->SetErr_code($data["err_code"]);
        }
        //错误代码描述
        if(array_key_exists("err_code_des", $data)){
            $this->SetErr_code_des($data["err_code_des"]);
        }
        //商户订单号
        if(array_key_exists("out_trade_no", $data)){
            $this->SetOut_trade_no($data["out_trade_no"]);
        }
        //设备号
        if(array_key_exists("device_info", $data)){
            $this->SetDevice_info($data["device_info"]);
        }

        try{
            $this->report();
        } catch (\Exception $e){
            //不做任何处理
        }
    }
    /**
     *
     * 测速上报，该方法内部封装在report中，使用时请注意异常流程
     * WxPayReport中interface_url、return_code、result_code、user_ip、execute_time_必填
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param int $timeOut
     * @throws WxPayException
     * @return 成功时返回，其他抛异常
     */
    public function report($timeOut = 1)
    {
        $url = "https://api.mch.weixin.qq.com/payitil/report";
        //检测必填参数
        if(!$this->IsInterface_urlSet()) {
            throw new \Exception("接口URL，缺少必填参数interface_url！");
        } if(!$this->IsReturn_codeSet()) {
        throw new \Exception("返回状态码，缺少必填参数return_code！");
    } if(!$this->IsResult_codeSet()) {
        throw new \Exception("业务结果，缺少必填参数result_code！");
    } if(!$this->IsUser_ipSet()) {
        throw new \Exception("访问接口IP，缺少必填参数user_ip！");
    } if(!$this->IsExecute_time_Set()) {
        throw new \Exception("接口耗时，缺少必填参数execute_time_！");
    }
        $this->SetAppid($this->wxpay_config['appid']);//公众账号ID
        $this->SetMch_id($this->wxpay_config['mchid']);//商户号
        $this->SetUser_ip($_SERVER['REMOTE_ADDR']);//终端ip
        $this->SetTime(date("YmdHis"));//商户上报时间
        $this->SetNonce_str($this->getNonceStr());//随机字符串

        $this->SetSign();//签名
        $xml = $this->ToXml();

        $startTimeStamp = $this->getMillisecond();//请求开始时间
        $response = $this->postXmlCurl($xml, $url, false, $timeOut);
        return $response;
    }
    /**
     * 以post方式提交xml到对应的接口url
     *
     * @param string $xml  需要post的xml数据
     * @param string $url  url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second   url执行超时时间，默认30s
     * @throws WxPayException
     */
    private function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);

        //如果有配置代理这里就设置代理
        if($this->wxpay_config['curl_proxy_host'] != "0.0.0.0"
            && $this->wxpay_config['curl_proxy_port'] != 0){
            curl_setopt($ch,CURLOPT_PROXY, $this->wxpay_config['curl_proxy_host']);
            curl_setopt($ch,CURLOPT_PROXYPORT, $this->wxpay_config['curl_proxy_host']);
        }
        curl_setopt($ch,CURLOPT_URL, $url);
        //curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);//注释掉原有设置
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        //curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验(注释掉了原有设置)
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);//
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if($useCert == true){
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, $this->wxpay_config['sslcert_path']);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, $this->wxpay_config['sslkey_path']);
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            exit("curl出错，错误码:$error");
        }
    }
    /**
     *
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    public static function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }
    /**
     *
     * 检测签名
     */
    public function CheckSign()
    {
        //fix异常
        if(!$this->IsSignSet()){
            throw new \Exception("签名错误！");
        }

        $sign = $this->MakeSign();
        if($this->GetSign() == $sign){
            return true;
        }
        throw new \Exception("签名错误！");
    }
    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public function Init($xml)
    {
        $this->FromXml($xml);
        //fix bug 2015-06-29
        if($this->values['return_code'] != 'SUCCESS'){
            return $this->GetValues();
        }
        $this->CheckSign();
        return $this->GetValues();
    }
    /**
     * 获取毫秒级别的时间戳
     */
    private static function getMillisecond()
    {
        //获取毫秒的时间戳
        $time = explode ( " ", microtime () );
        $time = $time[1] . ($time[0] * 1000);
        $time2 = explode( ".", $time );
        $time = $time2[0];
        return $time;
    }
}

interface ILogHandler
{
    public function write($msg);

}

class CLogFileHandler implements ILogHandler
{
    private $handle = null;

    public function __construct($file = '')
    {
        $this->handle = fopen($file,'a');
    }

    public function write($msg)
    {
        fwrite($this->handle, $msg, 4096);
    }

    public function __destruct()
    {
        fclose($this->handle);
    }
}

class Log
{
    private $handler = null;
    private $level = 15;

    private static $instance = null;

    private function __construct(){}

    private function __clone(){}

    public static function Init($handler = null,$level = 15)
    {
        if(!self::$instance instanceof self)
        {
            self::$instance = new self();
            self::$instance->__setHandle($handler);
            self::$instance->__setLevel($level);
        }
        return self::$instance;
    }


    private function __setHandle($handler){
        $this->handler = $handler;
    }

    private function __setLevel($level)
    {
        $this->level = $level;
    }

    public static function DEBUG($msg)
    {
        self::$instance->write(1, $msg);
    }

    public static function WARN($msg)
    {
        self::$instance->write(4, $msg);
    }

    public static function ERROR($msg)
    {
        $debugInfo = debug_backtrace();
        $stack = "[";
        foreach($debugInfo as $key => $val){
            if(array_key_exists("file", $val)){
                $stack .= ",file:" . $val["file"];
            }
            if(array_key_exists("line", $val)){
                $stack .= ",line:" . $val["line"];
            }
            if(array_key_exists("function", $val)){
                $stack .= ",function:" . $val["function"];
            }
        }
        $stack .= "]";
        self::$instance->write(8, $stack . $msg);
    }

    public static function INFO($msg)
    {
        self::$instance->write(2, $msg);
    }

    private function getLevelStr($level)
    {
        switch ($level)
        {
            case 1:
                return 'debug';
                break;
            case 2:
                return 'info';
                break;
            case 4:
                return 'warn';
                break;
            case 8:
                return 'error';
                break;
            default:

        }
    }

    protected function write($level,$msg)
    {
        if(($level & $this->level) == $level )
        {
            $msg = '['.date('Y-m-d H:i:s').']['.$this->getLevelStr($level).'] '.$msg."\n";
            $this->handler->write($msg);
        }
    }
}
