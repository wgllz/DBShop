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

namespace Payment\Controller;

use Zend\View\Model\ViewModel;
use Admin\Controller\BaseController;

class PaymentController extends BaseController
{
    /** 
     * 支付方式列表
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $paymentArray = array();
        $filePath      = DBSHOP_PATH . '/data/moduledata/Payment/';
        if(is_dir($filePath)) {
            $dh = opendir($filePath);
            while (false !== ($fileName = readdir($dh))) {
                if($fileName != '.' and $fileName != '..' and stripos($fileName, '.php') !== false and $fileName != '.DS_Store') {
                    $paymentArray[] = include($filePath . $fileName);
                }
            }
        }
        //排序操作
        usort($paymentArray, function ($a, $b) {
            if($a['payment_sort']['content'] == $b['payment_sort']['content']) {
                return 0;
            }
            return ($a['payment_sort']['content'] < $b['payment_sort']['content']) ? -1 : 1;
        });
        
        return array('payment'=>$paymentArray);
    }
    /**
     * 支付编辑
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|\Zend\View\Model\ViewModel
     */
    public function  paymentAction ()
    {
        //识别系统已经有的支付方式
        $payType = $this->params('paytype');
        if(!in_array($payType, array('alipay', 'paypal', 'hdfk', 'xxzf', 'malipay', 'wxpay', 'wxmpay', 'yezf'))) $payType = 'alipay';

        //进行配置信息设置
        if($this->request->isPost()) {
            $paymentArray = $this->request->getPost()->toArray();
            $this->getServiceLocator()->get($payType)->savePaymentConfig($paymentArray);
            //操作日志记录
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('支付设置'), 'operlog_info'=>$this->getDbshopLang()->translate('更新支付') . '&nbsp;' . $paymentArray['payment_name']));
            
            return $this->redirect()->toRoute('payment/default',array('action'=>'payment', 'paytype'=>$payType), array('query'=>array('save'=>'ok')));
        }
        //设置view信息
        $view = new ViewModel();
        $view->setTemplate('/payment/payment/edit.phtml');

        //设置保存成功后，显示在页面上的提示信息
        $saveState = trim($this->request->getQuery('save'));
        if(!empty($saveState) and $saveState == 'ok') {
            $successMsg = $this->getDbshopLang()->translate('支付方式设置保存成功！');
            $view->setVariable('success_msg', $successMsg);
        }

        $paymentInput = $this->getServiceLocator()->get($payType)->getFromInput();
        //抛出订单状态
        if(isset($paymentInput['orders_state']) and !empty($paymentInput['orders_state'])) {
            $view->setVariable('select_orders_state', $paymentInput['orders_state']);
            unset($paymentInput['orders_state']);
        }
        $view->setVariable('form_input', $paymentInput);
        
        
        return $view;        
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName)
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}
