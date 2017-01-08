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

namespace Admin\Helper;

use Admin\Service\DbshopOpcache;
use Zend\View\Helper\AbstractHelper;
use Zend\Authentication\AuthenticationService;

class Helper extends AbstractHelper
{

    protected $route;
    //protected $paramsArray;
    private   $iniReader;
    
    public function __construct ($route='')
    {
        if($route != '') {
            $this->route = $route;
        }
        if(empty($this->iniReader)) {
            $this->iniReader = new \Zend\Config\Reader\Ini();
        }
    }
    /**
     * 返回命名空间
     * @return unknown
     */
    public function returnNamespace ()
    {
        /*if(!$this->paramsArray) {
            $this->paramsArray = $this->route->getParams();
        }
        return $this->paramsArray['__NAMESPACE__'];*/
        return $this->route->getParam('__NAMESPACE__');
    }
    /**
     * 返回action名称
     */
    public function returnActionname ()
    {
        /* if(!$this->paramsArray) {
            $this->paramsArray = $this->route->getParams();
        }
        return $this->paramsArray['action']; */
        return $this->route->getParam('action');
    }
    public function returnControllername ()
    {
        /* if(!$this->paramsArray) {
            $this->paramsArray = $this->route->getParams();
        }
        return $this->paramsArray['controller']; */
        return $this->route->getParam('controller');
    }
    /**
     * 返回验证后的管理员信息
     * @return Ambigous <\Zend\Authentication\mixed, NULL, \Zend\Authentication\Storage\mixed>
     */
    public function returnAuth ($name)
    {
        $auth = new AuthenticationService();
        $array= $auth->getIdentity();
        return $array[$name];
    }

    /**
     * 获取扩展插件的后台url（adminTools 工具管理、adminSystem 系统管理、adminCms CMS管理、adminUser 客户管理、adminGoods 商品管理、adminOrder 销售管理、adminAnalytics 统计分析）
     * @return array
     */
    public function adminExtendUrlArray()
    {
        $urlFile  = DBSHOP_PATH . '/data/moduledata/moduleini/url.ini';
        $urlArray = array();
        if(file_exists($urlFile)) {
            $urlArray = $this->iniReader->fromFile($urlFile);
        }
        return $urlArray;
    }
    /** 
     * 获取前台模板中的配置信息
     * @param unknown $name
     * @param string $type
     */
    public function defaultShopSet($name, $type='image_size')
    {
        $array = $this->iniReader->fromFile(DBSHOP_PATH . '/module/Shopfront/view/' . DBSHOP_TEMPLATE . '/shopfront/template.ini');
        return isset($array[$type][$name]) ? $array[$type][$name] : '';
    }
    /** 
     * 设置前台时区及模板配置文件
     * @param unknown $array 这里的数组，键为名称，键值为内容
     */
    public function setDbshopSetshopFile($array)
    {
        $setShopArray = array(
            'DBSHOP_TIMEZONE'       => (defined('DBSHOP_TIMEZONE') ? DBSHOP_TIMEZONE : 'Asia/Shanghai'),

            'DBSHOP_TEMPLATE'       => (defined('DBSHOP_TEMPLATE') ? DBSHOP_TEMPLATE : 'default'),
            'DBSHOP_TEMPLATE_CSS'   => (defined('DBSHOP_TEMPLATE_CSS') ? DBSHOP_TEMPLATE_CSS : 'default'),

            'DBSHOP_PHONE_TEMPLATE' => (defined('DBSHOP_PHONE_TEMPLATE') ? DBSHOP_PHONE_TEMPLATE : 'default'),
            'DBSHOP_PHONE_TEMPLATE_CSS' => (defined('DBSHOP_PHONE_TEMPLATE_CSS') ? DBSHOP_PHONE_TEMPLATE_CSS : 'default'),

            'DBSHOP_ADMIN_COMPRESS' => (defined('DBSHOP_ADMIN_COMPRESS') ? DBSHOP_ADMIN_COMPRESS : ''),
            'DBSHOP_FRONT_COMPRESS' => (defined('DBSHOP_FRONT_COMPRESS') ? DBSHOP_FRONT_COMPRESS : ''),

            'FRONT_CACHE_STATE'       => (defined('FRONT_CACHE_STATE') ? FRONT_CACHE_STATE : ''),
            'FRONT_CACHE_TIME'        => (defined('FRONT_CACHE_TIME') ? FRONT_CACHE_TIME : '0'),
            'FRONT_CACHE_TIME_TYPE'   => (defined('FRONT_CACHE_TIME_TYPE') ? FRONT_CACHE_TIME_TYPE : '0'),
            'FRONT_CACHE_EXPIRE_TIME' => (defined('FRONT_CACHE_EXPIRE_TIME') ? FRONT_CACHE_EXPIRE_TIME : '0'),

            'FRONT_CDN_STATE'         => (defined('FRONT_CDN_STATE') ? FRONT_CDN_STATE : 'false'),
            'FRONT_CDN_HTTP_TYPE'     => (defined('FRONT_CDN_HTTP_TYPE') ? FRONT_CDN_HTTP_TYPE : 'http://'),
            'FRONT_CDN_DOMAIN'        => (defined('FRONT_CDN_DOMAIN') ? FRONT_CDN_DOMAIN : ''),
        );
        
        if(is_array($array) and !empty($array)) {
            foreach ($array as $a_key => $a_val) {
                if(isset($setShopArray[$a_key]) and $setShopArray[$a_key] != $a_val) {
                    $setShopArray[$a_key] = $a_val;
                }
            }    
        }
        
        $setShopContent ="<?php\r\n";
        foreach ($setShopArray as $key => $val) {
            $setShopContent .= "define('" . $key . "', '" . $val . "');\r\n";
        }
        file_put_contents(DBSHOP_PATH . '/data/moduledata/Shopfront/setShop.php', $setShopContent);
    }
    /** 
     * 清空zf2框架的缓存配置/opcache清理
     */
    public function clearZfConfigCache()
    {
        //清理zf2框架缓存
        $zfConfigCachePathArray = array(
                DBSHOP_PATH . '/data/cache/modulecache/'
                );
        if(!is_dir(DBSHOP_PATH . '/data/cache/modulecache/')) @mkdir(DBSHOP_PATH . '/data/cache/modulecache/', 0777, true);
        foreach ($zfConfigCachePathArray as $valuePath) {
            $folder = @opendir($valuePath);
            while (false !== ($file = @readdir($folder))) {
                if($file != '.' and $file != '..' and is_file($valuePath.$file)) {
                    @unlink($valuePath.$file);
                }
            }
        }
        //重置opcache
        DbshopOpcache::reset();

        return true;
    }
    /**
     * DBShop无组件分词调用
     * @param $content
     * @return string
     */
    public function dbshopPhpAnalysis($content, $length=0, $type='keywords')
    {
        //判断如果内容为空，或者iconv函数不存在则不继续处理
        if(!function_exists('iconv') or empty($content)) return '';

        $dbshopContent = '';

        //载入分词核心文件
        require_once DBSHOP_PATH . '/module/Upload/src/Upload/Plugin/phpanalysis/phpanalysis.class.php';
        \PhpAnalysis::$loadInit = false;
        $phpAn = new \PhpAnalysis('utf-8', 'utf-8', false);
        $phpAn->LoadDict();
        $phpAn->SetSource(strip_tags($content));
        $phpAn->SetResultType(2);
        $phpAn->resultType  = 2;
        $phpAn->differMax   = true;
        $phpAn->unitWord    = true;

        $phpAn->StartAnalysis(true);
        if($type == 'keywords') {//获取关键字
            $dbshopContent = $phpAn->GetFinallyKeywords(80);
            $dbshopContent = str_replace('nbsp,', '', $dbshopContent);
        } else {//分词提取
            $dbshopContent = $phpAn->GetFinallyResult(' ');
        }

        return $this->dbshopCutStr($dbshopContent, $length, false);
    }
    /**
     * 字符截取方法
     * @param $str          要截取的字符串
     * @param int $length   需要截取的长度，0为不截取
     * @param bool $append  是否显示省略号，默认显示
     * @return string
     */
    public function dbshopCutStr($str, $length=0, $append=true)
    {
        $str = trim($str);
        $strLength = strlen($str);

        if ($length == 0 || $length >= $strLength) {
            return $str;
        } elseif ($length < 0) {
            $length = $strLength + $length;
            if ($length < 0) {
                $length = $strLength;
            }
        }
        if (function_exists('mb_substr')) {
            $newStr = mb_substr($str, 0, $length, 'utf-8');
        } elseif (function_exists('iconv_substr')) {
            $newStr = iconv_substr($str, 0, $length, 'utf-8');
        } else {
            $newStr = substr($str, 0, $length);
        }
        if ($append && $str != $newStr){
            $newStr .= '...';
        }
        return $newStr;
    }
    /**
     * 删除目录下的所有目录和文件
     * @param $dir
     * @return bool
     */
    public function delDirAndFile($dir) {
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!="..") {
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) {
                    @unlink($fullpath);
                } else {
                    $this->delDirAndFile($fullpath);
                }
            }
        }
        closedir($dh);
        if(@rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * 输出帮助网址
     * @param unknown $name
     * @return Ambigous <>
     */
    public function dbshopHelpUrl($name)
    {
        $urlHost  = 'http://help.dbshop.net/index.php?title=';
        $urlArray = array(
            'system_quick_set'              => '系统快速设置',       //系统快速设置帮助说明
            /*===============系统管理===============*/
            'system_set'                    => '系统设置',          //系统设置
            'optimization_set'              => '性能优化',          //性能优化
            'upload_set'                    => '附件设置',          //附件设置
            'user_set'                      => '客户设置',          //客户设置
            'currency_set'                  => '货币设置',          //货币设置
            'payment_set'                   => '支付设置',          //支付设置
            'express_set'                   => '配送设置',          //配送设置
            'express_api'                   => '配送动态API',       //配送动态API
            'region_set'                    => '地区设置',          //地区设置
            'phone_message_set'             => '手机短信设置',       //手机短信设置
            'send_message_set'              => '消息提醒设置',       //消息提醒设置
            'state_set'                     => '状态设置',          //状态设置
            'admin_set'                     => '管理员设置',        //管理员设置
            'admin_group_set'               => '管理员组设置',       //管理员组设置
            'online_style_set'              => '在线客服样式',       //在线客服样式
            'online_set'                    => '在线客服设置',       //在线客服设置
            'online_group_set'              => '在线客服组设置',     //在线客服组设置
            'admin_template'                => '模板管理',          //模板管理设置
            'online_package'                => '在线更新管理',       //在线更新管理
            'plugin_set'                    => '扩展插件',          //在线更新管理
            /*===============工具管理===============*/
            'navigation_set'                => '导航设置',         //导航设置
            'link_set'                      => '友情链接设置',      //友情链接设置
            'email_send'                    => '邮件发送',         //邮件发送
            'ad_set'                        => '广告设置',         //广告设置
            'db_back'                       => '数据库备份还原',    //数据库备份还原
            'operlog'                       => '操作日志',         //操作日志
            /*===============CMS管理===============*/
            'cms_set'                       => '管理文章',        //文章添加与编辑
            'cms_class_set'                 => '管理文章分类',     //文章分类添加与编辑
            'cms_single_set'                => '管理单页文章',    //单页文章管理
            /*===============客户管理===============*/
            'user_add'                      => '管理客户',        //客户添加与编辑
            'user_group_set'                => '管理客户组',      //客户组添加与编辑
            'user_integration_set'          => '管理整合',        //管理者整合
            'user_integral_set'             => '管理积分',        //管理积分
            'user_paycheck_log'             => '充值记录',        //充值记录
            'user_withdraw_log'             => '提现记录',        //提现记录
            'user_money_log'                => '余额记录',        //余额记录
            'user_integral_log'             => '积分历史记录',    //积分历史记录
            'user_integral_rule_set'        => '积分规则设置',    //积分规则设置
            'user_integral_type'            => '积分类型',       //积分类型
            /*===============商品管理===============*/
            'goods_set'                     => '管理商品',       //商品添加与编辑
            'goods_class_set'               => '管理商品分类',    //商品分类添加与编辑
            'goods_attribute_set'           => '管理属性',       //商品属性设置
            'goods_attribute_value_set'     => '管理属性值',     //商品属性值设置
            'goods_attribute_group_set'     => '管理属性组',     //商品属性组设置
            'goods_brand_set'               => '管理品牌',      //商品品牌设置
            'goods_comment_set'             => '管理商品评价',    //商品评价设置
            'goods_ask_set'                 => '管理商品咨询',    //商品咨询设置
            'goods_tag_set'                 => '管理商品标签',   //商品标签设置
            'goods_tag_group_set'           => '管理商品标签组',  //商品标签组设置
            'goods_promotions_set'          => '优惠促销规则',   //优惠促销规则
            'clear_goods_image'             => '无用商品图片清理',//无用商品图片清理
            'goods_index_set'               => '商品索引',      //商品索引
            'virtual_goods_set'             => '虚拟商品',      //虚拟商品
            /*===============销售管理===============*/
            'order_set'                     => '订单管理',      //订单设置
            'order_pay_set'                 => '订单付款',      //订单付款操作
            'order_ship_set'                => '订单发货',      //订单发货操作
            'order_finish_set'              => '订单完成',      //订单完成操作
            'order_ship_list'               => '发货单',        //发货单
            'order_refund'                  => '退货管理',      //发货单
            'order_pay_log'                 => '支付记录',      //支付记录
            'order_moreshipoper'            => '批量订单发货',   //批量订单发货
            'export_ship_set'               => '导出发货单',   //导出发货单
            'order_express_number'          => '快递单号管理',   //快递单号管理
        );
        return $urlHost . urlencode($urlArray[$name]);
    }
}

?>