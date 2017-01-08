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

use Admin\Controller\BaseController;

class AskController extends BaseController
{
    /** 
     * 咨询首页
     * !CodeTemplates.overridecomment.nonjd!
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $array = array();
        
        //咨询分页
        $page = $this->params('page',1);
        $array['page']     = $page;
        $array['ask_list'] = $this->getDbshopTable()->listGoodsAsk(array('page'=>$page, 'page_num'=>20), array('e.language'=>$this->getDbshopLang()->getLocale()));

        return $array;
    }
    /** 
     * 删除商品咨询
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function delAction()
    {
    	$askId = (int) $this->params('askid',0);
    	if($askId == 0) return $this->redirect()->toRoute('ask/default');
    	
    	$askInfo = $this->getDbshopTable()->infoGoodsAsk(array('ask_id'=>$askId));
    	$delState= $this->getDbshopTable()->delGoodsAsk(array('ask_id'=>$askId));
    	if($delState) {
    		$goodsInfo = $this->getDbshopTable('GoodsExtendTable')->infoGoodsExtend(array('goods_id'=>$askInfo->goods_id));
    		
    		//记录操作日志
    		$this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('商品咨询'), 'operlog_info'=>$this->getDbshopLang()->translate('删除商品咨询') . '&nbsp;' . $goodsInfo->goods_name . ' : ' . $askInfo->ask_content));
    	}
    	return $this->redirect()->toRoute('ask/default');
    }
    /** 
     * 回复商品咨询
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function replycontentAction()
    {
    	$page = (int)$this->request->getPost('ask_page');
    	$page = ($page <=0 ? 1 : $page);

    	if($this->request->isPost()) {
    		$replyArray = $this->request->getPost()->toArray();
    		$rArray		= array();
    		$rArray['reply_ask_content'] = $replyArray['reply_ask_content'];
    		$rArray['ask_show_state']    = $replyArray['ask_show_state'];
    		$rArray['reply_ask_writer']  = $this->getServiceLocator()->get('adminHelper')->returnAuth('admin_name');
    		$rArray['reply_ask_time']    = ($rArray['reply_ask_content'] == '' ? '' : time());

            //这里获取为了下面的邮件提醒而用
            $askInfo = $this->getDbshopTable()->infoGoodsAsk(array('ask_id'=>$replyArray['ask_id']));

    		$this->getDbshopTable()->updateGoodsAsk($rArray, array('ask_id'=>$replyArray['ask_id']));

            /*===========================商品咨询后台回复提醒邮件发送===========================*/
            $askUserInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_name'=>$replyArray['ask_writer']));

            if(md5($askInfo->ask_writer) == md5('游客')) $buyerEmail = '';
            else $buyerEmail = $this->getServiceLocator()->get('frontHelper')->getSendMessageBuyerEmail('goods_ask_reply_state', $askUserInfo->user_email);

            $adminEmail = $this->getServiceLocator()->get('frontHelper')->getSendMessageAdminEmail('goods_ask_reply_state');
            if($rArray['ask_show_state'] ==1 and ($buyerEmail != '' or $adminEmail != '') and ($rArray['reply_ask_content'] != '' and $rArray['reply_ask_content'] != $askInfo->reply_ask_content)) {
                $sendMessageBody = $this->getServiceLocator()->get('frontHelper')->getSendMessageBody('goods_ask_reply');
                if($sendMessageBody != '') {
                    $sendArray = array();
                    $sendArray['shopname']     = $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name');
                    $sendArray['shopurl']      = $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost() . $this->url()->fromRoute('shopfront/default');
                    $sendArray['askusername']  = $replyArray['ask_writer'];
                    $sendArray['replyusername']= $rArray['reply_ask_writer'];
                    $sendArray['replytime']    = $rArray['reply_ask_time'];

                    $goodsInfo   = $this->getDbshopTable('GoodsTable')->infoGoods(array('dbshop_goods.goods_id'=>$askInfo->goods_id, 'e.language'=>$this->getDbshopLang()->getLocale()));
                    $inClass     = $this->getDbshopTable('GoodsInClassTable')->oneGoodsInClass(array('dbshop_goods_in_class.goods_id'=>$askInfo->goods_id, 'c.class_state'=>1));
                    $sendArray['goodsname']  = '<a href="'. $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost() . $this->url()->fromRoute('frontgoods/default', array('goods_id'=>$askInfo->goods_id, 'class_id'=>$inClass[0]['class_id'])).'" target="_blank">' . $goodsInfo->goods_name . '</a>';

                    $sendArray['subject']       = $sendArray['shopname'] . '|' . $this->getDbshopLang()->translate('商品咨询回复') . '|' . $goodsInfo->goods_name;
                    $sendArray['send_mail'][]   = $buyerEmail;
                    $sendArray['send_mail'][]   = $adminEmail;
                    $sendMessageBody            = $this->getServiceLocator()->get('frontHelper')->createSendMessageContent($sendArray, $sendMessageBody);
                    try {
                        $sendState = $this->getServiceLocator()->get('shop_send_mail')->SendMesssageMail($sendArray, $sendMessageBody);
                        $sendState = ($sendState ? 1 : 2);
                    } catch (\Exception $e) {
                        $sendState = 2;
                    }
                    //记录给用户发的电邮
                    if($sendArray['send_mail'][0] != '') {
                        $sendLog = array(
                            'mail_subject' => $sendArray['subject'],
                            'mail_body'    => $sendMessageBody,
                            'send_time'    => time(),
                            'user_id'      => $askUserInfo->user_id,
                            'send_state'   => $sendState
                        );
                        $this->getDbshopTable('UserMailLogTable')->addUserMailLog($sendLog);
                    }
                }
            }
            /*===========================商品咨询后台回复提醒邮件发送===========================*/

    		//记录操作日志
    		$this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('商品咨询'), 'operlog_info'=>$this->getDbshopLang()->translate('商品咨询回复') . '&nbsp;' . $replyArray['goods_name'] . ' : ' . $replyArray['reply_ask_content']));
    	}
    	
    	return $this->redirect()->toRoute('ask/default/ask-page', array('action'=>'index', 'page'=>$page));
    }
    /** 
     * 修改商品咨询前台显示状态
     */
    public function changeShowStateAction()
    {
    	$updateState = '';
    	$askId	   = (int)$this->request->getPost('ask_id');
    	if($askId != 0) {
    		$askInfo = $this->getDbshopTable()->infoGoodsAsk(array('ask_id'=>$askId));
    		$showState = ($askInfo->ask_show_state == 1 ? 2 : 1);
    		$this->getDbshopTable()->updateGoodsAsk(array('ask_show_state'=>$showState), array('ask_id'=>$askId));
    		$updateState = $showState;
    	}
    	echo $updateState;
    	exit();
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'GoodsAskTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}