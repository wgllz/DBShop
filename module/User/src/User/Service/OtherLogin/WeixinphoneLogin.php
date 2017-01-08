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

class WeixinphoneLogin
{
    const VERSION               = "1.0";//DBShop自行加入的版本号
    const GET_AUTH_CODE_URL     = "https://open.weixin.qq.com/connect/qrconnect";
    const GET_ACCESS_TOKEN_URL  = "https://api.weixin.qq.com/sns/oauth2/access_token";
    const GET_USERINFO_URL      = "https://api.weixin.qq.com/sns/userinfo";

    const MOBILE_GET_AUTH_CODE_URL = "https://open.weixin.qq.com/connect/oauth2/authorize";

    private $loginConfig;
    private $loginSession;

    public $redirectUri;

    public function __construct()
    {
        $configReader = new \Zend\Config\Reader\Ini();
        if(empty($this->loginConfig) and file_exists(DBSHOP_PATH . '/data/moduledata/User/OtherLogin.ini')) {
            $config = $configReader->fromFile(DBSHOP_PATH . '/data/moduledata/User/OtherLogin.ini');
            if(isset($config['Weixinphone']) and !empty($config['Weixinphone'])) $this->loginConfig = $config['Weixinphone'];
        }
        if(empty($this->loginSession)) {
            $this->loginSession = new Container('weixin_login_session');
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
        $redirectUrl = urlencode($this->redirectUri);
        $loginKey["appid"] = $this->loginConfig['app_id'];
        $loginKey["redirect_uri"] = "$redirectUrl";
        $loginKey["response_type"] = "code";
        $loginKey["scope"] = "snsapi_userinfo";
        $loginKey["state"] = md5(time());

        $this->loginSession->login_state = $loginKey['state'];

        $bizString = $this->ToUrlParams($loginKey)."#wechat_redirect";
        $loginUrl  = "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;

        header("Location: {$loginUrl}");
        exit;
    }
    /**
     * 返回信息操作
     */
    public function callBack()
    {
        if($this->loginSession->login_state != $_GET['state']) {
            exit('<h2>The state does not match. You may be a victim of CSRF.</h2>');
        }
        $loginKey = array(
            'grant_type'    => 'authorization_code',
            'appid'         => $this->loginConfig['app_id'],
            'secret'        => $this->loginConfig['app_secret'],
            'code'          => $_GET['code']
        );
        $tokenUrl = $this->combineURL(self::GET_ACCESS_TOKEN_URL, $loginKey);
        $response = $this->getContents($tokenUrl);

        if(strpos($response, "access_token") !== false){
            $msg = json_decode($response);

            if(isset($msg->errcode)){
                exit('error:' . $msg->errcode . 'error:' . $msg->errmsg);
            }
        }

        $this->loginSession->access_token = $msg->access_token;
        $this->loginSession->openid = $msg->openid;

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
     * 获取唯一ID，通用
     * @return mixed
     */
    public function getUnionId()
    {
        $infoKey = array(
            'access_token'       => $this->loginSession->access_token,
            'openid'             => $this->loginSession->openid
        );

        $response       = json_decode($this->get(self::GET_USERINFO_URL, $infoKey));
        $responseArray  = $this->objToArr($response);

        //检查返回ret判断api是否成功调用
        if(isset($responseArray['openid']) and !empty($responseArray['openid'])){
            return $responseArray['unionid'];
        }else{
            exit('error:' . $response->errcode . 'error:' . $response->errmsg);
        }
    }
    /**
     * 获取会员信息
     * @return array
     */
    public function getOtherInfo()
    {
        $infoKey = array(
            'access_token'       => $this->loginSession->access_token,
            'openid'             => $this->loginSession->openid
        );

        $response       = json_decode($this->get(self::GET_USERINFO_URL, $infoKey));
        $responseArray  = $this->objToArr($response);

        //检查返回ret判断api是否成功调用
        if(isset($responseArray['openid']) and !empty($responseArray['openid'])){
            return $responseArray;
        }else{
            exit('error:' . $response->errcode . 'error:' . $response->errmsg);
        }
    }
    /**
     * 清除session
     */
    public function clearLoginSession()
    {
        $loginSession = new \Zend\Session\Container();
        $loginSession->getManager()->getStorage()->clear('weinxi_login_session');
    }
    /**
     * get
     * get方式请求资源
     * @param string $url     基于的baseUrl
     * @param array $keysArr  参数列表数组
     * @return string         返回的资源内容
     */
    private function get($url, $keysArr)
    {
        $combined = $this->combineURL($url, $keysArr);
        return $this->getContents($combined);
    }
    /**
     * combineURL
     * 拼接url
     * @param string $baseURL   基于的url
     * @param array  $keysArr   参数列表数组
     * @return string           返回拼接的url
     */
    private function combineURL($baseURL,$keysArr){
        $combined = $baseURL."?";
        $valueArr = array();

        foreach($keysArr as $key => $val){
            $valueArr[] = "$key=".urlencode($val);
        }

        $keyStr = implode("&",$valueArr);
        $combined .= ($keyStr);

        return $combined;
    }
    /**
     *
     * 拼接签名字符串
     * @param array $urlObj
     *
     * @return 返回已经拼接好的字符串
     */
    private function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v)
        {
            if($k != "sign"){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }
    /**
     * getContents
     * 服务器通过get请求获得内容
     * @param string $url       请求的url,拼接后的
     * @return string           请求返回的内容
     */
    public function getContents($url){
        if (ini_get("allow_url_fopen") == "1") {
            $response = file_get_contents($url);
        }else{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_URL, $url);
            $response =  curl_exec($ch);
            curl_close($ch);
        }

        //-------请求为空
        if(empty($response)){
            exit('<h2>可能是服务器无法请求https协议</h2>可能未开启curl支持,请尝试开启curl支持，重启web服务器，如果问题仍未解决，请联系我们http://bbs.dbshop.net/');
        }

        return $response;
    }
    //php 对象到数组转换
    private function objToArr($obj){
        if(!is_object($obj) && !is_array($obj)) {
            return $obj;
        }
        $arr = array();
        foreach($obj as $k => $v){
            $arr[$k] = $this->objToArr($v);
        }
        return $arr;
    }
}