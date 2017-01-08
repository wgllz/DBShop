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
namespace Analytics\Controller;

use Admin\Controller\BaseController;

class AnalyticsController extends BaseController
{
    private $loginUlr   = 'https://api.baidu.com/sem/common/HolmesLoginService';
    private $apiUlr     = 'https://api.baidu.com/json/tongji/v1/ReportService';
    private $uUid       = '666666';
    private $accountType= 1;

    private $userName   = '';
    private $userPasswd = '';
    private $token      = '';
    private $siteId     = '';
    private $headers    = array();
    private $publicKey;

    /**
     * 客户统计
     * @return array
     */
    public function userStatsAction()
    {
        $array = array();

        $array['page_type'] = 'userStats';//页面类型，用于标识页面左侧的菜单
        //当天客户数
        $dayTime = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $array['day_user_total'] = $this->getDbshopTable('UserTable')->countUser(array('user_time>'.$dayTime));
        //当月客户数
        $monthTime = mktime(0,0,0,date('m'),1,date('Y'));
        $array['month_user_total'] = $this->getDbshopTable('UserTable')->countUser(array('user_time>'.$monthTime));
        //客户总数
        $array['user_total'] = $this->getDbshopTable('UserTable')->countUser();
        //购买过订单的客户数
        $array['user_buyer_total'] = $this->getDbshopTable('OrderTable')->buyerCountOrder();

        //统计图表输出
        $dateNum = (int) $this->request->getQuery('dateNum');
        if($dateNum <= 0) $dateNum = 7 - 1;
        else $dateNum = $dateNum - 1;//因为是从0开始算，所以减去1
        $dateTime = strtotime("-".$dateNum." day");
        $array['date_num'] = $dateNum;
        $userWhere = 'user_time>'.$dateTime;
        $orderWhere= 'order_time>'.$dateTime;

        //当POST传过时间值时
        if($this->request->isPost()) {
            $postArray = $this->request->getPost()->toArray();
            if(!empty($postArray['start_time']) and !empty($postArray['end_time'])) {
                $startTime  = strtotime($postArray['start_time']);
                $endTime    = strtotime($postArray['end_time'].' 24:00:00');
                $dateNum    = round(($endTime - $startTime)/(60*60*24))-1;
                $array['date_num']  = '';
                $array['start_time']= $postArray['start_time'];
                $array['end_time']  = $postArray['end_time'];
                $userWhere = 'user_time>='.$startTime.' and user_time<='.$endTime;
                $orderWhere= 'order_time>='.$startTime.' and order_time<='.$endTime;
            }

        }

        $userS = $this->getDbshopTable('UserTable')->StatsUser($userWhere, '', 'utime');
        $userArray = array();
        if(is_array($userS) and !empty($userS)) {
            foreach($userS as $value) {
                $userArray[$value['utime']] = $value['user_count'];
            }
        }
        $orderS = $this->getDbshopTable('OrderTable')->StatsOrder($orderWhere, '', 'otime');
        $orderArray = array();
        if(is_array($orderS) and !empty($orderS)) {
            foreach($orderS as $oValue) {
                $orderArray[$oValue['otime']] = $oValue['order_count'];
            }
        }

        $dateArray = array();
        $uArray    = array();
        $oArray    = array();
        for($i=$dateNum; $i>=0; $i--) {
            $dateStr = date("Y-m-d", strtotime("-".$i." day"));
            $dateArray[] = '\''.$dateStr.'\'';
            $uArray[$dateStr] = isset($userArray[$dateStr]) ? $userArray[$dateStr] : 0;
            $oArray[$dateStr] = isset($orderArray[$dateStr]) ? $orderArray[$dateStr] : 0;
        }
        $array['week_user']= implode(',', $uArray);
        $array['week_order']= implode(',', $oArray);
        $array['date_str'] = implode(',', $dateArray);

        return $array;
    }
    /**
     * 订单统计
     * @return array
     */
    public function orderStatsAction()
    {
        $array = array();

        $array['page_type'] = 'orderStats';//页面类型，用于标识页面左侧的菜单

        //当天订单数
        $dayTime = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $array['day_order_num'] = $this->getDbshopTable('OrderTable')->stateCountOrder(array('order_time>'.$dayTime));
        //当月订单数
        $monthTime = mktime(0,0,0,date('m'),1,date('Y'));
        $array['month_order_num'] = $this->getDbshopTable('OrderTable')->stateCountOrder(array('order_time>'.$monthTime));
        //订单总数
        $array['order_num'] = $this->getDbshopTable('OrderTable')->stateCountOrder(array());
        //已付款订单数
        $array['pay_order_num'] = $this->getDbshopTable('OrderTable')->stateCountOrder(array("(pay_code<>'xxfk' and pay_code<>'hdfk' and order_state>=20) or order_state=60"));

        //统计图表输出
        $dateNum = (int) $this->request->getQuery('dateNum');
        if($dateNum <= 0) $dateNum = 7 - 1;
        else $dateNum = $dateNum - 1;//因为是从0开始算，所以减去1
        $dateTime = strtotime("-".$dateNum." day");
        $array['date_num'] = $dateNum;
        $orderWhere= 'order_time>'.$dateTime;

        //当POST传过时间值时
        if($this->request->isPost()) {
            $postArray = $this->request->getPost()->toArray();
            if(!empty($postArray['start_time']) and !empty($postArray['end_time'])) {
                $startTime  = strtotime($postArray['start_time']);
                $endTime    = strtotime($postArray['end_time'].' 24:00:00');
                $dateNum    = round(($endTime - $startTime)/(60*60*24))-1;
                $array['date_num']  = '';
                $array['start_time']= $postArray['start_time'];
                $array['end_time']  = $postArray['end_time'];
                $orderWhere= 'order_time>='.$startTime.' and order_time<='.$endTime;
            }
        }

        $payOrderS = $this->getDbshopTable('OrderTable')->StatsOrder($orderWhere." and ((pay_code<>'xxfk' and pay_code<>'hdfk' and order_state>=20) or order_state=60)", '', 'otime');
        $payOrderArray = array();
        if(is_array($payOrderS) and !empty($payOrderS)) {
            foreach($payOrderS as $value) {
                $payOrderArray[$value['otime']] = $value['order_count'];
            }
        }
        $orderS = $this->getDbshopTable('OrderTable')->StatsOrder($orderWhere, '', 'otime');
        $orderArray = array();
        if(is_array($orderS) and !empty($orderS)) {
            foreach($orderS as $oValue) {
                $orderArray[$oValue['otime']] = $oValue['order_count'];
            }
        }

        $dateArray = array();
        $pArray    = array();
        $oArray    = array();
        for($i=$dateNum; $i>=0; $i--) {
            $dateStr = date("Y-m-d", strtotime("-".$i." day"));
            $dateArray[] = '\''.$dateStr.'\'';
            $pArray[$dateStr] = isset($payOrderArray[$dateStr]) ? $payOrderArray[$dateStr] : 0;
            $oArray[$dateStr] = isset($orderArray[$dateStr])    ? $orderArray[$dateStr]     : 0;
        }
        $array['week_pay_order']= implode(',', $pArray);
        $array['week_order']    = implode(',', $oArray);
        $array['date_str']      = implode(',', $dateArray);

        //支付方式
        $xmlReader    = new \Zend\Config\Reader\Xml();
        $paymentArray = array();
        $xmlPath      = DBSHOP_PATH . '/data/moduledata/Payment/';
        if(is_dir($xmlPath)) {
            $dh = opendir($xmlPath);
            while (false !== ($fileName = readdir($dh))) {
                if($fileName != '.' and $fileName != '..' and $fileName != '.DS_Store') {
                    $paymentInfo = $xmlReader->fromFile($xmlPath . $fileName);
                    $paymentArray[$paymentInfo['editaction']] = '\''.$paymentInfo['payment_name']['content'].'\'';
                }
            }
        }
        $array['payment_str'] = implode(',', $paymentArray);
        $lPayment = $this->getDbshopTable('OrderTable')->statsOrderPaymentOrExpress('order_state>0 and '.$orderWhere, '', 'pay_code');
        if(is_array($lPayment) and !empty($lPayment)) {
            foreach($lPayment as $lValue) {
                $paymentArray[$lValue['pay_code']] = array('name'=>$paymentArray[$lValue['pay_code']], 'num'=>$lValue['order_count']);
            }
        }
        $array['payment_array'] = $paymentArray;

        //配送方式
        $lExpressArray= array();
        $expressArray = $this->getDbshopTable('ExpressTable')->listExpress(array());
        if(is_array($expressArray) and !empty($expressArray)) {
            foreach($expressArray as $eValue) {
                $lExpressArray[$eValue['express_id']] = '\''.$eValue['express_name'].'\'';
            }
        }
        $array['express_str'] = !empty($lExpressArray) ? implode(',', $lExpressArray) : '';

        $lExpress = $this->getDbshopTable('OrderTable')->statsOrderPaymentOrExpress('(express_name is not null) and order_state>0 and '.$orderWhere, '', 'express_id', 'express_name AS express_name, express_id AS express_id');
        if(is_array($lExpress) and !empty($lExpress)) {
            foreach($lExpress as $lEValue) {
                $lExpressArray[$lEValue['express_id']] = array('name'=>$lExpressArray[$lEValue['express_id']], 'num'=>$lEValue['order_count']);
            }
        }
        $array['express_array'] = $lExpressArray;

        //订单地区
        $regionArray = $this->getDbshopTable('OrderDeliveryAddressTable')->statsDelivery('order_state>0 and '.$orderWhere);
        $array['region_array'] = $regionArray;

        return $array;
    }
    /**
     * 销售概况
     * @return array
     */
    public function saleStatsAction()
    {
        $array = array();

        $array['page_type'] = 'saleStats';//页面类型，用于标识页面左侧的菜单

        $array['order_total']       = $this->getDbshopTable('OrderTable')->StatsOrder("order_state>0 and refund_state<>1", '', '', 'SUM(order_amount) AS order_total');
        $array['order_pay_total']   = $this->getDbshopTable('OrderTable')->StatsOrder("((pay_code<>'xxfk' and pay_code<>'hdfk' and order_state>=20) or order_state=60) and refund_state<>1", '', '', 'SUM(order_amount) AS order_total');
        $array['order_d_pay_total'] = $this->getDbshopTable('OrderTable')->StatsOrder("((order_state>=0 and order_state<20) or (order_state>=20 and (pay_code='xxfk' or pay_code='hdfk'))) and refund_state<>1", '', '', 'SUM(order_amount) AS order_total');

        $array['get_array'] = array();//用户输出query在订单走势与销售走势间切换
        $saleType = trim($this->request->getQuery('sale_type'));
        if(isset($saleType) and $saleType == 'total') $saleType = 'total';
        else $saleType = 'num';

        //统计图表输出
        $dateNum = (int) $this->request->getQuery('dateNum');
        if($dateNum <= 0) $dateNum = 7 - 1;
        else $dateNum = $dateNum - 1;//因为是从0开始算，所以减去1
        $dateTime = strtotime("-".$dateNum." day");
        $array['date_num'] = $dateNum;
        $orderWhere= 'order_time>'.$dateTime;
        $array['get_array']['dateNum'] = $dateNum+1;

        //当GET传过时间值时
        $getArray['start_time'] = $this->request->getQuery('start_time');
        $getArray['end_time']   = $this->request->getQuery('end_time');
        if(!empty($getArray['start_time']) and !empty($getArray['end_time'])) {
            $startTime  = strtotime($getArray['start_time']);
            $endTime    = strtotime($getArray['end_time'].' 24:00:00');
            $dateNum    = round(($endTime - $startTime)/(60*60*24))-1;
            $array['date_num']  = '';
            $array['start_time']= $getArray['start_time'];
            $array['end_time']  = $getArray['end_time'];
            $orderWhere= 'order_time>='.$startTime.' and order_time<='.$endTime;

            $array['get_array']['start_time']   = $getArray['start_time'];
            $array['get_array']['end_time']     = $getArray['end_time'] ;
        }

        if($saleType == 'num') {//订单走势
            $payOrderS = $this->getDbshopTable('OrderTable')->StatsOrder($orderWhere." and refund_state<>1 and ((pay_code<>'xxfk' and pay_code<>'hdfk' and order_state>=20) or order_state=60)", '', 'otime');
            $payOrderArray = array();
            if(is_array($payOrderS) and !empty($payOrderS)) {
                foreach($payOrderS as $value) {
                    $payOrderArray[$value['otime']] = $value['order_count'];
                }
            }
            $dPayOrderS = $this->getDbshopTable('OrderTable')->StatsOrder($orderWhere." and refund_state<>1 and ((order_state>=0 and order_state<20) or (order_state>20 and (pay_code='xxfk' or pay_code='hdfk')))", '', 'otime');
            $dOrderArray = array();
            if(is_array($dPayOrderS) and !empty($dPayOrderS)) {
                foreach($dPayOrderS as $dValue) {
                    $dOrderArray[$dValue['otime']] = $dValue['order_count'];
                }
            }
        } else {//销售额走势
            $payOrderTotal = $this->getDbshopTable('OrderTable')->StatsOrder($orderWhere." and refund_state<>1 and ((pay_code<>'xxfk' and pay_code<>'hdfk' and order_state>=20) or order_state=60)", '', 'otime', 'SUM(order_amount) AS order_total');
            $payOrderTotalArray = array();
            if(is_array($payOrderTotal) and !empty($payOrderTotal)) {
                foreach($payOrderTotal as $value) {
                    $payOrderTotalArray[$value['otime']] = $value['order_total'];
                }
            }
            $dPayOrderTotal = $this->getDbshopTable('OrderTable')->StatsOrder($orderWhere." and refund_state<>1 and ((order_state>=0 and order_state<20) or (order_state>=20 and (pay_code='xxfk' or pay_code='hdfk')))", '', 'otime', 'SUM(order_amount) AS order_total');
            $dOrderTotalArray = array();
            if(is_array($dPayOrderTotal) and !empty($dPayOrderTotal)) {
                foreach($dPayOrderTotal as $dValue) {
                    $dOrderTotalArray[$dValue['otime']] = $dValue['order_total'];
                }
            }
        }

        $dateArray = array();
        $pArray    = array();
        $dArray    = array();
        $zArray    = array();
        $pTArray   = array();
        $dTArray   = array();
        $zTArray   = array();
        for($i=$dateNum; $i>=0; $i--) {
            $dateStr = date("Y-m-d", strtotime("-".$i." day"));
            $dateArray[] = '\''.$dateStr.'\'';
            if($saleType == 'num') {
                $pArray[$dateStr]   = isset($payOrderArray[$dateStr])       ? $payOrderArray[$dateStr]      : 0;
                $dArray[$dateStr]   = isset($dOrderArray[$dateStr])         ? $dOrderArray[$dateStr]        : 0;
                $zArray[$dateStr]   = $pArray[$dateStr] + $dArray[$dateStr];
            } else {
                $pTArray[$dateStr]  = isset($payOrderTotalArray[$dateStr])  ? $payOrderTotalArray[$dateStr] : 0;
                $dTArray[$dateStr]  = isset($dOrderTotalArray[$dateStr])    ? $dOrderTotalArray[$dateStr]   : 0;
                $zTArray[$dateStr]  = $pTArray[$dateStr] + $dTArray[$dateStr];
            }
        }
        if($saleType == 'num') {
            $array['pay_order']     = implode(',', $pArray);
            $array['d_pay_order']   = implode(',', $dArray);
            $array['z_pay_order']   = implode(',', $zArray);
        } else {
            $array['p_total_order'] = implode(',', $pTArray);
            $array['d_total_order'] = implode(',', $dTArray);
            $array['z_total_order'] = implode(',', $zTArray);
        }
        $array['date_str']      = implode(',', $dateArray);
        $array['sale_type']     = $saleType;
        return $array;
    }
    /**
     * 会员排行
     * @return array
     */
    public function usersOrderAction()
    {
        $array = array();

        $array['page_type'] = 'usersOrder';//页面类型，用于标识页面左侧的菜单

        //时间判定
        $dateNum = (int) $this->request->getQuery('dateNum');
        if($dateNum <= 0) $dateNum = 7 - 1;
        else $dateNum = $dateNum - 1;//因为是从0开始算，所以减去1
        $dateTime = strtotime("-".$dateNum." day");
        $array['date_num'] = $dateNum;
        $orderWhere= 'o.order_time>'.$dateTime;

        //当GET传过时间值时
        $getArray['start_time'] = $this->request->getQuery('start_time');
        $getArray['end_time']   = $this->request->getQuery('end_time');
        if(!empty($getArray['start_time']) and !empty($getArray['end_time'])) {
            $startTime  = strtotime($getArray['start_time']);
            $endTime    = strtotime($getArray['end_time'].' 24:00:00');
            $dateNum    = round(($endTime - $startTime)/(60*60*24))-1;
            $array['date_num']  = '';
            $array['start_time']= $getArray['start_time'];
            $array['end_time']  = $getArray['end_time'];
            $orderWhere= 'o.order_time>='.$startTime.' and o.order_time<='.$endTime;

        }

        $page = $this->params('page',1);
        $array['user_list'] = $this->getDbshopTable('UserTable')->userRanking(array('page'=>$page, 'page_num'=>20), '', 'and '.$orderWhere);
        $array['page']      = $page;

        return $array;
    }
    /**
     * 销售明细
     * @return array
     */
    public function saleListAction()
    {
        $array = array();

        $array['page_type'] = 'saleList';//页面类型，用于标识页面左侧的菜单

        //时间判定
        $dateNum = (int) $this->request->getQuery('dateNum');
        if($dateNum <= 0) $dateNum = 7 - 1;
        else $dateNum = $dateNum - 1;//因为是从0开始算，所以减去1
        $dateTime = strtotime("-".$dateNum." day");
        $array['date_num'] = $dateNum;
        $orderWhere= 'dbshop_order.order_time>'.$dateTime;

        //当GET传过时间值时
        $getArray['start_time'] = $this->request->getQuery('start_time');
        $getArray['end_time']   = $this->request->getQuery('end_time');
        if(!empty($getArray['start_time']) and !empty($getArray['end_time'])) {
            $startTime  = strtotime($getArray['start_time']);
            $endTime    = strtotime($getArray['end_time'].' 24:00:00');
            $dateNum    = round(($endTime - $startTime)/(60*60*24))-1;
            $array['date_num']  = '';
            $array['start_time']= $getArray['start_time'];
            $array['end_time']  = $getArray['end_time'];
            $orderWhere= 'dbshop_order.order_time>='.$startTime.' and dbshop_order.order_time<='.$endTime;

        }

        $page = $this->params('page',1);
        $array['order_goods_list'] = $this->getDbshopTable('OrderGoodsTable')->pageListOrderGoods(array('page'=>$page, 'page_num'=>20), array($orderWhere.' and dbshop_order.order_state>0 and dbshop_order.refund_state<>1'));
        $array['page']      = $page;

        return $array;
    }
    /**
     * 销售排行
     * @return array
     */
    public function saleOrderAction()
    {
        $array = array();

        $array['page_type'] = 'saleOrder';//页面类型，用于标识页面左侧的菜单

        //时间判定
        $dateNum = (int) $this->request->getQuery('dateNum');
        if($dateNum <= 0) $dateNum = 7 - 1;
        else $dateNum = $dateNum - 1;//因为是从0开始算，所以减去1
        $dateTime = strtotime("-".$dateNum." day");
        $array['date_num'] = $dateNum;
        $orderWhere= 'dbshop_order.order_time>'.$dateTime;

        //当GET传过时间值时
        $getArray['start_time'] = $this->request->getQuery('start_time');
        $getArray['end_time']   = $this->request->getQuery('end_time');
        if(!empty($getArray['start_time']) and !empty($getArray['end_time'])) {
            $startTime  = strtotime($getArray['start_time']);
            $endTime    = strtotime($getArray['end_time'].' 24:00:00');
            $dateNum    = round(($endTime - $startTime)/(60*60*24))-1;
            $array['date_num']  = '';
            $array['start_time']= $getArray['start_time'];
            $array['end_time']  = $getArray['end_time'];
            $orderWhere= 'dbshop_order.order_time>='.$startTime.' and dbshop_order.order_time<='.$endTime;

        }

        $page = $this->params('page',1);
        $array['order_goods_list'] = $this->getDbshopTable('OrderGoodsTable')->statsOrderGoods(array('page'=>$page, 'page_num'=>20), array($orderWhere.' and dbshop_order.order_state>0 and dbshop_order.refund_state<>1'));
        $array['page']      = $page;

        return $array;
    }
    /**
     * 流量概况
     * @return array
     */
    public function indexAction()
    {
        $array = array();

        $array['page_type'] = 'overview';//页面类型，用于标识页面左侧的菜单

        $retArray = $this->loginBaiduTongji();
        if(isset($retArray['ucid']) and !empty($retArray['ucid']) and isset($retArray['st']) and !empty($retArray['st'])) {
            $resultArray = $this->apiPostData($this->getApiConData($retArray['st'], $this->getDataValue(array('start_date'=>date("Ymd",strtotime("-1 day"))))), $this->apiUlr . '/getData', $this->getApiHeaders($retArray['ucid']));
            $result      = json_decode($resultArray['raw']);
            $array['overview'] = $result->body->data[0]->result;

            $resultArray = $this->apiPostData($this->getApiConData($retArray['st'], $this->getDataValue(array('metrics'=>'pv_count,ip_count', 'gran'=>'hour'))), $this->apiUlr . '/getData', $this->getApiHeaders($retArray['ucid']));
            $result      = json_decode($resultArray['raw']);
            $array['qushi'] = $result->body->data[0]->result;
            $pv = '';
            $ip = '';
            if(isset($array['qushi']->items[1]) and !empty($array['qushi']->items[1])) {
                $pvArray = array();
                $ipArray = array();
                foreach($array['qushi']->items[1] as $qValue) {
                    $pvArray[] = $qValue[0];
                    $ipArray[] = $qValue[1];
                }
                $pv = implode(',', $pvArray);
                $ip = implode(',', $ipArray);
            }
            $array['pv'] = str_replace('--', '0', $pv);
            $array['ip'] = str_replace('--', '0', $ip);

            $resultArray = $this->apiPostData($this->getApiConData($retArray['st'], $this->getDataValue(array('method'=>'overview/getCommonTrackRpt', 'metrics'=>'pv_count', 'max_results'=>10))), $this->apiUlr . '/getData', $this->getApiHeaders($retArray['ucid']));
            $result      = json_decode($resultArray['raw']);
            $array['track_rpt'] = $result->body->data[0]->result;

            $resultArray = $this->apiPostData($this->getApiConData($retArray['st'], $this->getDataValue(array('method'=>'overview/getDistrictRpt', 'metrics'=>'pv_count'))), $this->apiUlr . '/getData', $this->getApiHeaders($retArray['ucid']));
            $result      = json_decode($resultArray['raw']);
            $array['district_rpt'] = $result->body->data[0]->result;

        }

        return $array;
    }
    /**
     * 趋势分析
     * @return array
     */
    public function trandAction()
    {
        $array = array();
        $array['page_type'] = 'trand';

        $retArray = $this->loginBaiduTongji();
        if(isset($retArray['ucid']) and !empty($retArray['ucid']) and isset($retArray['st']) and !empty($retArray['st'])) {
            $metrics     = 'pv_count,pv_ratio,visit_count,visitor_count,new_visitor_count,new_visitor_ratio,ip_count,bounce_ratio,avg_visit_time,avg_visit_pages,trans_count,trans_ratio';
            $resultArray = $this->apiPostData($this->getApiConData($retArray['st'], $this->getDataValue(array('metrics'=>$metrics, 'method'=>'trend/time/a', 'gran'=>'hour'))), $this->apiUlr . '/getData', $this->getApiHeaders($retArray['ucid']));
            $result      = json_decode($resultArray['raw']);
            $array['trand'] = $result->body->data[0]->result;

            $timeStr    = '';
            $pvStr      = '';
            $ipStr      = '';
            $visitorStr = '';
            $bounceStr  = '';
            if(isset($array['trand']->items[0]) and !empty($array['trand']->items[0])) {
                $timeArray      = array();
                $pvArray        = array();
                $ipArray        = array();
                $visitorArray   = array();
                foreach($array['trand']->items[0] as $tKey => $tValue) {
                    $timeArray[] = "'".$tValue[0]."'";

                    $pvArray[]      = $array['trand']->items[1][$tKey][0];
                    $ipArray[]      = $array['trand']->items[1][$tKey][6];
                    $visitorArray[] = $array['trand']->items[1][$tKey][3];
                    $bounceArray[]  = floor($array['trand']->items[1][$tKey][7]);
                }
                $timeArray      = array_reverse($timeArray);
                $ipArray        = array_reverse($ipArray);
                $pvArray        = array_reverse($pvArray);
                $visitorArray   = array_reverse($visitorArray);

                $timeStr = implode(',', $timeArray);

                $pvStr      = implode(',', $pvArray);
                $ipStr      = implode(',', $ipArray);
                $visitorStr = implode(',', $visitorArray);
            }
            $array['timestr'] = str_replace('--', '0', $timeStr);

            $array['pvstr']         = str_replace('--', '0', $pvStr);
            $array['ipstr']         = str_replace('--', '0', $ipStr);
            $array['visitorstr']    = str_replace('--', '0', $visitorStr);
        }

        return $array;
    }
    /**
     * 全部来源
     * @return array
     */
    public function sourceAction()
    {
        $array = array();
        $array['page_type'] = 'source';

        $retArray = $this->loginBaiduTongji();
        if(isset($retArray['ucid']) and !empty($retArray['ucid']) and isset($retArray['st']) and !empty($retArray['st'])) {
            $resultArray = $this->apiPostData($this->getApiConData($retArray['st'], $this->getDataValue(array('method'=>'overview/getCommonTrackRpt', 'metrics'=>'pv_count', 'max_results'=>10))), $this->apiUlr . '/getData', $this->getApiHeaders($retArray['ucid']));
            $result      = json_decode($resultArray['raw']);
            $array['track_rpt'] = $result->body->data[0]->result;
        }

        return $array;
    }
    /**
     * 百度统计站点列表
     * @throws \Exception
     */
    public function siteListAction()
    {
        $retArray = $this->loginBaiduTongji();
        if(isset($retArray['ucid']) and !empty($retArray['ucid']) and isset($retArray['st']) and !empty($retArray['st'])) {
            $resultArray = $this->apiPostData($this->getApiConData($retArray['st']), $this->apiUlr . '/getSiteList', $this->getApiHeaders($retArray['ucid']));
            if(isset($resultArray['body']['data'][0]['list']) and !empty($resultArray['body']['data'][0]['list'])) {
                $arrayWrite = new \Zend\Config\Writer\PhpArray();
                $arrayWrite->toFile(DBSHOP_PATH . '/data/moduledata/Analytics/SiteArray.php', $resultArray['body']['data'][0]['list']);
                $selectHtml = '';
                foreach($resultArray['body']['data'][0]['list'] as $value) {
                    $selectHtml .= '<option value="'.$value['site_id'].'">'.$value['domain'].'</option>';
                }
                exit(json_encode(array('state'=>'true', 'select_html'=>$selectHtml)));
            }
        }
        exit();
    }
    /**
     * 初始化
     */
    private function baiduTongJiInit()
    {
        if($this->request->isPost()) {
            $postArray = $this->request->getPost()->toArray();
            $this->userName     = trim($postArray['baidu_tongji_username']);
            $this->userPasswd   = trim($postArray['baidu_tongji_userpasswd']);
            $this->token        = trim($postArray['baidu_tongji_token']);
        } else {
            if(empty($this->userName))      $this->userName     = $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_tongji_baidu_user');
            if(empty($this->userPasswd))    $this->userPasswd   = $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_tongji_baidu_passwd');
            if(empty($this->token))         $this->token        = $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_tongji_token');
            if(empty($this->siteId))        $this->siteId       = $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_site_id');
        }
        if(empty($this->headers))       $this->headers      = array('UUID: '.$this->uUid, 'account_type: '.$this->accountType, 'Content-Type:  data/gzencode and rsa public encrypt;charset=UTF-8');
    }
    /**
     * 获取统计报告信息
     * @param array $data
     * @return array
     */
    private function getDataValue(array $data=array())
    {
        $valueArray = array(
            'site_id'       => $this->siteId,       //站点ID
            'method'        => (isset($data['method'])      ? $data['method']       : 'overview/getTimeTrendRpt'),//趋势分析报告
            'start_date'    => (isset($data['start_date'])  ? $data['start_date']   : date("Ymd")),             //所查询数据的起始日期
            'end_date'      => (isset($data['end_date'])    ? $data['end_date']     : date("Ymd")),             //所查询数据的结束日期
            'metrics'       => (isset($data['metrics'])     ? $data['metrics']      : 'pv_count,visitor_count,ip_count,bounce_ratio,avg_visit_time'),//所查询指标为PV和UV
            'max_results'   => (isset($data['max_results']) ? $data['max_results']  : 0),                       //返回所有条数
            'gran'          => (isset($data['gran'])        ? $data['gran']         : 'day')                    //按天粒度
        );

        return $valueArray;
    }
    /**
     * 登录数据
     * @return array
     */
    private function getLoginData()
    {
        return array(
            'username'  => $this->userName,
            'token'     => $this->token,
            'functionName'  => 'doLogin',
            'uuid'      => $this->uUid,
            'request'   => array(
                'password' => $this->userPasswd
            )
        );
    }
    /**
     * api登录调用
     * @param $st
     * @param null $body
     * @return array
     */
    private function getApiConData($st, $body=null)
    {
        return array(
            'header'    => array(
                'username'  => $this->userName,
                'password'  => $st,
                'token'     => $this->token,
                'account_type' => $this->accountType
            ),
            'body' => $body
        );
    }
    /**
     * api的headers
     * @param $ucid
     * @return array
     */
    private function getApiHeaders($ucid)
    {
        return array('UUID: '.$this->uUid, 'USERID: '.$ucid, 'Content-Type:  data/json;charset=UTF-8');
    }
    private function apiPostData($data, $url, $headers)
    {
        $postData = json_encode($data);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $tmpRet = curl_exec($curl);
        if (curl_errno($curl)) {
            echo '[error] CURL ERROR: ' . curl_error($curl) . PHP_EOL;
        }
        curl_close($curl);
        $tmpArray = json_decode($tmpRet, true);
        if (isset($tmpArray['header']) && isset($tmpArray['body'])) {
            return array('header' => $tmpArray['header'], 'body' => $tmpArray['body'], 'raw' => $tmpRet);
        } else {
            echo "[error] SERVICE ERROR: {$tmpRet}" . PHP_EOL;
        }
    }
    /**
     * 登录百度统计
     * @return array|bool
     */
    private function loginBaiduTongji()
    {
        $this->baiduTongJiInit();

        $loginArray = $this->loginConnection($this->getLoginData());
        if($loginArray['returnCode'] === 0) {
            $retData = gzinflate(substr($loginArray['retData'], 10, -8));
            $retArray= json_decode($retData, true);
            if($retArray['retcode'] === 0) {
                return array('ucid'=>$retArray['ucid'], 'st'=>$retArray['st']);
            }
        }
        return false;
    }
    /**
     * 百度统计登录连接
     * @param $data
     * @return array
     */
    private function loginConnection($data)
    {
        $postData = $this->genPostData($data);
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $this->loginUlr);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $tmpInfo = curl_exec($curl);
        if (curl_errno($curl)) {
            echo '[error] CURL ERROR: ' . curl_error($curl). PHP_EOL;
        }
        curl_close($curl);

        $returnCode = ord($tmpInfo[0])*64 + ord($tmpInfo[1]);

        if ($returnCode === 0) {
            return array('returnCode'=>$returnCode, 'retData'=>substr($tmpInfo, 8));
        }
        return array('returnCode'=>$returnCode);
    }
    /**
     * 百度统计登录时对post信息进行处理
     * @param $data
     * @return string
     */
    private function genPostData($data) {
        if(!function_exists('gzdecode')) {
            $gzData = $this->eGzdecode(json_encode($data), 9);
        } else {
            $gzData = gzencode(json_encode($data), 9);
        }
        for ($index = 0, $enData = ''; $index < strlen($gzData); $index += 117) {
            $gzPackData = substr($gzData, $index, 117);
            $enData .= $this->pubEncrypt($gzPackData);
        }
        return $enData;
    }
    /**
     * setup public key
     * @return boolean
     */
    private function setupPublicKey()
    {
        if(is_resource($this->publicKey))
        {
            return true;
        }
        $file = DBSHOP_PATH . '/module/Analytics/config/' . 'api_pub.key';
        $puk = file_get_contents($file);
        $this->publicKey = openssl_pkey_get_public($puk);
        return true;
    }

    /**
     * pub encrypt
     * @param string $data
     * @return string
     */
    private function pubEncrypt($data)
    {
        if(!is_string($data))
        {
            return null;
        }
        $this->setupPublicKey();
        $ret = openssl_public_encrypt($data, $encrypted, $this->publicKey);
        if($ret)
        {
            return $encrypted;
        }
        else
        {
            return null;
        }
    }
    /**
     * eGzdecode
     * @param $data
     * @return string
     */
    function eGzdecode($data) {
        $flags = ord(substr($data, 3, 1));
        $headerlen = 10;
        $extralen = 0;
        $filenamelen = 0;
        if ($flags & 4) {
            $extralen = unpack('v' ,substr($data, 10, 2));
            $extralen = $extralen[1];
            $headerlen += 2 + $extralen;
        }
        if ($flags & 8) {
            $headerlen = strpos($data, chr(0), $headerlen) + 1;
        }
        if ($flags & 16) {
            $headerlen = strpos($data, chr(0), $headerlen) + 1;
        }
        if ($flags & 2) {
            $headerlen += 2;
        }
        $unpacked = @gzinflate(substr($data, $headerlen));
        if ($unpacked === false) {
            $unpacked = $data;
        }
        return $unpacked;
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