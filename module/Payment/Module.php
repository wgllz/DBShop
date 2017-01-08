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

namespace Payment;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
		    // if we're in a namespace deeper than one level we need to fix the \ in the path
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/' , __NAMESPACE__),
                ),
            ),
        );
    }
    public function getServiceConfig ()
    {
        return array(
                'factories' => array(
                        'alipay' => function  () { return new \Payment\Service\AlipayService(); },
                        'malipay'=> function  () { return new \Payment\Service\MalipayService();},
                        'hdfk'   => function  () { return new \Payment\Service\HdfkService();   },
                        'paypal' => function  () { return new \Payment\Service\PaypalService(); },
                        'xxzf'   => function  () { return new \Payment\Service\XxzfService();   },
                        'wxpay'  => function  () { return new \Payment\Service\WxpayService();  },
                        'yezf'   => function  () { return new \Payment\Service\YezfService();   },
                        'wxmpay' => function  () { return new \Payment\Service\WxmpayService(); },
                )
        );
    }
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap($e)
    {
    }
}
