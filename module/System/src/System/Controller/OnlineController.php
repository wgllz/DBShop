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

namespace System\Controller;

use Admin\Controller\BaseController;

class OnlineController extends BaseController
{
    /** 
     * 客服列表
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction ()
    {
        $array = array();
        
        $array['online_list'] = $this->getDbshopTable()->listOnline();

        return $array;
    }
    /** 
     * 客服添加
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:NULL
     */
    public function addAction ()
    {
        $array = array();
        $systemReader = new \Zend\Config\Reader\Ini();
        $array['online_type_array'] = $systemReader->fromFile(DBSHOP_PATH . '/data/moduledata/System/Online.ini');
        
        if($this->request->isPost()) {
            $onlineArray = $this->request->getPost()->toArray();
            $onlineArray['online_web_code'] = str_replace('{online_account}', $onlineArray['online_account'], $array['online_type_array'][$onlineArray['online_type']]['web_code']);
            $onlineArray['online_web_code'] = str_replace('{name}', $onlineArray['online_name'], $onlineArray['online_web_code']);
            
            $this->getDbshopTable()->addOnline($onlineArray);
            //生成前台可使用的文件
            $this->createOnlinePhpFile();
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('客服管理'), 'operlog_info'=>$this->getDbshopLang()->translate('添加客服') . '&nbsp;' . $onlineArray['online_name']));
            
            return $this->redirect()->toRoute('online/default');
        }

        $array['group_list'] = $this->getDbshopTable('OnlineGroupTable')->listOnlineGroup();
        
        return $array;
    }
    /** 
     * 客服编辑
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:NULL
     */
    public function editAction ()
    {
        $onlineId = (int) $this->params('online_id', 0);
        if(!$onlineId) {
            return $this->redirect()->toRoute('online/default');
        }
        
        $array = array();
        $systemReader = new \Zend\Config\Reader\Ini();
        $array['online_type_array'] = $systemReader->fromFile(DBSHOP_PATH . '/data/moduledata/System/Online.ini');
        
        if($this->request->isPost()) {
            $onlineArray = $this->request->getPost()->toArray();
            $onlineArray['online_web_code'] = str_replace('{online_account}', $onlineArray['online_account'], $array['online_type_array'][$onlineArray['online_type']]['web_code']);
            $onlineArray['online_web_code'] = str_replace('{name}', $onlineArray['online_name'], $onlineArray['online_web_code']);

            $this->getDbshopTable()->updateOnline($onlineArray, array('online_id'=>$onlineId));
            //生成前台可使用的文件
            $this->createOnlinePhpFile();
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('客服管理'), 'operlog_info'=>$this->getDbshopLang()->translate('更新客服') . '&nbsp;' . $onlineArray['online_name']));
            
            return $this->redirect()->toRoute('online/default');
        }

        //客服信息
        $array['online_info'] = $this->getDbshopTable()->infoOnline(array('online_id'=>$onlineId));
        //客服分组
        $array['group_list'] = $this->getDbshopTable('OnlineGroupTable')->listOnlineGroup();
        
        return $array;
    }
    /** 
     * 客服删除
     */
    public function delAction ()
    {
        $onlineId = (int) $this->request->getPost('online_id');
        if(!$onlineId) {
            exit('false');
        }
        //为了记录操作日志
        $onlineInfo = $this->getDbshopTable()->infoOnline(array('online_id'=>$onlineId));
        
        if($this->getDbshopTable()->delOnline(array('online_id'=>$onlineId))) {
            //生成前台可使用的文件
            $this->createOnlinePhpFile();
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('客服管理'), 'operlog_info'=>$this->getDbshopLang()->translate('删除客服') . '&nbsp;' . $onlineInfo->online_name));
            
            exit('true');
        }
    }
    /** 
     * 客服组列表
     */
    public function groupAction ()
    {
        $array = array();
        
        $array['group_list'] = $this->getDbshopTable('OnlineGroupTable')->listOnlineGroup();
        
        return $array;
    }
    /**
     * 创建前台客服内容文件，方便直接调用使用
     */
    private function createOnlinePhpFile()
    {
        $onlineArray = $this->getDbshopTable()->listOnline(array('dbshop_online.online_state=1'));
        $array       = array();
        if(is_array($onlineArray) and !empty($onlineArray)) {
            foreach ($onlineArray as $value) {
                if(isset($array[$value['online_group_id']])) $array[$value['online_group_id']] .= '<LI>'.$value['online_web_code'].'</LI>';
                else $array[$value['online_group_id']] = '<LI>'.$value['online_web_code'].'</LI>';
            }
        }
        $indexHtml  = '';
        $classHtml  = '';
        $goodsHtml  = '';
        $groupArray = $this->getDbshopTable('OnlineGroupTable')->listOnlineGroup(array('online_group_state'=>1));
        if(is_array($groupArray) and !empty($groupArray)) {
            foreach($groupArray as $g_value) {
                $html = '';
                $html .= '<LI><SPAN class=icoZx>'.$g_value['online_group_name'].'</SPAN></LI>';
                $html .= $array[$g_value['online_group_id']];
                
                if($g_value['index_show'] == 'true') $indexHtml .= $html; 
                if($g_value['class_show'] == 'true') $classHtml .= $html; 
                if($g_value['goods_show'] == 'true') $goodsHtml .= $html; 
            }
        }

        file_put_contents(DBSHOP_PATH . '/data/moduledata/System/online/index.php', $indexHtml);
        file_put_contents(DBSHOP_PATH . '/data/moduledata/System/online/class.php', $classHtml);
        file_put_contents(DBSHOP_PATH . '/data/moduledata/System/online/goods.php', $goodsHtml);
    }
    /** 
     * 客服组添加
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function groupaddAction ()
    {
        if($this->request->isPost()) {
            $groupArray = $this->request->getPost()->toArray();
            if($this->getDbshopTable('OnlineGroupTable')->addOnlineGroup($groupArray)) {
                //生成前台可使用的文件
                $this->createOnlinePhpFile();
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('客服组管理'), 'operlog_info'=>$this->getDbshopLang()->translate('添加客服组') . '&nbsp;' . $groupArray['online_group_name']));
                
                return $this->redirect()->toRoute('online/default',array('action'=>'group'));
            }
        }        
    }
    /** 
     * 客户组编辑
     */
    public function groupeditAction ()
    {
        $onlineGroupId = (int) $this->params('online_group_id', '0');
        if(!$onlineGroupId) {
            return $this->redirect()->toRoute('online/default',array('action'=>'group'));
        }
        //更新操作
        if($this->request->isPost()) {
            $groupArray = $this->request->getPost()->toArray();
            if($this->getDbshopTable('OnlineGroupTable')->updateOnlineGroup($groupArray, array('online_group_id'=>$onlineGroupId))) {
                //生成前台可使用的文件
                $this->createOnlinePhpFile();
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('客服组管理'), 'operlog_info'=>$this->getDbshopLang()->translate('更新客服组') . '&nbsp;' . $groupArray['online_group_name']));
                
                return $this->redirect()->toRoute('online/default',array('action'=>'group'));
            }
        }
        
        $array = array();
        
        $array['online_group_info'] = $this->getDbshopTable('OnlineGroupTable')->infoOnlineGroup(array('online_group_id'=>$onlineGroupId));
        
        return $array;
    }
    /** 
     * 客户组删除
     */
    public function groupdelAction ()
    {
        $onlineGroupId = (int) $this->request->getPost('online_group_id');
        if(!$onlineGroupId) {
            exit('false');
        }
        if($this->getDbshopTable()->infoOnline(array('online_group_id'=>$onlineGroupId))) {
            exit('online_exists');
        }
        //为了记录操作日志
        $groupInfo = $this->getDbshopTable('OnlineGroupTable')->infoOnlineGroup(array('online_group_id'=>$onlineGroupId));
        
        if($this->getDbshopTable('OnlineGroupTable')->delOnlineGroup(array('online_group_id'=>$onlineGroupId))) {
            //生成前台可使用的文件
            $this->createOnlinePhpFile();
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('客服组管理'), 'operlog_info'=>$this->getDbshopLang()->translate('删除客服组') . '&nbsp;' . $groupInfo->online_group_name));
            
            exit('true');
        }
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName='OnlineTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}

?>