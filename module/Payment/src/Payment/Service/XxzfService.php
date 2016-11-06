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

/**
 * 线下支付
 */
class XxzfService
{
    private $xmlReader;
    private $paymentConfig;
    private $paymentForm;
    public function __construct()
    {
        if(!$this->xmlReader) {
            $this->xmlReader = new \Zend\Config\Reader\Xml();
        }
        if(!$this->paymentConfig) {
            $this->paymentConfig = $this->xmlReader->fromFile(DBSHOP_PATH . '/data/moduledata/Payment/xxzf.xml');
        }
        if(!$this->paymentForm) {
            $this->paymentForm = new \Payment\Form\PaymentForm();
        }
    }
    public function savePaymentConfig(array $data)
    {
        $xmlWriter   = new \Zend\Config\Writer\Xml();
        $configArray = $this->paymentForm->setFormValue($this->paymentConfig, $data);
        $xmlWriter->toFile(DBSHOP_PATH . '/data/moduledata/Payment/xxzf.xml', $configArray);
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
    /*========================================================下面为支付处理=======================================================*/
    /** 
     * 线下支付处理，跳转到下一步
     * @param unknown $data
     */
    public function paymentTo($data)
    {
        //返回url
        $returnUrl = $data['return_url'];
        header("Location: ".$returnUrl);
    }
    /**
     * 支付抛出form
     * @param $orderInfo
     * @param array $language
     * @return array|multitype
     */
    public function paymentReturn ($orderInfo, array $language=array())
    {
        if(isset($_REQUEST['posttype']) and $_REQUEST['posttype'] == 'paypalto') {
            return $this->paymentFinish($orderInfo, $language);
        } else {
            return $this->createForm($language);
        }
    }
    /**
     * 最后的付款步骤
     * @param unknown $orderInfo
     * @param array $language
     */
    private function paymentFinish ($orderInfo,  array $language=array())
    {
        $tableHtml = '<table class="table table-bordered"><tbody><tr><td><h3>'.$language['pay_xxzf_finish'].'</h3></td></tr></tbody></table>';
        return array('payFinish'=>true, 'html'=>$tableHtml, 'state_info'=>trim($_REQUEST['state_info']));
    }
    /**
     *
     * @param $orderInfo
     * @param $express
     * @return bool
     */
    public function toSendOrder ($orderInfo, $express)
    {
        return true;
    }
    /** 
     * 创建form
     * @param array $language
     * @return multitype:string
     */
    private function createForm(array $language=array())
    {
        $tableHtml = '<form action="" method="POST" onsubmit="return check_info();">';
        $tableHtml .= '<input type="hidden" name="posttype" value="paypalto" />';
        $tableHtml .= '<table class="table table-bordered table-striped">';
        $tableHtml .= '<thead><tr><th colspan="2">' . $language['xxzf_title'] . '</th></tr></thead>';
        $tableHtml .= '<tbody>';
        $tableHtml .= '<tr><td width="20%">' . $language['xxzf_input'] . '</td><td><textarea id="state_info"  name="state_info" class="span12" rows="5"></textarea></td></tr>';
        $tableHtml .= '</tbody></table>';
        $tableHtml .= '<p align="center"><a class="btn" href="' .$language['return_order']. '"><i class="icon-arrow-left"></i> ' . $language['xxzf_return_url'] . '</a>&nbsp;&nbsp;&nbsp;&nbsp;<button class="btn btn-primary btn-large" type="submit">'.$language['xxzf_submit_pay'].'</button></p>';
        $tableHtml .= '</form>';
        $tableHtml .= '<script>function check_info() { var info=$("#state_info").val(); if(info == "") { alert("' . $language['xxzf_alert'] . '"); return false;} return true;  }</script>';
        
        return array('html'=>$tableHtml);
    }
}

?>