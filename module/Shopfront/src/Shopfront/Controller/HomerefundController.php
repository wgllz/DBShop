<?php
/**
 * DBShop 电子商务系统
 *
 * ==========================================================================
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2016 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 * ==========================================================================
 *
 * @author    静静的风
 *
 */

namespace Shopfront\Controller;

use Zend\View\Model\ViewModel;

class HomerefundController extends FronthomeController
{
    private $dbTables = array();
    private $translator;

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('/shopfront/home/homerefund.phtml');

        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('退货申请');

        $array = array();

        $searchArray  = array();
        if($this->request->isGet()) {
            $searchArray['search_content'] = $this->request->getQuery('search_content');
        }
        $searchArray['user_id'] = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');

        $page = $this->params('page',1);
        $array['user_order_refund'] = $this->getDbshopTable('OrderRefundTable')->listOrderRefund(array('page'=>$page, 'page_num'=>20), $searchArray, 'front');

        $view->setVariables($array);
        return $view;
    }
    /**
     * 退货详情
     * @return ViewModel
     */
    public function refundShowAction()
    {
        $view = new ViewModel();
        $view->setTemplate('/shopfront/home/refundshow.phtml');
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('退货详情');

        $array = array();

        $refundId = (int) $this->params('refund_id');
        $userId   = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');

        $array['refund_info'] = $this->getDbshopTable('OrderRefundTable')->infoOrderRefund(array('refund_id'=>$refundId, 'user_id'=>$userId));
        $array['order_info']  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_sn'=>$array['refund_info']->order_sn, 'buyer_id'=>$userId));
        //订单商品
        $array['order_goods'] = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=> $array['order_info']->order_id));

        $view->setVariables($array);
        return $view;
    }
    /**
     * 退款申请页面
     */
    public function addRefundAction()
    {
        $view = new ViewModel();
        $view->setTemplate('/shopfront/home/addrefund.phtml');

        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('申请退货');

        $array = array();


        $view->setVariables($array);
        return $view;
    }
    /**
     * 保存申请退款页面
     */
    public function saveRefundAction()
    {
        if($this->request->isPost()) {
            $refundArray = $this->request->getPost()->toArray();
            $orderRefund = array();
            $orderRefund['order_sn']     = $refundArray['order_sn'];
            $orderRefund['goods_id_str'] = implode(',', $refundArray['goods_id']);
            $orderRefund['refund_type']  = $refundArray['refund_type'];
            $orderRefund['refund_info']  = $refundArray['refund_info'];
            $orderRefund['user_id']      = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
            $orderRefund['user_name']    = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name');
            $orderRefund['refund_info']  = $refundArray['refund_info'];
            if($orderRefund['refund_type'] == 2) {
                $orderRefund['bank_name']        = $refundArray['bank_name'];
                $orderRefund['bank_account']     = $refundArray['bank_account'];
                $orderRefund['bank_card_number'] = $refundArray['bank_card_number'];
            }
            if($orderRefund['refund_type'] == 3) {
                $orderRefund['bank_name']        = $refundArray['pay_name'];;
                $orderRefund['bank_account']     = $refundArray['pay_user_name'];;
                $orderRefund['bank_card_number'] = $refundArray['pay_account'];;
            }
            $state = $this->getDbshopTable('OrderRefundTable')->addOrderRefund($orderRefund);
            if($state) exit('true');
        }
        exit($this->getDbshopLang()->translate('申请退货没有成功！'));
    }
    /**
     * 删除退货信息
     */
    public function delRefundAction()
    {
        if($this->request->isPost()) {
            $refundId   = $this->request->getPost('refund_id');
            $refundInfo = $this->getDbshopTable('OrderRefundTable')->infoOrderRefund(array('refund_id'=>$refundId, 'user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
            if(!empty($refundInfo) and $refundInfo->refund_state == 0) {
                $state = $this->getDbshopTable('OrderRefundTable')->delOrderRefund(array('refund_id'=>$refundId));
                if($state) exit('true');
            }
        }
        exit($this->getDbshopLang()->translate('删除退货记录失败！'));
    }
    /**
     * 查询订单和订单商品
     */
    public function searchOrderGoodsAction()
    {
        $array = array('state'=>'false', 'message'=>$this->getDbshopLang()->translate('此订单编号不符合查询条件'));
        if($this->request->isPost()) {
            $orderSn    = $this->request->getPost('order_sn');
            $orderInfo  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_sn'=>$orderSn, 'order_state IN (40,60)', 'buyer_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
            if(empty($orderInfo)) exit(json_encode($array));

            $refundInfo = $this->getDbshopTable('OrderRefundTable')->infoOrderRefund(array('order_sn'=>$orderSn, 'refund_state!=2'));
            if(!empty($refundInfo)) exit(json_encode(array('state'=>'false', 'message'=>$this->getDbshopLang()->translate('此订单编号已经存在于退货记录中，不能重复申请。'))));

            $orderGoods = $this->getDbshopTable('OrderGoodsTable')->listOrderGoods(array('order_id'=>$orderInfo->order_id));
            if(!empty($orderGoods)) exit(json_encode(array('state'=>'true', 'goods'=>$orderGoods)));
        }
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
    /**
     * 语言包调用
     * @return Ambigous <object, multitype:, \Zend\I18n\Translator\Translator>
     */
    private function getDbshopLang ()
    {
        if (! $this->translator) {
            $this->translator = $this->getServiceLocator()->get('translator');
        }
        return $this->translator;
    }
}