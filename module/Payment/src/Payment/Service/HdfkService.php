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

namespace Payment\Service;
use Zend\Config\Writer\PhpArray;

/**
 * 货到付款
 */
class HdfkService
{
    private $paymentConfig;
    private $paymentForm;
    public function __construct()
    {
        if(!$this->paymentConfig) {
            $this->paymentConfig = include(DBSHOP_PATH . '/data/moduledata/Payment/hdfk.php');
        }
        if(!$this->paymentForm) {
            $this->paymentForm = new \Payment\Form\PaymentForm();
        }
    }
    public function savePaymentConfig(array $data)
    {
        $phpWriter  = new PhpArray();
        $configArray = $this->paymentForm->setFormValue($this->paymentConfig, $data);
        $phpWriter->toFile(DBSHOP_PATH . '/data/moduledata/Payment/hdfk.php', $configArray);
        return $configArray;
    }
    /**
     * 获取表单数组
     * @return multitype:multitype:string array  Ambigous <\Payment\Service\multitype:string, multitype:string array >
     */
    public function getFromInput()
    {
        $inputArray = $this->paymentForm->createFormInput($this->paymentConfig);
        return $inputArray;
    }
    /**
     * 线下支付处理，跳转到下一步
     * @param unknown $data
     */
    public function paymentTo($data)
    {
        //返回url
        $returnUrl = $data['return_url'];
        header("Location: ".$returnUrl);
        exit();
    }
    /**
     * 最后的付款步骤
     * @param unknown $orderInfo
     * @param array $language
     */
    public function paymentReturn ($orderInfo,  array $language=array())
    {
        header("Location: ".$language['return_order']);
        exit();
    }
    /**
     * 发货处理
     * @param unknown $orderInfo
     */
    public function toSendOrder ($orderInfo, $express)
    {
        return true;
    }
}

?>