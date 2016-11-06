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

use Zend\View\Helper\AbstractHelper;

class DbshopCommonFun extends AbstractHelper
{
    protected $request;
    
    public function setRequest($request)
    {
        $this->request = $request;
    }
    /**
     * 模板中获取传值
     */
    public function getRequest()
    {
        return $this->request;
    }
    /** 
     * 主要用于分页获取传值字符串
     * @param string $str
     */
    public function dbshopRequesthelper($str = 'QUERY_STRING')
    {
        return $this->getRequest()->getServer()->get($str);
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
}

?>