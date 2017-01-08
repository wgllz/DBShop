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

namespace User\Controller;

use Zend\View\Model\ViewModel;
use Admin\Controller\BaseController;

class UsermoneyController extends BaseController
{
    /**
     * 余额日志记录列表
     * @return array
     */
    public function indexAction()
    {
        $array        = array();
        $searchArray  = array();
        if($this->request->isGet()) {
            $searchArray          = $this->request->getQuery()->toArray();
            $array['searchArray'] = $searchArray;
        }

        $page = $this->params('page',1);
        $array['user_money_log'] = $this->getDbshopTable()->listUserMoneyLog(array('page'=>$page, 'page_num'=>20), $searchArray);

        return $array;
    }
    /**
     * 管理员给用户充值
     */
    public function addUserMoneyAction()
    {
        $message = $this->getDbshopLang()->translate('用户充值失败！');
        if($this->request->isPost()) {
            $moneyLogArray = $this->request->getPost()->toArray();
            $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$moneyLogArray['user_id']));
            $moneyLogArray['user_name']       = $userInfo->user_name;
            $moneyLogArray['money_change_num']= $moneyLogArray['money_change_num'];
            $moneyLogArray['money_pay_state'] = 20;//20是已经处理（充值后者减值，10是待处理）
            $moneyLogArray['money_pay_type']  = 1;//支付类型，1充值，2消费，3提现，4退款
            $moneyLogArray['admin_id']        = $this->getServiceLocator()->get('adminHelper')->returnAuth('admin_id');
            $moneyLogArray['admin_name']      = $this->getServiceLocator()->get('adminHelper')->returnAuth('admin_name');
            $moneyLogArray['money_changed_amount'] = $userInfo->user_money + $moneyLogArray['money_change_num'];

            $state = $this->getDbshopTable()->addUserMoneyLog($moneyLogArray);
            if($state) {
                //对会员表中的余额总值进行更新
                $this->getDbshopTable('UserTable')->updateUser(array('user_money'=>$moneyLogArray['money_changed_amount']), array('user_id'=>$userInfo->user_id));

                //将充值信息写入充值记录表
                $currencyInfo = $this->getDbshopTable('CurrencyTable')->infoCurrency(array('currency_type'=>1));
                $paycheckArray = array();
                $paycheckArray['user_id']          = $userInfo->user_id;
                $paycheckArray['user_name']        = $userInfo->user_name;
                $paycheckArray['money_change_num'] = $moneyLogArray['money_change_num'];
                $paycheckArray['currency_code']    = $currencyInfo->currency_code;
                $paycheckArray['pay_state']        = 20;//10是未完成充值，15是正常处理中，20已经完成充值
                $paycheckArray['paycheck_time']    = time();
                $paycheckArray['paycheck_finish_time']= $paycheckArray['paycheck_time'];
                $paycheckArray['admin_id']         = $moneyLogArray['admin_id'];
                $paycheckArray['admin_name']       = $moneyLogArray['admin_name'];
                $paycheckArray['paycheck_info']    = $moneyLogArray['money_pay_info'];
                $this->getDbshopTable('PayCheckTable')->addPayCheck($paycheckArray);
                //操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('账户余额'), 'operlog_info'=>$this->getDbshopLang()->translate('管理员充值处理').' '.$this->getDbshopLang()->translate('被充值人:').$userInfo->user_name.' '.$this->getDbshopLang()->translate('充值金额:').$moneyLogArray['money_change_num']));
                $message = 'true';
            }
        }
        exit($message);
    }
    /**
     * 充值记录
     */
    public function paycheckAction()
    {
        $array        = array();
        $searchArray  = array();
        if($this->request->isGet()) {
            $searchArray          = $this->request->getQuery()->toArray();
            $array['searchArray'] = $searchArray;
        }

        $page = $this->params('page',1);
        $array['user_paycheck_log'] = $this->getDbshopTable('PayCheckTable')->listPayCheck(array('page'=>$page, 'page_num'=>20), $searchArray);

        return $array;
    }
    /**
     * 删除充值记录
     */
    public function paycheckDelAction()
    {
        if($this->request->isPost()) {
            $paycheckId = intval($this->request->getPost('paycheck_id'));
            $paycheckInfo = $this->getDbshopTable('PayCheckTable')->infoPayCheck(array('paycheck_id'=>$paycheckId));
            if(!empty($paycheckInfo)) {

                $this->getDbshopTable('PayCheckTable')->delPayCheck(array('paycheck_id'=>$paycheckId));

                //操作日志记录
                $stateArray = array(10=>$this->getDbshopLang()->translate('未充值'), 20=>$this->getDbshopLang()->translate('充值完成'));
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('充值记录'), 'operlog_info'=>$this->getDbshopLang()->translate('删除充值记录').' '.$this->getDbshopLang()->translate('被充值人:').$paycheckInfo->user_name.' '.$this->getDbshopLang()->translate('当前充值状态:').$stateArray[$paycheckInfo->pay_state].' '.$this->getDbshopLang()->translate('充值金额:').$paycheckInfo->money_change_num));

                exit('true');
            }
        }
        exit('false');
    }
    /**
     * 提现记录列表
     */
    public function withdrawAction()
    {
        $array        = array();
        $searchArray  = array();
        if($this->request->isGet()) {
            $searchArray          = $this->request->getQuery()->toArray();
            $array['searchArray'] = $searchArray;
        }

        $page = $this->params('page',1);
        $array['user_withdraw_log'] = $this->getDbshopTable('WithdrawLogTable')->listWithdrawLog(array('page'=>$page, 'page_num'=>20), $searchArray);

        return $array;
    }
    /**
     * 获取提现信息
     */
    public function withdrawInfoAction()
    {
        $array = array('state'=>'false', 'message'=>$this->getDbshopLang()->translate('获取提现信息失败！'));

        if($this->request->isPost()) {
            $withdrawId   = intval($this->request->getPost('withdraw_id'));
            if($withdrawId != 0) {
                $withdrawInfo = $this->getDbshopTable('WithdrawLogTable')->infoWithdrawLog(array('withdraw_id'=>$withdrawId));

                if($withdrawInfo[0]['withdraw_state'] != 0) exit($this->getDbshopLang()->translate('获取提现信息失败,已经处理，不能进行重复处理！'));

                if($withdrawInfo[0]) {
                    $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$withdrawInfo[0]['user_id']));
                    if($userInfo) {
                        $withdrawInfo[0]['user_money'] = $userInfo->user_money;
                        $withdrawInfo[0]['state'] = 'true';
                        exit(json_encode($withdrawInfo[0]));
                    }
                }
            }
        }
        exit(json_encode($array));
    }
    /**
     * 审核操作处理
     */
    public function withdrawUdateAction()
    {
        if($this->request->isPost()) {
            $withdrawArray = $this->request->getPost()->toArray();
            $withdrawId = $withdrawArray['withdraw_id'];
            unset($withdrawArray['withdraw_id']);

            $withdrawArray['withdraw_finish_time'] = time();
            $withdrawArray['admin_id']   = $this->getServiceLocator()->get('adminHelper')->returnAuth('admin_id');
            $withdrawArray['admin_name'] = $this->getServiceLocator()->get('adminHelper')->returnAuth('admin_name');

            $withdrawInfo = $this->getDbshopTable('WithdrawLogTable')->infoWithdrawLog(array('withdraw_id'=>$withdrawId));

            if($withdrawArray['withdraw_state'] == 2) {//拒绝提现处理
                $this->getDbshopTable('WithdrawLogTable')->updateWithdrawLog($withdrawArray, array('withdraw_id'=>$withdrawId));
                //操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('提现申请'), 'operlog_info'=>$this->getDbshopLang()->translate('审核提现申请').' '.$this->getDbshopLang()->translate('申请人:').$withdrawInfo[0]['user_name'].' '.$this->getDbshopLang()->translate('状态修改为:拒绝提现').' '.$this->getDbshopLang()->translate('提现金额:').$withdrawInfo[0]['money_change_num']));
                exit('true');
            }
            if($withdrawArray['withdraw_state'] == 1) {//下面为同意提现处理
                $userInfo     = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$withdrawInfo[0]['user_id']));
                if($userInfo->user_money < $withdrawInfo[0]['money_change_num']) exit($this->getDbshopLang()->translate('提现额度超出账户余额，无法提现！'));

                $state = $this->getDbshopTable('WithdrawLogTable')->updateWithdrawLog($withdrawArray, array('withdraw_id'=>$withdrawId));
                if($state) {
                    $moneyLogArray = array();
                    $moneyLogArray['user_name']       = $userInfo->user_name;
                    $moneyLogArray['money_change_num']= '-'.$withdrawInfo[0]['money_change_num'];
                    $moneyLogArray['money_pay_state'] = 20;
                    $moneyLogArray['money_pay_type']  = 3;//支付类型，1充值，2消费，3提现，4退款
                    $moneyLogArray['user_id']         = $withdrawInfo[0]['user_id'];
                    $moneyLogArray['user_name']       = $withdrawInfo[0]['user_name'];
                    $moneyLogArray['admin_id']        = $withdrawArray['admin_id'];
                    $moneyLogArray['admin_name']      = $withdrawArray['admin_name'];
                    $moneyLogArray['money_changed_amount'] = $userInfo->user_money - $withdrawInfo[0]['money_change_num'];
                    $moneyLogArray['money_pay_info']  = $this->getDbshopLang()->translate('提现已经完成');
                    $moneyState = $this->getDbshopTable()->addUserMoneyLog($moneyLogArray);
                    if($moneyState) {
                        //对会员表中的余额总值进行更新
                        $this->getDbshopTable('UserTable')->updateUser(array('user_money'=>$moneyLogArray['money_changed_amount']), array('user_id'=>$userInfo->user_id));
                        //操作日志
                        $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('提现申请'), 'operlog_info'=>$this->getDbshopLang()->translate('审核提现申请').' '.$this->getDbshopLang()->translate('申请人:').$withdrawInfo[0]['user_name'].' '.$this->getDbshopLang()->translate('状态修改为:提现已经完成').' '.$this->getDbshopLang()->translate('提现金额:').$withdrawInfo[0]['money_change_num']));
                        exit('true');
                    }
                }
            }
        }
        exit($this->getDbshopLang()->translate('审核操作处理失败！'));
    }
    /**
     * 删除提现记录
     */
    public function withdrawDelAction()
    {
        if($this->request->isPost()) {
            $withdrawId = intval($this->request->getPost('withdraw_id'));
            $withdrawInfo = $this->getDbshopTable('WithdrawLogTable')->infoWithdrawLog(array('withdraw_id'=>$withdrawId));
            if(!empty($withdrawInfo[0])) {
                $this->getDbshopTable('WithdrawLogTable')->delWithdrawLog(array('withdraw_id'=>$withdrawId));

                //操作日志记录
                $withdrawStateArray = array(0=>$this->getDbshopLang()->translate('处理中'), 1=>$this->getDbshopLang()->translate('同意且已完成'), 2=>$this->getDbshopLang()->translate('拒绝提现'));
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('提现申请'), 'operlog_info'=>$this->getDbshopLang()->translate('删除提现申请').' '.$this->getDbshopLang()->translate('申请人:').$withdrawInfo[0]['user_name'].' '.$this->getDbshopLang()->translate('当前申请状态:').$withdrawStateArray[$withdrawInfo[0]['withdraw_state']].' '.$this->getDbshopLang()->translate('提现金额:').$withdrawInfo[0]['money_change_num']));

                exit('true');
            }
        }
        exit('false');
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'UserMoneyLogTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}
