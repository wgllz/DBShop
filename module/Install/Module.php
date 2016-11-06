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

namespace Install;

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
        $app->getEventManager()->attach('dispatch', array($this, 'setInstallLayout'));
    }
    public function setInstallLayout($e)
    {
        $controller = $e->getRouteMatch()->getParam('controller');
        if (false === strpos($controller, __NAMESPACE__)) {
            return;
        }
        // Set the install layout template
        $viewModel = $e->getViewModel();
        $viewModel->setTemplate('install/layout');
    }
}
