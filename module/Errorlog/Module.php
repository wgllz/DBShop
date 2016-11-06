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

namespace Errorlog;

use Errorlog\Service\ErrorHanding as ErrorHandlingService;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream as LogWriterStream; 

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
    /** 
     * 错误日志记录
     * @param unknown $e
     */
    public function onBootstrap($e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach('dispatch.error', function($event){
            $exception = $event->getResult()->exception;
            if ($exception) {
                $sm = $event->getApplication()->getServiceManager();
                $service = $sm->get('Errorlog\Service\ErrorHanding');
                $service->logException($exception);
            }
        });
    }
    public function getServiceConfig()
    {
        return array(
                'factories' => array(
                        'Errorlog\Service\ErrorHanding' =>  function($sm) {
                            $logger = $sm->get('Zend\Log');
                            $service = new ErrorHandlingService($logger);
                            return $service;
                        },
                        'Zend\Log' => function ($sm) {
                            $filename =  date('Y-m-d') . '_error' . '.log';
                            $log = new Logger();
                            $writer = new LogWriterStream(DBSHOP_PATH . '/data/moduledata/Errorlog/' . $filename);
                            $log->addWriter($writer);
    
                            return $log;
                        },
                ),
        );
    }
}
