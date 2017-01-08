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

use User\FormValidate\FormUserValidate;
use Zend\View\Model\ViewModel;

class HomemoneyController extends FronthomeController
{
    private $dbTables = array();
    private $translator;

    /**
     * 账户余额记录
     * @return ViewModel
     */
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('/shopfront/home/homemoney.phtml');

        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('账户余额');

        $array = array();
        //用于模板分页那里
        $array['page_action'] = array();

        $searchArray  = array();
        if($this->request->isGet()) {
            $searchArray['search_content'] = $this->request->getQuery('search_content');
            $array['searchArray'] = $searchArray;
        }

        $array['user_info'] = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        $searchArray['user_id'] = $array['user_info']->user_id;

        $page = $this->params('page',1);
        $array['user_money_log'] = $this->getDbshopTable('UserMoneyLogTable')->listUserMoneyLog(array('page'=>$page, 'page_num'=>20), $searchArray, 'front');

        //在线支付方式，用于充值处理
        $array['payment'] = $this->listPayment();

        $view->setVariables($array);
        return $view;
    }
    /**
     * 账户消费记录
     */
    public function paylogAction()
    {
        $view = new ViewModel();
        $view->setTemplate('/shopfront/home/homemoney.phtml');

        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('账户余额');

        $array = array();
        //用于模板分页那里
        $array['page_action'] = array('action'=>'paylog');

        $searchArray  = array('money_pay_type'=>2);
        if($this->request->isGet()) {
            $searchArray['search_content'] = $this->request->getQuery('search_content');
            $array['searchArray'] = $searchArray;
        }

        $array['user_info'] = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        $searchArray['user_id'] = $array['user_info']->user_id;

        $page = $this->params('page',1);
        $array['user_money_log'] = $this->getDbshopTable('UserMoneyLogTable')->listUserMoneyLog(array('page'=>$page, 'page_num'=>20), $searchArray, 'front');

        //在线支付方式，用于充值处理
        $array['payment'] = $this->listPayment();

        $view->setVariables($array);
        return $view;
    }
    /**
     * 账户充值记录
     */
    public function paychecklogAction()
    {
        $view = new ViewModel();
        $view->setTemplate('/shopfront/home/paycheck.phtml');

        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('账户余额');

        $array = array();
        //用于模板分页那里
        $array['page_action'] = array('action'=>'paychecklog');

        $searchArray  = array('money_pay_type'=>1);
        if($this->request->isGet()) {
            $searchArray['search_content'] = $this->request->getQuery('search_content');
            $array['searchArray'] = $searchArray;
        }
        $array['user_info'] = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        $searchArray['user_id'] = $array['user_info']->user_id;

        $page = $this->params('page',1);
        $array['user_paycheck_log'] = $this->getDbshopTable('PayCheckTable')->listPayCheck(array('page'=>$page, 'page_num'=>20), $searchArray, 'front');

        //在线支付方式，用于充值处理
        $array['payment'] = $this->listPayment();

        $view->setVariables($array);
        return $view;
    }
    /**
     * 账户提现记录
     */
    public function withdrawlogAction()
    {
        $view = new ViewModel();
        $view->setTemplate('/shopfront/home/withdraw-log.phtml');

        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('账户余额');

        $array = array();

        $searchArray  = array();
        if($this->request->isGet()) {
            $searchArray['search_content'] = $this->request->getQuery('search_content');
        }

        $array['user_info'] = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        $searchArray['user_id'] = $array['user_info']->user_id;

        $page = $this->params('page',1);
        $array['user_withdraw_log'] = $this->getDbshopTable('WithdrawLogTable')->listWithdrawLog(array('page'=>$page, 'page_num'=>20), $searchArray, 'front');

        //在线支付方式，用于充值处理
        $array['payment'] = $this->listPayment();

        $view->setVariables($array);
        return $view;
    }
    /**
     * 账户退款记录
     */
    public function refundlogAction()
    {
        $view = new ViewModel();
        $view->setTemplate('/shopfront/home/homemoney.phtml');

        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('账户余额');

        $array = array();
        //用于模板分页那里
        $array['page_action'] = array('action'=>'refundlog');

        $searchArray  = array('money_pay_type'=>4);
        if($this->request->isGet()) {
            $searchArray['search_content'] = $this->request->getQuery('search_content');
            $array['searchArray'] = $searchArray;
        }

        $array['user_info'] = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        $searchArray['user_id'] = $array['user_info']->user_id;

        $page = $this->params('page',1);
        $array['user_money_log'] = $this->getDbshopTable('UserMoneyLogTable')->listUserMoneyLog(array('page'=>$page, 'page_num'=>20), $searchArray, 'front');

        //在线支付方式，用于充值处理
        $array['payment'] = $this->listPayment();

        $view->setVariables($array);
        return $view;
    }
    /**
     * 提现申请提交
     */
    public function addwithdrawAction()
    {
        if($this->request->isPost()) {
            $withdrawArray = $this->request->getPost()->toArray();
            //服务器端进行验证
            $withdrawValidate = new FormUserValidate($this->getDbshopLang());
            $withdrawValidate->checkUserForm($this->request->getPost(), 'withdrawLog');

            $withdrawArray['user_id']   = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
            $withdrawArray['user_name'] = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name');
            $withdrawArray['currency_code'] = $this->getServiceLocator()->get('frontHelper')->getFrontDefaultCurrency();

            //检查是否在表中存在该用户的待处理申请
            $checkState = $this->checkWithdraw($withdrawArray['user_id']);
            if($checkState != 'true') exit($checkState);

            //最后进行校验，提现金额是否大于账户余额
            $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$withdrawArray['user_id']));
            if($userInfo->user_money < $withdrawArray['money_change_num']) exit($this->getDbshopLang()->translate('您的账户余额小于提现额，无法申请提现！'));

            $state = $this->getDbshopTable('WithdrawLogTable')->addWithdrawLog($withdrawArray);
            if($state) exit('true');
        }
        exit($this->getDbshopLang()->translate('提现申请失败！'));
    }
    /**
     * 充值支付
     */
    public function mypaytoAction()
    {
        if($this->request->isPost()) {
            $payArray = array();
            $payArray['money_change_num'] = $this->request->getPost('pay_change_num');
            $payArray['pay_code']         = $this->request->getPost('payment_code');
            $payArray['pay_name']         = $this->request->getPost('pay_name');
            $payArray['user_id']          = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
            $payArray['user_name']        = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name');
            $payArray['currency_code']    = $this->getServiceLocator()->get('frontHelper')->getFrontDefaultCurrency();
            $payArray['pay_state']        = 10;//未充值
            $payArray['paycheck_info']    = $payArray['pay_name'].' '.$this->getDbshopLang()->translate('进行充值处理');
            $state = $this->getDbshopTable('PayCheckTable')->addPayCheck($payArray);
            if($state) exit(json_encode(array('state'=>'true', 'pay_url'=>$this->url()->fromRoute('frontmoney/default/paycheck_id', array('action'=>'paycheckPay', 'paycheck_id'=>$state)))));
        }
        exit(json_encode(array('state'=>'false', 'message'=>$this->getDbshopLang()->translate('充值处理未成功！'))));
    }
    /**
     * 在线支付充值处理
     */
    public function paycheckPayAction()
    {
        $paycheckId     = (int) $this->params('paycheck_id');
        $paycheckInfo   = $this->getDbshopTable('PayCheckTable')->infoPayCheck(array('paycheck_id'=>$paycheckId));
        if($paycheckInfo->pay_code == '' or $paycheckInfo->pay_state > 10) { return $this->redirect()->toRoute('frontmoney/default', array('action'=>'paychecklog'));  }

        //打包数据，传给下面的支付输出
		$httpHost = $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost();
		$httpType = $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps();
        $paymentData = array(
            'shop_name' => $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name'),
            'order_name'=> $this->getDbshopLang()->translate('会员充值'),
            'goods_name' => $this->getDbshopLang()->translate('会员充值，充值会员:').$paycheckInfo->user_name,
            'paycheck'=> $paycheckInfo,
            'return_url'=> $httpType . $httpHost . $this->url()->fromRoute('frontmoney/default/paycheck_id', array('action'=>'paycheckReturnPay', 'paycheck_id'=>$paycheckId)),
            'notify_url'=> $httpType . $httpHost . $this->url()->fromRoute('frontmoney/default/paycheck_id', array('action'=>'paycheckNotifyPay', 'paycheck_id'=>$paycheckId)),
            'cancel_url'=> $httpType . $httpHost . $this->url()->fromRoute('frontmoney/default', array('action'=>'paychecklog')),
            'order_url' => $httpType . $httpHost . $this->url()->fromRoute('frontmoney/default', array('action'=>'paychecklog')),
        );
        $result = $this->getServiceLocator()->get($paycheckInfo->pay_code)->paycheckPaymentTo($paymentData);

        if($paycheckInfo->pay_code == 'wxpay') {//微信扫码支付页面
            $view           = new ViewModel();
            $view->setTemplate('/shopfront/home/paycheck-pay.phtml');
            //顶部title使用
            $this->layout()->title_name = $this->getDbshopLang()->translate('微信扫码支付');

            $view->setVariables(array('result' => $result, 'paycheckinfo'=> $paycheckInfo, 'qrcode_url' => $httpType . $httpHost . $this->url()->fromRoute('frontorder/default', array('action'=>'orderQrcode')).'?data='.$result['code_url']));
            return $view;

        } else exit();
    }
    /**
     * 支付返回验证操作
     */
    public function paycheckReturnPayAction()
    {
        $view           = new ViewModel();
        $view->setTemplate('/shopfront/home/order-return-pay.phtml');

        $paycheckId     = (int) $this->params('paycheck_id');
        $paycheckInfo   = $this->getDbshopTable('PayCheckTable')->infoPayCheck(array('paycheck_id'=>$paycheckId));

        //判断支付方式是否非空，或者是否与充值者相对应
        if($paycheckInfo->pay_code == '' or $paycheckInfo->user_id != $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')) return $this->redirect()->toRoute('frontmoney/default', array('action'=>'paychecklog'));

        if($paycheckInfo->pay_state == 20) {//支付成功显示页面
            $array['html'] = '<table class="table table-bordered"><tbody><tr><td><h3>'.$this->getDbshopLang()->translate('充值支付成功。').' <a href="'.$this->url()->fromRoute('frontmoney/default', array('action'=>'paychecklog')).'">'.$this->getDbshopLang()->translate('返回充值列表').'</a></h3></td></tr></tbody></table>';
            $view->setVariables($array);
            return $view;
        }

        //语言包及支付处理，在支付模块中进行
        $language      = $this->paymentLanguage();

        $array = $this->getServiceLocator()->get($paycheckInfo->pay_code)->paycheckPaymentReturn($paycheckInfo, $language);

        //付款成功
        if(isset($array['payFinish']) and $array['payFinish']) {
            $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$paycheckInfo->user_id));
            $moneyLogArray['user_id']         = $userInfo->user_id;
            $moneyLogArray['user_name']       = $userInfo->user_name;
            $moneyLogArray['money_change_num']= $paycheckInfo->money_change_num;
            $moneyLogArray['money_pay_state'] = 20;//20是已经处理（充值后者减值，10是待处理）
            $moneyLogArray['money_pay_type']  = 1;//支付类型，1充值，2消费，3提现，4退款
            $moneyLogArray['money_changed_amount'] = $userInfo->user_money + $moneyLogArray['money_change_num'];
            $moneyLogArray['money_pay_info']  = $paycheckInfo->paycheck_info;

            $state = $this->getDbshopTable('UserMoneyLogTable')->addUserMoneyLog($moneyLogArray);
            if($state) {
                //对会员表中的余额总值进行更新
                $this->getDbshopTable('UserTable')->updateUser(array('user_money'=>$moneyLogArray['money_changed_amount']), array('user_id'=>$userInfo->user_id));
                //更新充值记录中的支付状态
                $this->getDbshopTable('PayCheckTable')->updatePayCheck(array('pay_state'=>20), array('paycheck_id'=>$paycheckId));
            }
        } else {
            $array['html'] = '<table class="table table-bordered"><tbody><tr><td><h3>'.$this->getDbshopLang()->translate('支付不成功，如有疑问，请联系管理员。').'</h3></td></tr></tbody></table>';
        }

        $view->setVariables($array);
        return $view;
    }
    /**
     * 支付通知接收，属于支付异步验证
     */
    public function paycheckNotifyPayAction()
    {
        $paycheckId     = (int) $this->params('paycheck_id');
        $paycheckInfo   = $this->getDbshopTable('PayCheckTable')->infoPayCheck(array('paycheck_id'=>$paycheckId));
        if($paycheckInfo->pay_code == '') exit();

        //语言包及支付处理，在支付模块中进行
        $language      = $this->paymentLanguage();
        $array = $this->getServiceLocator()->get($paycheckInfo->pay_code)->paycheckPaymentNotify($paycheckInfo, $language);
        //付款操作处理
        if(isset($array['payFinish']) and $array['payFinish']) {
            if($array['stateNum'] == 15) {//更新充值记录中的支付状态,处理中，等待确认付款
                $this->getDbshopTable('PayCheckTable')->updatePayCheck(array('pay_state'=>15), array('paycheck_id'=>$paycheckId));
            }
            if($array['stateNum'] == 20) {//更新充值记录中的支付状态,付款完成
                $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$paycheckInfo->user_id));
                $moneyLogArray['user_id']         = $userInfo->user_id;
                $moneyLogArray['user_name']       = $userInfo->user_name;
                $moneyLogArray['money_change_num']= $paycheckInfo->money_change_num;
                $moneyLogArray['money_pay_state'] = 20;//20是已经处理（充值后者减值，10是待处理）
                $moneyLogArray['money_pay_type']  = 1;//支付类型，1充值，2消费，3提现，4退款
                $moneyLogArray['money_changed_amount'] = $userInfo->user_money + $moneyLogArray['money_change_num'];
                $moneyLogArray['money_pay_info']  = $paycheckInfo->paycheck_info;

                $state = $this->getDbshopTable('UserMoneyLogTable')->addUserMoneyLog($moneyLogArray);
                if($state) {
                    //对会员表中的余额总值进行更新
                    $this->getDbshopTable('UserTable')->updateUser(array('user_money'=>$moneyLogArray['money_changed_amount']), array('user_id'=>$userInfo->user_id));
                    //更新充值记录中的支付状态
                    $this->getDbshopTable('PayCheckTable')->updatePayCheck(array('pay_state'=>20), array('paycheck_id'=>$paycheckId));
                }
            }
        }

        exit($array['message']);
    }
    /**
     * @return array
     */
    private function paymentLanguage()
    {
        $array = array(
            'pay_finish'  =>$this->getDbshopLang()->translate('充值完成').'&nbsp;<a class="btn btn-primary" href="'.$this->url()->fromRoute('frontmoney/default', array('action'=>'paychecklog')).'"><i class="icon-arrow-left icon-white"></i> '.$this->getDbshopLang()->translate('充值记录页').'</a>',
            'return'      =>$this->url()->fromRoute('frontmoney/default', array('action'=>'paychecklog')),
        );
        return $array;
    }
    /**
     * 获取是否有正在处理中的提现申请
     */
    public function infowithdrawAction()
    {
        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        exit($this->checkWithdraw($userId));
    }
    private function checkWithdraw($userId)
    {
        $withdrawInfo = $this->getDbshopTable('WithdrawLogTable')->infoWithdrawLog(array('user_id'=>$userId, 'withdraw_state'=>0));
        if(isset($withdrawInfo[0]) and !empty($withdrawInfo[0])) return $this->getDbshopLang()->translate('已经有一个正在处理中的提现申请，需要处理完毕后，才能进行再次申请。');
        return 'true';
    }
    /**
     * 获取支付列表（只显示线上支付，不包括 余额支付）
     */
    private function listPayment()
    {
        $xmlReader    = new \Zend\Config\Reader\Xml();
        $paymentArray = array();
        $xmlPath      = DBSHOP_PATH . '/data/moduledata/Payment/';
        if(is_dir($xmlPath)) {
            $dh = opendir($xmlPath);
            while (false !== ($fileName = readdir($dh))) {
                if($fileName != '.' and $fileName != '..' and $fileName != '.DS_Store') {
                    $paymentInfo = $xmlReader->fromFile($xmlPath . $fileName);

                    if(!in_array($paymentInfo['editaction'], array('xxzf', 'hdfk', 'yezf', 'malipay'))) {

                        //判断是否显示在当前平台
                        if(isset($paymentInfo['payment_show']['checked']) and !empty($paymentInfo['payment_show']['checked'])) {
                            $showArray = is_array($paymentInfo['payment_show']['checked']) ? $paymentInfo['payment_show']['checked'] : array($paymentInfo['payment_show']['checked']);
                            if(!in_array('pc', $showArray) and !in_array('all', $showArray)) continue;
                        } else continue;

                        //判断是否符合当前的货币要求
                        $currencyState = false;
                        if(isset($paymentInfo['payment_currency']['checked']) and !empty($paymentInfo['payment_currency']['checked'])) {
                            $currencyArray = is_array($paymentInfo['payment_currency']['checked']) ? $paymentInfo['payment_currency']['checked'] : array($paymentInfo['payment_currency']['checked']);
                            $currencyState = in_array($this->getServiceLocator()->get('frontHelper')->getFrontDefaultCurrency(), $currencyArray) ? true : false;
                        }

                        if($paymentInfo['payment_state']['checked'] == 1 and $currencyState) {
                            $paymentArray[] = $paymentInfo;
                        }
                    }

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

        return $paymentArray;
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