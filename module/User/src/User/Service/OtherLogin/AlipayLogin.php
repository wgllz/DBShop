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

namespace User\Service\OtherLogin;

use Zend\Session\Container;

class AlipayLogin
{
    const VERSION               = "1.0";//DBShop自行加入的版本号
    const GET_GATEWAY_URL       = "https://mapi.alipay.com/gateway.do?";

    private $https_verify_url   = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
    private $http_verify_url    = 'http://notify.alipay.com/trade/notify_query.do?';

    private $loginConfig;
    private $loginSession;

    public $redirectUri;

    public function __construct()
    {
        $configReader = new \Zend\Config\Reader\Ini();
        if(empty($this->loginConfig) and file_exists(DBSHOP_PATH . '/data/moduledata/User/OtherLogin.ini')) {
            $config = $configReader->fromFile(DBSHOP_PATH . '/data/moduledata/User/OtherLogin.ini');
            if(isset($config['Alipay']) and !empty($config['Alipay'])) {
                $httpType = ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https' : 'http';

                $this->loginConfig = $config['Alipay'];
                //目标服务地址
                $this->loginConfig['target_service']    = 'user.auth.quick.login';

                $this->loginConfig['partner']           = $this->loginConfig['alipay_pid'];
                $this->loginConfig['key']               = $this->loginConfig['alipay_key'];
                //防钓鱼时间戳,若要使用请调用类文件submit中的query_timestamp函数
                $this->loginConfig['anti_phishing_key'] = '';
                //客户端的IP地址,非局域网的外网IP地址，如：221.0.0.1
                $this->loginConfig['exter_invoke_ip']   = $_SERVER["REMOTE_ADDR"];
                //签名方式
                $this->loginConfig['sign_type']         = 'MD5';
                //字符编码格式
                $this->loginConfig['input_charset']     = 'utf-8';
                //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
                $this->loginConfig['transport']         = $httpType;
                //ca证书路径地址，用于curl中ssl校验
                $this->loginConfig['cacert']            = DBSHOP_PATH . '/module/Payment/src/Payment/Service/api/alipay/cacert.pem';
            }
        }
        if(empty($this->loginSession)) {
            $this->loginSession = new Container('alipay_login_session');
        }
    }
    /**
     * 获取当前的登录配置信息是否存在
     * @return string
     */
    public function getLoginConfigState()
    {
        if(empty($this->loginConfig) or $this->loginConfig['login_state'] == 'false') return 'configError';

        return true;
    }
    /**
     * 微信的第一次请求操作
     */
    public function toLogin()
    {
        $loginKey = array(
            "service"           => 'alipay.auth.authorize',
            "partner"           => $this->loginConfig['partner'],
            "target_service"	=> $this->loginConfig['target_service'],
            "return_url"        => $this->redirectUri,
            "anti_phishing_key"	=> $this->loginConfig['anti_phishing_key'],
            "exter_invoke_ip"	=> $this->loginConfig['exter_invoke_ip'],
            "_input_charset"	=> $this->loginConfig['input_charset']
        );

        $loginForm = $this->buildRequestForm($loginKey, 'get','Login To');
        echo $loginForm;exit;
    }
    /**
     * 返回信息操作
     */
    public function callBack()
    {
        //验证结果
        $verify_result = $this->verifyReturn();
        if($verify_result) {
            $this->loginSession->openid = $_GET['user_id'];
        }
        return true;
    }
    /**
     * 获取Id值，唯一值，可用来识别用户
     * @return mixed
     */
    public function getOpenId()
    {
        return $this->loginSession->openid;
    }
    /**
     * 获取会员信息
     * @return array
     */
    public function getOtherInfo()
    {
        return array('nickname'=>'');
    }
    /**
     * 清除session
     */
    public function clearLoginSession()
    {
        $loginSession = new \Zend\Session\Container();
        $loginSession->getManager()->getStorage()->clear('alipay_login_session');
    }
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
        switch (strtoupper(trim($this->loginConfig['sign_type']))) {
            case "MD5" :
                $mysign = $this->md5Sign($prestr, $this->loginConfig['key']);
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
        $para_sort['sign_type'] = strtoupper(trim($this->loginConfig['sign_type']));

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
        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".self::GET_GATEWAY_URL."_input_charset=".trim(strtolower($this->loginConfig['input_charset']))."' method='".$method."'>";
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
        $sResult = $this->getHttpResponsePOST(self::GET_GATEWAY_URL, $this->loginConfig['cacert'],$request_data,trim(strtolower($this->loginConfig['input_charset'])));

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
        $sResult = $this->getHttpResponsePOST(self::GET_GATEWAY_URL, $this->loginConfig['cacert'],$para,trim(strtolower($this->loginConfig['input_charset'])));

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
        switch (strtoupper(trim($this->loginConfig['sign_type']))) {
            case "MD5" :
                $isSgin = $this->md5Verify($prestr, $sign, $this->loginConfig['key']);
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
        $transport = strtolower(trim($this->loginConfig['transport']));
        $partner = trim($this->loginConfig['partner']);
        $veryfy_url = '';
        if($transport == 'https') {
            $veryfy_url = $this->https_verify_url;
        }
        else {
            $veryfy_url = $this->http_verify_url;
        }
        $veryfy_url = $veryfy_url."partner=" . $partner . "&notify_id=" . $notify_id;
        $responseTxt = $this->getHttpResponseGET($veryfy_url, $this->loginConfig['cacert']);

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