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

//在发布时是开启状态，在开发环境中注释掉
error_reporting(E_ERROR | E_PARSE);


$loader = include 'vendor/autoload.php';

$zf2Path = __DIR__ . '/vendor/zendframework/zendframework/library';
$loader->add('Zend', $zf2Path);

$qiniuPath = __DIR__ . '/vendor/qiniu/php-sdk/src/Qiniu/functions.php';
if(file_exists($qiniuPath)) require $qiniuPath;

$aliyunPath= __DIR__ . '/vendor/alibaba/aliyun/autoload.php';
if(file_exists($aliyunPath)) require $aliyunPath;



