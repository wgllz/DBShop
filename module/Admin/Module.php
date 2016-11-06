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

namespace Admin;

use Zend\Mvc\ModuleRouteListener;
use Admin\Helper\Helper as MyViewHelper;

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
            'initializers' => array(
                function  ($instance, $sm)
                {
                    if ($instance instanceof \Zend\Db\Adapter\AdapterAwareInterface) {
                        $Adapter = $sm->get('Zend\Db\Adapter\Adapter');
                        //此为系统加入，获取db信息，主要用于数据表前缀
                        //$Adapter->dbConfig = $Adapter->driver->getConnection()->getConnectionParameters();
                        
                        $instance->setDbAdapter($Adapter);
                    }
                }
            ),
            'factories'=>array(
                'AdminTable'            => function () {return new Model\AdminTable(); },
                'AdminGroupTable'       => function () {return new Model\AdminGroupTable(); },
                'AdminGroupExtendTable' => function () {return new Model\AdminGroupExtendTable(); },
                
                'adminHelper'           => function () {return new \Admin\Helper\Helper(); },
                
                'dbshopTransaction'     => function () {return new Model\DbshopSqlTransaction(); },
            )
        );
    }
    /** 
     * 解析到模板中调用
     * @return multitype:multitype:NULL  |\Admin\Helper\Requesthelper
     */
    public function getViewHelperConfig()
    {
        return array(
                'factories' => array(
                        'DbshopCommonFun'=> function ($sm) {
                        $dbshopCommon = new \Admin\Helper\DbshopCommonFun();
                        $request      = $sm->getServiceLocator()->get('Request');
                        $dbshopCommon->setRequest($request);
                        return $dbshopCommon; 
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
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $app = $e->getApplication();
        $serviceManager = $app->getServiceManager();
        $serviceManager->get('viewhelpermanager')->setFactory('myviewalias', function  ($sm) use( $e)
        {
            return new MyViewHelper($e->getRouteMatch());
        });
    }
}
