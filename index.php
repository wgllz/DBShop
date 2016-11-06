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

@header("Content-type: text/html; charset=utf-8");
//设定根目录
define('DBSHOP_PATH', dirname(__FILE__));

/*==========================魔术符处理==========================*/
if(get_magic_quotes_gpc()) {    
    function stripslashes_deep($value)
    {
        $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
        return $value;
    }
    
    if(isset($_POST)    and !empty($_POST))    $_POST   = array_map('stripslashes_deep', $_POST);
    if(isset($_GET)     and !empty($_GET))     $_GET    = array_map('stripslashes_deep', $_GET);
    if(isset($_COOKIE)  and !empty($_COOKIE))  $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
    if(isset($_REQUEST) and !empty($_REQUEST)) $_REQUEST= array_map('stripslashes_deep', $_REQUEST);
}

/*==========================设定后台==========================*/
define('DBSHOP_ADMIN_DIR', 'admin');//后台默认目录

/*==========================设定前台==========================*/
require __DIR__ . '/data/moduledata/Shopfront/setShop.php';
//时区设定
if(function_exists('date_default_timezone_set') and defined('DBSHOP_TIMEZONE')){
    date_default_timezone_set(DBSHOP_TIMEZONE);
}

/*==========================设定版权信息==========================*/
define('DBSHOP_YEAR_COPYRIGHT',  '2012-'.date("Y").' ');
define('DBSHOP_FRONT_COPYRIGHT', 'DBShop.net');//前台版权信息
define('DBSHOP_FRONT_COPYRIGHT_URL', 'http://www.dbshop.net/');//前台版权信息url

/*==========================系统运行==========================*/
require 'init_autoloader.php';
$dbshopConfig = require 'config/application.config.php';

//合并不同的配置文件方法
if (file_exists('data/Module.ini.php')) {
    $dbshopConfig = Zend\Stdlib\ArrayUtils::merge($dbshopConfig, require 'data/Module.ini.php');
}

Zend\Mvc\Application::init($dbshopConfig)->run();
