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

namespace Admin\Service;

class DbshopOpcache
{
    /**
     * 获取opcache状态信息
     * @param $sign
     * @return string
     */
    public static function cacheStatus($sign)
    {
        if(function_exists('opcache_get_status')) {
            $status = opcache_get_status();
            if(is_array($status) and !empty($status)) {
                if(isset($status[$sign])) return $status[$sign];
            }
        }
        return '';
    }
    /**
     * 重置或清除整个字节码缓存数据
     * @return bool
     */
    public static function reset()
    {
        if(function_exists('opcache_reset')) {
            $status = self::cacheStatus('opcache_enabled');
            if(isset($status) and $status) return opcache_reset();
        }
    }
    /**
     * 指定某脚本文件字节码缓存失效
     * @param $script
     * @return bool
     */
    public static function invalidate($script)
    {
        if(function_exists('opcache_invalidate')) return (self::isCached($script) ? opcache_invalidate($script, true) : true);
    }
    /**
     * 无需运行，就可以编译并缓存脚本
     * @param $file
     * @return bool
     */
    public static function compile($file)
    {
        if(function_exists('opcache_compile_file')) return opcache_compile_file($file);
    }
    /**
     * 判断某个脚本是否已经缓存到Opcache
     * @param $script
     * @return bool
     */
    public static function isCached($script)
    {
        if(function_exists('opcache_is_script_cached')) return opcache_is_script_cached($script);
        return true;
    }
}