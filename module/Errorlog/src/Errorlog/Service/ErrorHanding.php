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

namespace Errorlog\Service;

class ErrorHanding
{
    protected $logger;
    
    public function __construct($logger)
    {
        $this->logger = $logger;
    }
    public function logException(\Exception $e)
    {
        $trace = $e->getTraceAsString();
        $i = 1;
        do {
            $messages[] = $i++ . ": " . $e->getMessage();
        } while ($e = $e->getPrevious());
 
        $log = "Exception:\n" . implode("\n", $messages);
        $log .= "\nTrace:\n" . $trace;
 
        $this->logger->err($log);
    }
    /** 
     * 共同完善计划
     */
    private function logInsertDbshopService()
    {
        if(class_exists('SoapClient')) {
            try {
                $logSoapClient = new \SoapClient(null, array(
                        'location' => 'http://update.dbshop.net/errorlogservice',
                        'uri'      => 'dbshop_errorlog_soap'
                ));
                
            } catch (\Exception $e) {
                
            }
        }
    }
}
