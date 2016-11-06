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

namespace User\Service;

class UcenterService
{
    private $xmlReader;
    private $integrationConfig;
    private $integrationForm;
    public function __construct()
    {
        if(!$this->xmlReader) {
            $this->xmlReader = new \Zend\Config\Reader\Xml();
        }
        if(!$this->integrationConfig) {
            $this->integrationConfig = $this->xmlReader->fromFile(DBSHOP_PATH . '/data/moduledata/User/Integration/ucenter.xml');
        }
        if(!$this->integrationForm) {
            $this->integrationForm = new \User\Form\IntegrationForm();
        }
    }
    /**
     * 保存配置信息
     * @param array $data
     * @return unknown
     */
    public function saveIntegrationConfig(array $data)
    {
        $xmlWriter   = new \Zend\Config\Writer\Xml();
        $configArray = $this->integrationForm->setFormValue($this->integrationConfig, $data);
        $xmlWriter->toFile(DBSHOP_PATH . '/data/moduledata/User/Integration/ucenter.xml', $configArray);
        return $configArray;
    }
    /**
     * 获取表单数组
     * @return multitype:multitype:string array  Ambigous <\Payment\Service\multitype:string, multitype:string array >
     */
    public function getFromInput()
    {
        $inputArray = $this->integrationForm->createFormInput($this->integrationConfig);
        return $inputArray;
    }
}
