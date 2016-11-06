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

namespace Stock\Controller;

use Admin\Controller\BaseController;

class StateController extends BaseController
{
    /** 
     * 库存状态列表
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction ()
    {
        $array = array();
        
        $array['state_array'] = $this->getDbshopTable()->listStockState();
        
        return $array;
    }
    /** 
     * 库存状态添加
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function addAction ()
    {
        if ($this->request->isPost()) {
            $stockStateArray = $this->request->getPost()->toArray();
            $stockStateId    = $this->getDbshopTable()->addStockState($stockStateArray);
            if($stockStateId) {
                $stockStateArray['stock_state_id'] = $stockStateId;
                $stockStateArray['language']       = $this->getDbshopLang()->getLocale();
                $this->getDbshopTable('StockStateExtendTable')->addStockStateExtend($stockStateArray);
                
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('库存状态'), 'operlog_info'=>$this->getDbshopLang()->translate('添加库存状态') . '&nbsp;' . $stockStateArray['stock_state_name']));
            }
            unset($stockStateArray);
            
            return $this->redirect()->toRoute('stock_state/default');
        }
    }
    /** 
     * 库存状态编辑
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:NULL
     */
    public function editAction ()
    {
        $stockStateId = (int) $this->params('stock_state_id', 0);
        if($stockStateId == 0) {
            return $this->redirect()->toRoute('stock_state/default');
        }
        if ($this->request->isPost()) {
            $stockStateArray = $this->request->getPost()->toArray();
            $updateState     = $this->getDbshopTable()->updateStockState($stockStateArray, array('stock_state_id'=>$stockStateId));
            if($updateState) {
                $stockStateArray['language']       = $this->getDbshopLang()->getLocale();
                $this->getDbshopTable('StockStateExtendTable')->updateStockStateExtend($stockStateArray, array('stock_state_id'=>$stockStateId));
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('库存状态'), 'operlog_info'=>$this->getDbshopLang()->translate('更新库存状态') . '&nbsp;' . $stockStateArray['stock_state_name']));
                
                unset($stockStateArray);
                return $this->redirect()->toRoute('stock_state/default');
            }
        }
        
        $array = array();
        
        $array['stock_state_info'] = $this->getDbshopTable()->infoStockState(array('e.stock_state_id'=>$stockStateId, 'e.language'=>$this->getDbshopLang()->getLocale()));
       return $array;
    }
    /** 
     * 库存状态删除
     */
    public function delAction ()
    {
        $stockStateId = (int) $this->request->getPost('stock_state_id');
        if($stockStateId != 0) {
            //检查商品中是否已经使用
            $goodsInfo= $this->getDbshopTable('GoodsTable')->infoGoods(array('dbshop_goods.goods_stock_state'=>$stockStateId, 'e.language'=>$this->getDbshopLang()->getLocale()));
            if($goodsInfo) exit('goods_exists');
            //为了记录操作日志
            $stockStateInfo = $this->getDbshopTable()->infoStockState(array('e.stock_state_id'=>$stockStateId, 'e.language'=>$this->getDbshopLang()->getLocale()));
            
            $delState = $this->getDbshopTable()->delStockState(array('stock_state_id'=>$stockStateId));
            
            if($delState == 'true') {
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('库存状态'), 'operlog_info'=>$this->getDbshopLang()->translate('删除库存状态') . '&nbsp;' . $stockStateInfo->stock_state_name));
            }
            
            exit($delState);
        }
        exit('false');
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'StockStateTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}
