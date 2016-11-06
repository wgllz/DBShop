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

namespace Currency\Controller;

use Admin\Controller\BaseController;

class CurrencyController extends BaseController
{
    public function indexAction()
    {
        $array = array();
        //存入库的货币列表
        $array['currency_list'] = $this->getDbshopTable()->listCurrency();
        //xml中的货币信息
        $array['currency_xml'] = array();
        $xmlReader      = new \Zend\Config\Reader\Xml();
        $currencyArray  = $xmlReader->fromFile(__DIR__ . '/../../../data/' . $this->getDbshopLang()->getLocale() . '.xml');
        if(is_array($currencyArray['currency']) and !empty($currencyArray['currency'])) {
            foreach ($currencyArray['currency'] as $value) {
                $array['currency_xml'][$value['type']] = (is_array($value['displayName']) ? $value['displayName'][0] : $value['displayName']);
            }
        }

        
        return $array;
    }
    /** 
     * 货币添加
     */
    public function addAction ()
    {
        $array = array();
        $xmlReader      = new \Zend\Config\Reader\Xml();
        $array['currency_array']  = $xmlReader->fromFile(__DIR__ . '/../../../data/' . $this->getDbshopLang()->getLocale() . '.xml');
        
        if($this->request->isPost()) {
            $currencyArray = $this->request->getPost()->toArray();
            $currencyArray['currency_code'] = $currencyArray['currency_name'];
            
            $addState      = $this->getDbshopTable()->addCurrency($currencyArray);
            if($addState) {
                $this->setCurrencyIni();
                //记录操作日志
                $currencyName = '';
                foreach ($array['currency_array']['currency'] as $currencyValue) {
                    if($currencyValue['type'] == $currencyArray['currency_name']) {
                        $currencyName = $currencyValue['displayName'];
                        break;
                    }
                }
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('货币设置'), 'operlog_info'=>$this->getDbshopLang()->translate('添加货币') . '&nbsp;' . $currencyName));
                
                return $this->redirect()->toRoute('currency/default',array('controller'=>'currency'));
            }
        }

        return $array;
    }
    /** 
     * 货币编辑
     * @return multitype:Ambigous <multitype:, \Zend\Config\Reader\mixed, string>
     */
    public function editAction ()
    {
        $currencyId = (int) $this->params('currency_id', 0);
        if($currencyId == 0) {
            return $this->redirect()->toRoute('currency/default',array('controller'=>'currency'));
        }
        $array = array();
        //xml货币信息
        $xmlReader = new \Zend\Config\Reader\Xml();
        $array['currency_array'] = $xmlReader->fromFile(__DIR__ . '/../../../data/' . $this->getDbshopLang()->getLocale() . '.xml');
        
        //货币更新操作
        if($this->request->isPost()) {
            $currencyArray = $this->request->getPost()->toArray();
            $currencyArray['currency_code'] = $currencyArray['currency_name'];
            
            $updateState   = $this->getDbshopTable()->updateCurrency($currencyArray, array('currency_id'=>$currencyId));
            if($updateState) {
                $this->setCurrencyIni();
                //记录操作日志
                $currencyName = '';
                foreach ($array['currency_array']['currency'] as $currencyValue) {
                    if($currencyValue['type'] == $currencyArray['currency_name']) {
                        $currencyName = $currencyValue['displayName'];
                        break;
                    }
                }
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('货币设置'), 'operlog_info'=>$this->getDbshopLang()->translate('编辑货币') . '&nbsp;' . $currencyName));
                
                return $this->redirect()->toRoute('currency/default',array('controller'=>'currency'));
            }
        }
        
        //数据表中的货币信息
        $array['currency_info']   = $this->getDbshopTable()->infoCurrency(array('currency_id'=>$currencyId));

        return $array;        
    }
    /** 
     * 删除货币信息
     */
    public function delAction ()
    {
        $currencyId = (int) $this->request->getPost('currency_id');
        if($currencyId == 0 or $currencyId == 1) {
            exit('false');
        }
        //为记录操作日志使用
        $currencyInfo   = $this->getDbshopTable()->infoCurrency(array('currency_id'=>$currencyId));
        
        if($this->getDbshopTable()->delCurrency(array('currency_id'=>$currencyId))) {
            $this->setCurrencyIni();
            //记录操作日志
            $xmlReader      = new \Zend\Config\Reader\Xml();
            $currencyArray  = $xmlReader->fromFile(__DIR__ . '/../../../data/' . $this->getDbshopLang()->getLocale() . '.xml');
            $currencyName = '';
            foreach ($currencyArray['currency'] as $currencyValue) {
                if($currencyValue['type'] == $currencyInfo->currency_name) {
                    $currencyName = $currencyValue['displayName'];
                    break;
                }
            }
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('货币设置'), 'operlog_info'=>$this->getDbshopLang()->translate('删除货币') . '&nbsp;' . $currencyName));
            
            exit('true');
        }
        
        exit('false');
    }
    private function setCurrencyIni ()
    {
        $configWriter  = new \Zend\Config\Writer\Ini();
        $currencyArray = $this->getDbshopTable('CurrencyTable')->listCurrency();
        $iniArray      = array();
        if(is_array($currencyArray)and !empty($currencyArray)) {
            foreach ($currencyArray as $value) {
                $iniArray[$value['currency_code']] = array(
                    'currency_code'     =>$value['currency_code'],
                    'currency_symbol'   =>$value['currency_symbol'],
                    'currency_decimal'  =>$value['currency_decimal'],
                    'currency_unit'     =>$value['currency_unit'],
                    'currency_rate'     =>$value['currency_rate'],
                    'currency_type'     =>$value['currency_type'],
                    'currency_state'    =>$value['currency_state']
                );
            }
        }
        $configWriter->toFile(DBSHOP_PATH . '/data/moduledata/Currency/Currency.ini', $iniArray);
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'CurrencyTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}
