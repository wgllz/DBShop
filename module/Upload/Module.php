<?php
/**
 * DBShop 电子商务系统
 *
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2015 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 */

namespace Upload;

class Module
{

    public function getAutoloaderConfig ()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php'
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    // if we're in a namespace deeper than one level we need to fix the \ in the path
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/', __NAMESPACE__)
                )
            )
        );
    }

    public function getServiceConfig ()
    {
        return array(
            'factories' => array(
                'shop_goods_upload' => function  ()
                {
                    return new \Upload\Common\Service\GoodsUploadService();
                },
                'shop_system_upload' => function  ()
                {
                    return new \Upload\Common\Service\SystemUploadService();
                },
                'shop_other_upload' => function ()
                {
                    return new \Upload\Common\Service\OtherUploadService();
                },
                'shop_qrcode' => function ()//暂时未用
                {
                    return new \QRcode();
                }
            )
        );
    }

    public function getConfig ()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap ($e)
    {

    }
}
