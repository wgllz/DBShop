<?php
//header('Access-Control-Allow-Origin: http://www.baidu.com'); //设置http://www.baidu.com允许跨域访问
//header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header
//时区设定
if(function_exists('date_default_timezone_set')){
    if(defined('DBSHOP_TIMEZONE')) date_default_timezone_set(DBSHOP_TIMEZONE);
    else date_default_timezone_set("Asia/Shanghai");
}

error_reporting(E_ERROR);
header("Content-Type: text/html; charset=utf-8");

include __DIR__ . '/../../../../init_autoloader.php';

$CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents("config.json")), true);
$action = $_GET['action'];

$dbshop_php_self = $_SERVER['PHP_SELF'] ? dirname($_SERVER['PHP_SELF']) : dirname($_SERVER['SCRIPT_NAME']);
$substr_dbshop_str = strpos(PHP_OS, "WIN") !== false ? str_replace('/', '\\', $dbshop_php_self) : $dbshop_php_self;

switch ($action) {
    case 'config':
        $result =  json_encode($CONFIG);
        break;

    /* 上传图片 */
    case 'uploadimage':
    /* 上传涂鸦 */
    case 'uploadscrawl':
    /* 上传视频 */
    case 'uploadvideo':
    /* 上传文件 */
    case 'uploadfile':
        $result = include("action_upload.php");
        break;

    /* 列出图片 */
    case 'listimage':
        $result = include("action_list.php");
        break;
    /* 列出图片 */
    case 'listgoodsimage':
        $result = include("action_goods_list.php");
        break;		
    /* 列出文件 */
    case 'listfile':
        $result = include("action_list.php");
        break;

    /* 抓取远程文件 */
    case 'catchimage':
        $result = include("action_crawler.php");
        break;

    default:
        $result = json_encode(array(
            'state'=> '请求地址出错'
        ));
        break;
}

/* 输出结果 */
if (isset($_GET["callback"])) {
    if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
        echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
    } else {
        echo json_encode(array(
            'state'=> 'callback参数不合法'
        ));
    }
} else {
    echo $result;
}