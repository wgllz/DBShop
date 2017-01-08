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

namespace Shopfront\Helper;

use Zend\Session\Container;
use Zend\View\Helper\AbstractHelper;

class Helper extends AbstractHelper
{
    private $iniReader;
    private $userSession;     //会员session
    private $cartSession;     //购物车session
    private $orderStateArray; //订单状态
    private $goodsConfig;     //商品设置
    private $storageConfig;   //存储设置

    public  $currencySession; //货币session
    
    protected $dbshopSql;
    protected $dbshopResultSet;

    public function __construct ()
    {
        if(empty($this->iniReader)) {
            $this->iniReader = new \Zend\Config\Reader\Ini();
        }
        if(empty($this->storageConfig)) {
            $this->storageConfig = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/Upload/Storage.ini');
        }
        if(empty($this->userSession)) {
            $this->userSession = new Container('user_info');
        }
        if(empty($this->currencySession)) {
            $this->currencySession = new Container('currency_session');
        }
        if(empty($this->cartSession)) {
            $this->cartSession = new Container('dbshop_cart');
            if(!isset($this->cartSession->cart)) {
                $this->cartSession->cart = array();
            }
        }
        if(empty($this->orderStateArray)) {
            $this->orderStateArray[0]  =  '已取消';
            $this->orderStateArray[10]  = '待付款';
            $this->orderStateArray[15]  = '付款中';
            $this->orderStateArray[20]  = '已付款';
            $this->orderStateArray[30]  = '待发货';
            $this->orderStateArray[40]  = '已发货';
            $this->orderStateArray[60]  = '订单完成';
        }
    }
    /**
     * 获取当前域名
     * @return mixed
     */
    public function dbshopHttpHost()
    {
        $httpHost = $_SERVER['HTTP_HOST'];
        return $httpHost;
    }
    /**
     * 获取当前访问协议是http:// 还是 https://
     * @return string
     */
    public function dbshopHttpOrHttps()
    {
        $httpType = ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';

        return $httpType;
    }
    /**
     * 获取系统设置信息
     * @param $name
     * @return mixed
     */
    public function websiteInfo ($name)
    {
        $array  = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/System/config.ini');
        return (isset($array['shop_system'][$name]) ? $array['shop_system'][$name] : null);
    }
    /**
     * 获取后台附件设置中，商品上传信息
     * @param $typeName
     * @param $valueName
     * @return mixed
     */
    public function getGoodsUploadIni($typeName, $valueName)
    {
        $array  = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/Upload/Goods.ini');
        return (isset($array[$typeName][$valueName]) ? $array[$typeName][$valueName] : null);
    }
    /**
     * 获取分析设置信息
     * @param $valueName
     * @return string
     */
    public function getAnalyticsIni($valueName)
    {
        $fileIni = DBSHOP_PATH . '/data/moduledata/Analytics/Analytics.ini';
        if(file_exists($fileIni)) {
            $array  = $this->iniReader->fromFile($fileIni);
            return (isset($array[$valueName]) ? $array[$valueName] : null);
        }
        return '';
    }
    /**
     * 获取系统后台设置的内容信息及统计代码
     * @param $name
     * @return string
     */
    public function getSystemContent($name)
    {
        $array = array('buy_service'=>'buy_service.ini', 'buy'=>'buy.ini', 'goods_quality'=>'goods_quality.ini', 'statistics'=>'statistics.ini');
        if($array[$name] == '') return ;
        $content = @file_get_contents(DBSHOP_PATH . '/data/moduledata/System/' . $array[$name]);
        return $content;
    }
    /**
     * 获取会员设置信息
     * @param $name
     * @return mixed
     */
    public function getUserIni($name)
    {
        $array = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/User/User.ini');
        return (isset($array[$name]) ? $array[$name] : null);
    }
    /**
     * 获取第三方登录设置信息
     * @return array
     */
    public function getUserOtherLoginIni()
    {
        $array = array();
        if(file_exists(DBSHOP_PATH . '/data/moduledata/User/OtherLogin.ini')) {
            $array = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/User/OtherLogin.ini');
        }
        //如果是在电脑端则去除微信内登录
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false) {
            unset($array['Weixinphone']);
        } else {
            unset($array['Weixin']);
        }
        return $array;
    }
    /**
     * 检查第三方登录是否有开启的登录
     * @return string
     */
    public function getUserOtherLoginState()
    {
        $state = 'false';
        $array = array();
        if(file_exists(DBSHOP_PATH . '/data/moduledata/User/OtherLogin.ini')) {
            $array = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/User/OtherLogin.ini');
        }
        if(!empty($array)) {
            foreach($array as $value) {
                if($value['login_state'] == 'true') {
                    $state = 'true';
                    break;
                }
            }
        }
        return $state;
    }
    /*-----------------------------------会员登录----------------------------------------*/
    /**
     * 设置会员登录session
     * @param $array
     */
    public function setUserSession ($array)
    {
        if(is_array($array) and !empty($array)) {
            foreach($array as $key => $val) {
                $this->userSession->$key = $val;
            }
        }
    }
    /**
     * 获取会员登录session中的值
     * @param $name
     * @return null|string
     */
    public function getUserSession ($name)
    {
        return (isset($this->userSession->$name) ? $this->userSession->$name : '');
    }
    /*-----------------------------------会员登录----------------------------------------*/
    
    /*-----------------------------------前台客服----------------------------------------*/
    /**
     * 前台客服
     * @param $fileName
     * @return string
     */
    public function getOnlineService($fileName)
    {
        if(file_exists(DBSHOP_PATH . '/data/moduledata/System/online/' . $fileName . '.php'))
        return @file_get_contents(DBSHOP_PATH . '/data/moduledata/System/online/' . $fileName . '.php');
        else
        return '';
    }
    /*-----------------------------------前台客服----------------------------------------*/
    
    /*-----------------------------------购物车处理----------------------------------------*/
    /**
     * 设置购物车商品
     * @param $key
     * @param $val
     */
    public function setCartSession ($key, $val)
    {
		//$this->cartSession->cart[$key] = $val;
		//这里进行一步处理，这是一个极个别的现象，使用 $this->cartSession->cart[$key] = $val; 这样的方式赋值不成功，所以才有下面的代码，其他购物车代码类似
		$array = $this->cartSession->cart;
		$array[$key] = $val;
		$this->cartSession->cart = $array;
    }
    /**
     * 编辑购物车商品
     * @param $key
     * @param $type
     * @param $value
     */
    public function editCartSession($key, $type, $value)
    {
		$array = $this->cartSession->cart;
		$array[$key][$type] = $value;
        $this->cartSession->cart = $array;
    }
    /**
     * 获取单个购物车内商品信息
     * @param $key
     * @param null $type
     * @return mixed
     */
    public function getCartOneGoodsSession($key, $type=null)
    {
		$array = $this->cartSession->cart;
        return ($type != '' ? $array[$key][$type] : $array[$key]);
    }
    /**
     * 检查购物车内该商品是否存在
     * @param $key
     * @return bool|null
     */
    public function checkCartSession ($key)
    {
		$array = $this->cartSession->cart;
        if(isset($array[$key]) and !empty($array[$key])) return null;
        return true;
    }
    /**
     * 购物车删除单个商品
     * @param $key
     */
    public function delCartSession ($key)
    {
		$array = $this->cartSession->cart;
        unset($array[$key]);
		$this->cartSession->cart = $array;
    }
    /**
     * 清空购物车商品
     */
    public function clearCartSession()
    {
    	$this->cartSession->getManager()->getStorage()->clear('dbshop_cart');
    }
    /**
     * 购物车商品种类数
     * @return int
     */
    public function cartGoodsNum ()
    {
        return count($this->cartSession->cart);
    }
    /**
     * 获取购物车商品总价
     * @return int
     */
    public function getCartTotal ()
    {
        $total = 0;
        foreach ($this->cartSession->cart as $value) {
            $total = $total + $this->shopPrice($value['goods_shop_price']) * $value['buy_num'];
        }
        return $total;
    }
    /**
     * 获取购物车商品重量
     * @return int
     */
    public function getCartTotalWeight ()
    {
        $totalWeight = 0;
        foreach ($this->cartSession->cart as $value) {
            $totalWeight = $totalWeight + $value['goods_weight'] * $value['buy_num'];
        }
        return $totalWeight;
    }
    /**
     * 获取购物车中可以使用的积分数（消费积分）
     * @return int
     */
    public function getCartTotalIntegral ()
    {
        $totalIntegral = 0;
        foreach ($this->cartSession->cart as $value) {
            if(isset($value['integral_num']) and $value['integral_num']>0) $totalIntegral = $totalIntegral + $value['integral_num'];
        }
        return $totalIntegral;
    }
    /**
     * 获取购物车
     * @return mixed
     */
    public function getCartSession ()
    {
        return $this->cartSession->cart;
    }
    /*-----------------------------------购物车处理----------------------------------------*/
    
    /*-----------------------------------订单状态----------------------------------------*/
    public function getOrderState()
    {
        return $this->orderStateArray;
    }
    public function getOneOrderStateInfo($orderState)
    {
        return $this->orderStateArray[$orderState];
    }
    /*-----------------------------------订单状态----------------------------------------*/
    
    /*-----------------------------------货币信息----------------------------------------*/
    /**
     * 获取货币信息设置
     * @return array
     */
    public function getFrontCurrency()
    {
        $array = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/Currency/Currency.ini');
        $currencyArray = array();
        if(is_array($array) and !empty($array)) {
            foreach($array as $key => $value) {
                if($value['currency_state'] == 1) $currencyArray[$key] = $value;
            }
        }
        return $currencyArray;
    }

    /**
     * 获取默认货币信息内容
     * @return mixed
     */
    public function getFrontDefaultCurrency()
    {
        if(isset($this->currencySession->default_currency) and $this->currencySession->default_currency) {
            return $this->currencySession->default_currency;
        } else {
            $currencyArray = $this->getFrontCurrency();
            foreach ($currencyArray as $c_value) {
                if($c_value['currency_type'] == '1') {
                    $this->currencySession->default_currency        = $c_value['currency_code'];
                    $this->currencySession->default_currency_rate   = $c_value['currency_rate'];
                    $this->currencySession->default_currency_symbol = $c_value['currency_symbol'];
                    $this->currencySession->default_currency_unit   = $c_value['currency_unit'];
                    $this->currencySession->default_currency_decimal= $c_value['currency_decimal'];
                }
            }
            return $this->currencySession->default_currency;
        }
    }

    /**
     * 设置默认货币信息
     * @param $currencyCode
     */
    public function setFrontDefaultCurrency($currencyCode)
    {
        $array = $this->getFrontCurrency();
        if(!empty($currencyCode) and $array[$currencyCode] != '') {
            $this->currencySession->default_currency        = $currencyCode;
            $this->currencySession->default_currency_rate   = $array[$currencyCode]['currency_rate'];
            $this->currencySession->default_currency_symbol = $array[$currencyCode]['currency_symbol'];
            $this->currencySession->default_currency_unit   = $array[$currencyCode]['currency_unit'];
            $this->currencySession->default_currency_decimal= $array[$currencyCode]['currency_decimal'];
        }
    }
    /*-----------------------------------货币信息----------------------------------------*/
    
    /*-----------------------------------导航信息----------------------------------------*/
    /**
     * 获取导航信息
     * @param $type
     * @return array|mixed
     */
    public function shopFrontMenu($type)
    {
        $array = array();
        $file = DBSHOP_PATH . '/data/moduledata/Navigation/' . $type . '.php';
        if(file_exists($file)) {
            return include $file;
        }
        return $array;
    }
    /*-----------------------------------货币信息----------------------------------------*/
    
    /*-----------------------------------商品价格转换----------------------------------------*/
    /**
     * 带前后缀的价格
     * @param $price
     * @return string
     */
    public function shopPriceExtend($price)
    {
        if(!isset($this->currencySession->default_currency)) $this->getFrontDefaultCurrency();
        return $this->currencySession->default_currency_symbol . number_format($price * $this->currencySession->default_currency_rate, $this->currencySession->default_currency_decimal, '.', '') . $this->currencySession->default_currency_unit;
    }
    /**
     * 不带前后缀的价格
     * @param $price
     * @return string
     */
    public function shopPrice($price)
    {
        if(!isset($this->currencySession->default_currency)) $this->getFrontDefaultCurrency();
        return number_format($price * $this->currencySession->default_currency_rate, $this->currencySession->default_currency_decimal, '.', '');
    }
    /**
     * 货币
     * @return mixed
     */
    public function shopCurrency()
    {
        if(!isset($this->currencySession->default_currency)) $this->getFrontDefaultCurrency();
        return $this->currencySession->default_currency;
    }
    /**
     * 货币符号
     * @return mixed
     */
    public function shopPriceSymbol()
    {
        if(!isset($this->currencySession->default_currency)) $this->getFrontDefaultCurrency();
        return $this->currencySession->default_currency_symbol;
    }
    /**
     * 货币汇率
     * @return mixed
     */
    public function shopPriceRate()
    {
        if(!isset($this->currencySession->default_currency)) $this->getFrontDefaultCurrency();
        return $this->currencySession->default_currency_rate;
    }
    /**
     * 货币单位
     * @return mixed
     */
    public function shopPriceUnit()
    {
        if(!isset($this->currencySession->default_currency)) $this->getFrontDefaultCurrency();
        return $this->currencySession->default_currency_unit;
    }
    /*-----------------------------------商品价格转换----------------------------------------*/
    /**
     * 商品图片处理，当为空时显示默认图片(前台商品图片显示)，前台有cdn处理，所以后台单独拿出来
     * @param $goodsImage
     * @return mixed
     */
    public function shopGoodsImage($goodsImage)
    {
        $image = $goodsImage;
        $qiniuHttp  = (isset($this->storageConfig['qiniu_http_type']) ? $this->storageConfig['qiniu_http_type'] : 'http://');
        $aliyunHttp = (isset($this->storageConfig['aliyun_http_type']) ? $this->storageConfig['aliyun_http_type'] : 'http://');

        if(stripos($image, '{qiniu}') !== false) return str_replace('{qiniu}', $qiniuHttp.$this->storageConfig['qiniu_domain'], $image);
        if(stripos($image, '{aliyun}') !== false) return str_replace('{aliyun}', $aliyunHttp.$this->storageConfig['aliyun_domain'], $image);
        if(defined('FRONT_CDN_STATE') and FRONT_CDN_STATE == 'true') {//开启cdn图片加速
            //if(stripos($image, 'http') === false) return FRONT_CDN_HTTP_TYPE . FRONT_CDN_DOMAIN . '/goods/' . basename($image);
            if(stripos($image, 'http') === false) return FRONT_CDN_HTTP_TYPE . FRONT_CDN_DOMAIN . $image;
        }

        if($image == '' or !file_exists(DBSHOP_PATH . $image)) $image = $this->getGoodsUploadIni('goods', 'goods_image_default');
        return $image;
    }
    /**
     * 商品图片处理，当为空时显示默认图片(后台商品图片显示)
     * @param $goodsImage
     * @return mixed
     */
    public function shopadminGoodsImage($goodsImage) {
        $image = $goodsImage;
        $qiniuHttp  = (isset($this->storageConfig['qiniu_http_type']) ? $this->storageConfig['qiniu_http_type'] : 'http://');
        $aliyunHttp = (isset($this->storageConfig['aliyun_http_type']) ? $this->storageConfig['aliyun_http_type'] : 'http://');

        if(stripos($image, '{qiniu}') !== false) return str_replace('{qiniu}', $qiniuHttp.$this->storageConfig['qiniu_domain'], $image);
        if(stripos($image, '{aliyun}') !== false) return str_replace('{aliyun}', $aliyunHttp.$this->storageConfig['aliyun_domain'], $image);

        if($image == '' or !file_exists(DBSHOP_PATH . $image)) $image = $this->getGoodsUploadIni('goods', 'goods_image_default');
        return $image;
    }
    /**
     * 对商品详情进行处理
     * @param $goodsBody
     */
    /**
     * 对商品详情进行处理
     * @param $goodsBody
     * @return mixed
     */
    public function shopGoogsBody($goodsBody)
    {
        if(defined('FRONT_CDN_STATE') and FRONT_CDN_STATE == 'true') {//开启cdn图片加速
            $imageBaseUrl = FRONT_CDN_HTTP_TYPE . FRONT_CDN_DOMAIN;

            preg_match_all('/<img(.*)src="([^"]+)"[^>]+>/isU', $goodsBody, $matches);
            if(isset($matches[2]) and !empty($matches[2])) {
                $images         = $matches[2];
                $patterns       = array();
                $replacements   = array();
                foreach($images as $imageitem) {
                    if(stripos($imageitem, 'http') === false) {
                        //$replacements[] = $imageBaseUrl . '/goods/'. basename($imageitem);
                        $replacements[] = $imageBaseUrl . $imageitem;
                        $patterns[]     = "/".preg_replace("/\//i","\/",$imageitem)."/";
                    }
                }
                if(!empty($replacements)) {
                    ksort($patterns);
                    ksort($replacements);
                    $goodsBody = preg_replace($patterns, $replacements, $goodsBody);
                }
            }
        }
        return $goodsBody;
    }
    /**
     * 获取广告信息
     * @param $adClass
     * @param $adPlace
     * @param string $showType
     * @return null|string
     */
    public function getShopAd($adClass, $adPlace, $showType='pc')
    {
        $adContent = '';
        if($showType == 'pc') {
            $filePath  = DBSHOP_PATH . '/data/moduledata/Ad/' . DBSHOP_TEMPLATE . '/';
        } else {
            $filePath  = DBSHOP_PATH . '/data/moduledata/Ad/mobile/' . (defined('DBSHOP_PHONE_TEMPLATE') ? DBSHOP_PHONE_TEMPLATE : 'default') . '/';
        }

        if(!file_exists($filePath . $adClass . '.ini')) return null;
        
        $adArray   = $this->iniReader->fromFile($filePath . $adClass . '.ini');
        if(isset($adArray[$adPlace]) and is_array($adArray[$adPlace]) and !empty($adArray[$adPlace])) {
            foreach ($adArray[$adPlace] as $value) {
                if($value['state'] == 1 and file_exists($filePath . $value['file'])) {
                    $startTimeState = (isset($value['start_time']) and !empty($value['start_time'])) ? true : false;
                    $endTimeState   = (isset($value['end_time']) and !empty($value['end_time'])) ? true : false;

                    if($startTimeState and $endTimeState and time()>=$value['start_time'] and time()<=$value['end_time']) $adContent .= file_get_contents($filePath . $value['file']);
                    if(!$startTimeState and $endTimeState and time()<=$value['end_time']) $adContent .= file_get_contents($filePath . $value['file']);
                    if($startTimeState and !$endTimeState and time()>=$value['start_time']) $adContent .= file_get_contents($filePath . $value['file']);
                    if(!$startTimeState and !$endTimeState) $adContent .= file_get_contents($filePath . $value['file']);
                }
            }
        }
        return $adContent;
    }
    /*-----------------------------------消息提醒----------------------------------------*/
    /**
     * 获取消息提醒是否发给管理员
     * @param $stateName
     * @return string
     */
    public function getSendMessageAdminEmail($stateName)
    {
        $messageSet = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/System/sendmessage/sendmessage.ini');
        if(isset($messageSet[$stateName]) and $messageSet[$stateName] == '1') {
            return $messageSet['admin_receive_email'];
        }
        return '';
    }
    /**
     * 获取消息提醒是否发给买家
     * @param $stateName
     * @param $buyerEmail
     * @return string
     */
    public function getSendMessageBuyerEmail($stateName, $buyerEmail='')
    {
        if(empty($buyerEmail)) return '';

        $messageSet = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/System/sendmessage/sendmessage.ini');
        if(isset($messageSet['buyer_'.$stateName]) and $messageSet['buyer_'.$stateName] == '1') {
            return $buyerEmail;
        }
        return '';
    }
    /**
     * 获取消息模板内容
     * @param $fileName
     * @return string
     */
    public function getSendMessageBody($fileName)
    {
        $content = @file_get_contents(DBSHOP_PATH . '/data/moduledata/System/sendmessage/' . $fileName . '.php');
        return $content;
    }
    /**
     * 生成消息内容
     * @param $data
     * @param $messageContent
     * @return string
     */
    public function createSendMessageContent($data, $messageContent)
    {
        $sourceArray = array(
                '{shopname}',       //网站名称
                '{buyname}',        //买家名称
                '{ordersn}',        //订单编号
                '{ordertotal}',     //订单金额
                '{expressname}',    //快递公司
                '{expressnumber}',  //快递单号
                '{submittime}',     //订单提交时间
                '{shopurl}',        //网站url
                '{paymenttime}',    //支付完成时间
                '{shiptime}',       //订单发货时间
                '{finishtime}',     //订单完成时间
                '{canceltime}',     //订单取消时间
                '{cancelinfo}',     //订单取消原因
                '{deltime}',        //订单删除时间

                '{askusername}',    //商品咨询者名称
                '{goodsname}',      //商品名称
                '{asktime}',        //商品咨询时间
                '{replyusername}',  //回复者名称
                '{replytime}',      //回复时间
        );
        $bodyArray  = array(
                '{shopname}'   => (isset($data['shopname'])     ? $data['shopname']     : ''),
                '{buyname}'    => (isset($data['buyname'])      ? $data['buyname']      : ''),
                '{ordersn}'    => (isset($data['ordersn'])      ? $data['ordersn']      : ''),
                '{ordertotal}' => (isset($data['ordertotal'])      ? $data['ordertotal']      : ''),
                '{expressname}'=> (isset($data['expressname'])      ? $data['expressname']    : ''),
                '{expressnumber}' => (isset($data['expressnumber']) ? $data['expressnumber']  : ''),
                '{submittime}' => (isset($data['submittime'])   ? date("Y-m-d H:i:s", $data['submittime'])   : ''),
                '{shopurl}'    => (isset($data['shopurl'])      ? '<a href="'. $data['shopurl'] . '" target="_blank">' . $data['shopurl'] . '</a>'     : ''),
                '{paymenttime}'=> (isset($data['paymenttime'])  ? date("Y-m-d H:i:s", $data['paymenttime'])  : ''),
                '{shiptime}'   => (isset($data['shiptime'])     ? date("Y-m-d H:i:s", $data['shiptime'])     : ''),
                '{finishtime}' => (isset($data['finishtime'])   ? date("Y-m-d H:i:s", $data['finishtime'])   : ''),
                '{canceltime}' => (isset($data['canceltime'])   ? date("Y-m-d H:i:s", $data['canceltime'])   : ''),
                '{cancelinfo}' => (isset($data['cancel_info'])  ? trim($data['cancel_info'])                 : ''),
                '{deltime}'    => (isset($data['deltime'])      ? date("Y-m-d H:i:s", $data['deltime'])      : ''),

                '{askusername}'    => (isset($data['askusername'])   ? $data['askusername']                  : ''),
                '{goodsname}'      => (isset($data['goodsname'])     ? $data['goodsname']                    : ''),
                '{asktime}'        => (isset($data['asktime'])       ? date("Y-m-d H:i:s", $data['asktime']) : ''),
                '{replyusername}'  => (isset($data['replyusername']) ? $data['replyusername']                : ''),
                '{replytime}'      => (isset($data['replytime'])     ? date("Y-m-d H:i:s", $data['replytime']): ''),
        );
        //将邮件内容，进行html化处理，防止没有正常显示html格式问题
        $messageContent = htmlspecialchars($messageContent);
        $messageContent = str_replace("\n", "<br>", $messageContent);
        $messageContent = str_replace("  ", "&nbsp;", $messageContent);

        foreach ($sourceArray as $value) {
            $messageContent = str_replace($value, $bodyArray[$value], $messageContent);
        }

        return $messageContent;
    }
    /*-----------------------------------消息提醒----------------------------------------*/

    /*-----------------------------------手机消息提醒----------------------------------------*/
    /**
     * 获取手机短信的配置信息
     * @param $type
     * @param $sign
     * @return string
     */
    public function getIphoneSmsConfig($type, $sign) {
        $config = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/System/phonesms.ini');
        if(isset($config[$type][$sign])) return $config[$type][$sign];

        return '';
    }
    /*-----------------------------------手机消息提醒----------------------------------------*/

    /**
     * 获取特殊标签对应商品信息
     * @param $tagCode
     * @param int $goodsNum
     * @param string $tagType
     * @return array|null
     */
    public function getTagGoodsArray($tagCode, $goodsNum=0, $tagType='pc')
    {
        $goodsNum = intval($goodsNum);
        
        if(empty($this->dbshopSql)) {
            $this->dbshopSql = new \Zend\Db\Adapter\Adapter(include DBSHOP_PATH . '/data/Database.ini.php');
        }
        if(empty($this->dbshopResultSet)) {
            $this->dbshopResultSet = new \Zend\Db\ResultSet\ResultSet();
        }
        $query  = $this->dbshopSql->query('SELECT tag_id FROM dbshop_goods_tag WHERE tag_type=\''.$tagCode.'\' and template_tag=\''.DBSHOP_TEMPLATE.'\' and show_type=\''.$tagType.'\'');
        $result = $query->execute()->current();
        if(isset($result['tag_id'])) {
            $selectSql = "SELECT g.*, e.goods_name, e.goods_extend_name,
                    (SELECT i.goods_thumbnail_image FROM dbshop_goods_image as i WHERE i.goods_image_id=e.goods_image_id) as goods_thumbnail_image, (SELECT in_c.class_id FROM dbshop_goods_in_class as in_c WHERE in_c.goods_id=g.goods_id and in_c.class_state=1 LIMIT 1) as one_class_id
                    FROM dbshop_goods as g
                    INNER JOIN dbshop_goods_extend as e ON e.goods_id=g.goods_id
                    INNER JOIN dbshop_goods_tag_in_goods as t ON t.goods_id=g.goods_id";
            $whereSql  = " WHERE t.tag_id=".$result['tag_id']." and g.goods_state=1";
            $orderSql  = " order by t.tag_goods_sort ASC, t.goods_id DESC";
            $limitSql  = " " . ($goodsNum > 0 ? 'limit '.$goodsNum : '');
            
            $query     = $this->dbshopSql->query($selectSql . $whereSql . $orderSql . $limitSql);
            $goodsArray= $this->dbshopResultSet->initialize($query->execute())->toArray();
            
            return $goodsArray;
        }
        return null;
    }
    /**
     * 特殊标签对应的文章，可以是单页模式，也可以是文章分类中的文章模式
     * @param $tagCode
     * @param string $type single为单页模式
     * @param string $limitNum
     * @return array
     */
    public function getTagArticleArray($tagCode, $type='single', $limitNum='')
    {
        if(empty($this->dbshopSql)) {
            $this->dbshopSql = new \Zend\Db\Adapter\Adapter( include DBSHOP_PATH . '/data/Database.ini.php');
        }
        if(empty($this->dbshopResultSet)) {
            $this->dbshopResultSet = new \Zend\Db\ResultSet\ResultSet();
        }
        if($type == 'single') {//单页模式获取
            $selectSql    = "SELECT * FROM dbshop_single_article as ds INNER JOIN dbshop_single_article_extend as e ON e.single_article_id=ds.single_article_id WHERE ds.article_tag='".$tagCode."' and ds.template_tag='".DBSHOP_TEMPLATE."'";
        }
        if($type == 'cms') {//文章分类中的文章模式(新闻)
            $selectSql = "SELECT article_class_id FROM dbshop_article_class WHERE index_news=1 and article_class_state=1 limit 1";
            $query        = $this->dbshopSql->query($selectSql);
            $array        = $this->dbshopResultSet->initialize($query->execute())->toArray();
            $selectSql = '';
            if(isset($array[0]['article_class_id'])) {
                $where = 'WHERE a.article_state=1 ';
                if($tagCode == 'index_news') $where .= 'and a.article_class_id IN (SELECT c.article_class_id FROM dbshop_article_class as c WHERE (c.article_class_id='.$array[0]['article_class_id'].' or c.article_class_top_id='.$array[0]['article_class_id'].') and c.article_class_state=1)';
                if(!empty($limitNum)) $limit = ' limit '.$limitNum;
                $selectSql   = "SELECT * FROM dbshop_article as a
                            INNER JOIN dbshop_article_extend as e ON e.article_id=a.article_id
                            ".$where." ORDER BY a.article_sort ASC,a.article_id DESC ".$limit;
            }
        }

        $articleArray = array();
        if(!empty($selectSql)) {
            $query        = $this->dbshopSql->query($selectSql);
            $articleArray = $this->dbshopResultSet->initialize($query->execute())->toArray();
        }
        
        return $articleArray;
    }
    /*-----------------------------------商品设置----------------------------------------*/
    /**
     * 商品设置ini信息
     * @param $sign
     * @param string $type
     * @return string
     */
    public function getDbshopGoodsIni($sign, $type='shop_goods')
    {
        if(empty($this->goodsConfig)) {
            $this->goodsConfig = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/System/goods/goods.ini');
        }
        if(isset($this->goodsConfig[$type][$sign])) return $this->goodsConfig[$type][$sign];

        return '';
    }
    /**
     * 商品分享代码输出
     * @return array
     */
    public function getDbshopGoodsShare()
    {
        if(empty($this->goodsConfig)) {
            $this->goodsConfig = $this->iniReader->fromFile(DBSHOP_PATH . '/data/moduledata/System/goods/goods.ini');
        }
        $shareCode = (!empty($this->goodsConfig['shop_goods']['dbshop_goods_share']) ? @file_get_contents(DBSHOP_PATH . '/data/moduledata/System/goods/share' . $this->goodsConfig['shop_goods']['dbshop_goods_share']) : '');

        return array('share_type'=>$this->goodsConfig['shop_goods']['dbshop_goods_share'], 'share_code'=>$shareCode);
    }
    /*-----------------------------------商品设置----------------------------------------*/
    /**
     * 二维码生成
     * @param $text
     * @param $name
     * @param string $type
     * @return string
     */
    public function createQRcode($text, $name, $type='shop')
    {
        $qrCodePath = '/public';
        $fileName   = '';
        if($type == 'shop'){
            $qrCodePath  = $qrCodePath . '/img/shopqrcode/';
            $fileName    = md5($name) . '.png';
        }
        if($type == 'goods') {
            $qrCodePath = $qrCodePath . '/upload/goods/qrcode/';
            $fileName   = intval($name) . '.png';
        }
        if(!is_dir(DBSHOP_PATH . $qrCodePath)) @mkdir(DBSHOP_PATH . $qrCodePath, 0755, true);

        if(!file_exists(DBSHOP_PATH . $qrCodePath . $fileName)) {
            include DBSHOP_PATH . '/module/Upload/src/Upload/Plugin/Phpqrcode/phpqrcode.php';
            \QRcode::png($text, DBSHOP_PATH . $qrCodePath . $fileName, QR_ECLEVEL_L, 6, 1);
        }

        return $qrCodePath . $fileName;
    }
    /**
     * 获取经过处理后的密码
     * @param $password
     * @return string
     */
    public function getPasswordStr($password)
    {
        $keyCode = '?3b)f*ixoY!WQ4t{jyk#<{/HZXIw$>7Kr?+VN`?tN8qRJZ?6GW|oJW|{z+KBe2@?';
        return md5($keyCode . trim($password));
    }

    /*-----------------------------------判断是否为手机端----------------------------------------*/
    public function isMobile()
    {
        $useragent=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $useragent_commentsblock=preg_match('|\(.*?\)|',$useragent,$matches)>0?$matches[0]:'';

        $mobile_os_list=array('Google Wireless Transcoder','Windows CE','WindowsCE','Symbian','Android','armv6l','armv5','Mobile','CentOS','mowser','AvantGo','Opera Mobi','J2ME/MIDP','Smartphone','Go.Web','Palm','iPAQ');
        $mobile_token_list=array('Profile/MIDP','Configuration/CLDC-','160×160','176×220','240×240','240×320','320×240','UP.Browser','UP.Link','SymbianOS','PalmOS','PocketPC','SonyEricsson','Nokia','BlackBerry','Vodafone','BenQ','Novarra-Vision','Iris','NetFront','HTC_','Xda_','SAMSUNG-SGH','Wapaka','DoCoMo','iPhone','iPod');

        $found_mobile=$this->CheckSubstrs($mobile_os_list,$useragent_commentsblock) || $this->CheckSubstrs($mobile_token_list,$useragent);

        if ($found_mobile){
            return true;
        }else{
            return false;
        }

        /*if ($this->isMobile())
            echo '手机登录';
        else
            echo '电脑登录';*/
    }
    public function CheckSubstrs($substrs,$text){
        foreach($substrs as $substr)
            if(false!==strpos($text,$substr)){
                return true;
            }
        return false;
    }
}