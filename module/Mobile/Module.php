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

namespace Mobile;

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

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap($e)
    {
        $app = $e->getParam('application');
        $app->getEventManager()->attach('dispatch', array($this, 'setMobileLayout'));
    }
    public function setMobileLayout($e)
    {
        //设置系统信息
        $app = $e->getApplication();
        $serviceManager = $app->getServiceManager();


        $matches    = $e->getRouteMatch();
        $controller = $matches->getParam('controller');
        if (false === strpos($controller, __NAMESPACE__)) {
            // not a controller from this module
            return;
        }

        //检查是否关闭
        if($serviceManager->get('frontHelper')->websiteInfo('shop_close') == 'close') {
            @header("Content-type: text/html; charset=utf-8");
            exit($serviceManager->get('frontHelper')->websiteInfo('shop_close_info'));
        }
        // Set the layout template
        $viewModel = $e->getViewModel();
        if($e->getRequest()->isXmlHttpRequest()) {//这里暂不确定在mobile中是否有用处
            $viewModel->setTerminal(true);
        } else {
            $viewModel->setTemplate('mobile/layout');
        }
    }
}