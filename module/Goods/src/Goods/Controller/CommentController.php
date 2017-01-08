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

namespace Goods\Controller;

use Zend\View\Model\ViewModel;
use Admin\Controller\BaseController;

class CommentController extends BaseController
{
    /**
     * 商品评价列表
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction ()
    {
        $array = array();
        //评价分页
        $page = $this->params('page',1);
        $array['comment_list'] = $this->getDbshopTable('GoodsCommentBaseTable')->listGoodsCommentBase(array('page'=>$page, 'page_num'=>20), array('e.language'=>$this->getDbshopLang()->getLocale()));
        
        return $array;
    }
    /**
     * 商品评价查看编辑
     */
    public function editAction ()
    {
        $goodsId = (int) $this->params('goods_id',0);
        if(!$goodsId) {
            return $this->redirect()->toRoute('comment/default',array('controller'=>'comment'));
        }
        $array = array();
        $array['goods_info'] = $this->getDbshopTable('GoodsExtendTable')->infoGoodsExtend(array('goods_id'=>$goodsId,'language'=>$this->getDbshopLang()->getLocale()));
        
        //商品评价分页
        $page = $this->params('page',1);
        $array['page']     = $page;
        $array['goods_comment_list'] = $this->getDbshopTable()->listGoodsComment(array('page'=>$page, 'page_num'=>20), array('goods_id'=>$goodsId));
        
        return $array;
    }
    /**
     * 商品评价删除
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function delAction ()
    {
        $goodsId = (int) $this->params('goods_id',0);
        if(!$goodsId) {
            return $this->redirect()->toRoute('comment/default',array('controller'=>'comment'));
        }
        $this->getDbshopTable('GoodsCommentBaseTable')->delGoodsCommentBase(array('goods_id'=>$goodsId));
        $this->getDbshopTable()->delGoodsComment(array('goods_id'=>$goodsId));
        //商品评价置0
        $this->getDbshopTable('OrderGoodsTable')->updateOrderGoods(array('comment_state'=>'0'),array('goods_id'=>$goodsId));
        
        return $this->redirect()->toRoute('comment/default',array('controller'=>'comment'));
    }
    /**
     * 回复商品咨询
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function replycontentAction()
    {
        $page = (int)$this->request->getPost('comment_page');
        $page = ($page <=0 ? 1 : $page);

        if($this->request->isPost()) {
            $replyArray = $this->request->getPost()->toArray();
            $rArray		= array();
            $rArray['reply_body'] = $replyArray['reply_comment_content'];
            $rArray['comment_show_state']= $replyArray['comment_show_state'];
            $rArray['reply_writer']  = $this->getServiceLocator()->get('adminHelper')->returnAuth('admin_name');
            $rArray['reply_time']    = ($rArray['reply_body'] == '' ? '' : time());

            $this->getDbshopTable()->updateGoodsComment($rArray, array('comment_id'=>$replyArray['comment_id']));

            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('商品评价'), 'operlog_info'=>$this->getDbshopLang()->translate('商品评价回复') . '&nbsp;' . $replyArray['goods_name'] . ' : ' . $replyArray['reply_comment_content']));
        }

        return $this->redirect()->toRoute('comment/default/goods-id', array('action'=>'edit','controller'=>'comment','goods_id'=>$replyArray['goods_id'], 'page'=>$page));
    }
    /**
     * ajax调用评价列表，供编辑商品页面使用
     * @return string|multitype:\Zend\Paginator\Paginator
     */
    public function ajaxcommentAction ()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        
        $array   = array();
        $goodsId = (int) $this->params('goods_id',0);
        $userName= trim($this->params('user_name'));
        if(!$goodsId and empty($userName)) {
            return '';
        }
        $whereArray           = array();
        if($goodsId != 0) {
            $whereArray['goods_id'] = $goodsId;
            $array['goods_id']      = $goodsId;
        }
        if($userName != '') {
            $viewModel->setTemplate('/goods/comment/userajaxcomment.phtml');
            $whereArray['comment_writer'] = $userName;
            $array['comment_writer']      = $userName;
        }
        
        $array['show_div_id'] = $this->request->getQuery('show_div_id');
        //评价分页
        $page = $this->params('page',1);
        $array['comment_list'] = $this->getDbshopTable()->listGoodsComment(array('page'=>$page, 'page_num'=>20), $whereArray);
        
        return $viewModel->setVariables($array);
    }
    /**
     * 单个商品评价删除
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function delonecommentAction ()
    {
        $commentId = (int) $this->params('comment_id',0);
        $goodsId   = (int) $this->params('goods_id',0);
        //判断单个评价id或者商品id是否存在
        if(!$commentId or !$goodsId) {
            return $this->redirect()->toRoute('comment/default',array('controller'=>'comment'));
        }
        //获取商品评价信息
        $commentInfo = $this->getDbshopTable()->infoGoodsComment(array('comment_id'=>$commentId));
        //删除单个商品评价
        $delState    = $this->getDbshopTable()->delGoodsComment(array('comment_id'=>$commentId));
        if($delState) {
            //商品评价置0
            $this->getDbshopTable('OrderGoodsTable')->updateOrderGoods(array('comment_state'=>'0'),array('order_goods_id'=>$commentInfo->order_goods_id));
            //更新订单表中的序列化商品评价状态
            $orderGoods = $this->getDbshopTable('OrderGoodsTable')->InfoOrderGoods(array('order_goods_id'=>$commentInfo->order_goods_id));
            $orderInfo  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_id'=>$orderGoods->order_id));
            $orderGoodsSerArray = unserialize($orderInfo->goods_serialize);
            unset($orderGoodsSerArray[$commentInfo->order_goods_id]['comment_state']);
            $this->getDbshopTable('OrderTable')->updateOrder(array('goods_serialize'=>serialize($orderGoodsSerArray)), array('order_id'=>$orderGoods->order_id));
            
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('商品评价'), 'operlog_info'=>$this->getDbshopLang()->translate('删除商品评价') . '&nbsp;' . $orderGoods->goods_name . ' : ' . $commentInfo->comment_body));
            
            //判断评价是否还有，如果没有将评价基础表中的数据删除
            $commentArray = $this->getDbshopTable()->allGoodsComment(array('goods_id'=>$goodsId));
            if(count($commentArray)>0) {//当商品下还有评价时，跳转到单个商品评价列表页面
                $this->getDbshopTable('GoodsCommentBaseTable')->updataGoodsCommentBase(array('comment_count'=>count($commentArray)), array('goods_id'=>$goodsId));
            
                return $this->redirect()->toRoute('comment/default/goods-id',array('controller'=>'comment','action'=>'edit','goods_id'=>$goodsId));
            } else {//否则，删除商品评价基础表，并返回主评价列表
                $this->getDbshopTable('GoodsCommentBaseTable')->delGoodsCommentBase(array('goods_id'=>$goodsId));
                return $this->redirect()->toRoute('comment/default',array('controller'=>'comment'));
            }
        }
        return $this->redirect()->toRoute('comment/default',array('controller'=>'comment'));
    }
    /**
     * ajax单个商品评价删除
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function ajaxdelonecommentAction ()
    {
        $commentId = (int) $this->request->getPost('comment_id');
        $goodsId   = (int) $this->request->getPost('goods_id');
        //判断单个评价id或者商品id是否存在
        if(!$commentId or !$goodsId) {
            exit('');
        }
        //获取商品评价信息
        $commentInfo = $this->getDbshopTable()->infoGoodsComment(array('comment_id'=>$commentId));
        //删除单个商品评价
        $delState  = $this->getDbshopTable()->delGoodsComment(array('comment_id'=>$commentId));
        if($delState) {
            //判断评价是否还有，如果没有将评价基础表中的数据删除
            $commentArray = $this->getDbshopTable()->allGoodsComment(array('goods_id'=>$goodsId));
            if(count($commentArray)>0) {//当商品下还有评价时，跳转到单个商品评价列表页面
                $this->getDbshopTable('GoodsCommentBaseTable')->updataGoodsCommentBase(array('comment_count'=>count($commentArray)), array('goods_id'=>$goodsId));
            } else {//否则，删除商品评价基础表，并返回主评价列表
                $this->getDbshopTable('GoodsCommentBaseTable')->delGoodsCommentBase(array('goods_id'=>$goodsId));
            }
            
            //商品评价置0
            $this->getDbshopTable('OrderGoodsTable')->updateOrderGoods(array('comment_state'=>'0'),array('order_goods_id'=>$commentInfo->order_goods_id));
            //更新订单表中的序列化商品评价状态
            $orderGoods = $this->getDbshopTable('OrderGoodsTable')->InfoOrderGoods(array('order_goods_id'=>$commentInfo->order_goods_id));
            $orderInfo  = $this->getDbshopTable('OrderTable')->infoOrder(array('order_id'=>$orderGoods->order_id));
            $orderGoodsSerArray = unserialize($orderInfo->goods_serialize);
            unset($orderGoodsSerArray[$commentInfo->order_goods_id]['comment_state']);
            $this->getDbshopTable('OrderTable')->updateOrder(array('goods_serialize'=>serialize($orderGoodsSerArray)), array('order_id'=>$orderGoods->order_id));
        
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('商品评价'), 'operlog_info'=>$this->getDbshopLang()->translate('删除商品评价') . '&nbsp;' . $orderGoods->goods_name . ' : ' . $commentInfo->comment_body));
        }
        exit('true');
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'GoodsCommentTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}