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

namespace User\Service\OtherLogin;


use Zend\Session\Container;

class QqLogin {

    const VERSION               = "2.0";
    const GET_AUTH_CODE_URL     = "https://graph.qq.com/oauth2.0/authorize";
    const GET_ACCESS_TOKEN_URL  = "https://graph.qq.com/oauth2.0/token";
    const GET_OPENID_URL        = "https://graph.qq.com/oauth2.0/me";

    private $loginConfig;
    private $loginSession;

    public $redirectUri;

    public function __construct()
    {
        $configReader = new \Zend\Config\Reader\Ini();
        if(empty($this->loginConfig) and file_exists(DBSHOP_PATH . '/data/moduledata/User/OtherLogin.ini')) {
            $config = $configReader->fromFile(DBSHOP_PATH . '/data/moduledata/User/OtherLogin.ini');
            if(isset($config['QQ']) and !empty($config['QQ'])) $this->loginConfig = $config['QQ'];
        }

        if(empty($this->loginSession)) {
            $this->loginSession = new Container('qq_login_session');
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
     * qq登录操作
     */
    public function toLogin()
    {
        $loginKey = array(
            'response_type' => 'code',
            'client_id'     => $this->loginConfig['app_id'],
            //'redirect_uri'  => $this->loginConfig['redirect_uri'],
            'redirect_uri'  => $this->redirectUri,
            'state'         => md5(uniqid(rand(), TRUE)),
            'scope'         => 'get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo,check_page_fans,add_t,add_pic_t,del_t,get_repost_list,get_info,get_other_info,get_fanslist,get_idolist,add_idol,del_idol,get_tenpay_addr'
        );

        $this->loginSession->login_state = $loginKey['state'];

        $loginUrl   = $this->combineURL(self::GET_AUTH_CODE_URL, $loginKey);

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
            'client_id'     => $this->loginConfig['app_id'],
            //'redirect_uri'  => $this->loginConfig['redirect_uri'],
            'redirect_uri'  => $this->redirectUri,
            'client_secret' => $this->loginConfig['app_key'],
            'code'          => $_GET['code']
        );
        $tokenUrl = $this->combineURL(self::GET_ACCESS_TOKEN_URL, $loginKey);
        $response = $this->getContents($tokenUrl);

        if(strpos($response, "callback") !== false){
            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
            $msg = json_decode($response);

            if(isset($msg->error)){
                exit('error:' . $msg->error . 'error:' . $msg->error_description);
            }
        }

        $params = array();
        parse_str($response, $params);

        $this->loginSession->access_token = $params['access_token'];

        return true;
    }
    /**
     * 获取会员信息
     * @return array
     */
    public function getOtherInfo()
    {
        $infoKey = array(
            'oauth_consumer_key' => $this->loginConfig['app_id'],
            'access_token'       => $this->loginSession->access_token,
            'openid'             => $this->loginSession->openid,
            'format'             => 'json'
        );

        $response       = json_decode($this->get('https://graph.qq.com/user/get_user_info', $infoKey));
        $responseArray  = $this->objToArr($response);

        //检查返回ret判断api是否成功调用
        if($responseArray['ret'] == 0){
            return $responseArray;
        }else{
            exit('error:' . $response->ret . 'error:' . $response->msg);
        }
    }
    /**
     * 获取Id值，唯一值，可用来识别用户
     * @return mixed
     */
    public function getOpenId()
    {
        $openKey = array(
            'access_token' => $this->loginSession->access_token
        );
        $openUrl  = $this->combineURL(self::GET_OPENID_URL, $openKey);
        $response = $this->getContents($openUrl);
        //--------检测错误是否发生
        if(strpos($response, "callback") !== false){

            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response = substr($response, $lpos + 1, $rpos - $lpos -1);
        }

        $user = json_decode($response);
        if(isset($user->error)){
            exit('error:' . $user->error . 'error:' . $user->error_description);
        }

        //------记录openid
        $this->loginSession->openid = $user->openid;

        return $user->openid;
    }
    /**
     * 清除session
     */
    public function clearLoginSession()
    {
        $loginSession = new \Zend\Session\Container();
        $loginSession->getManager()->getStorage()->clear('qq_login_session');
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