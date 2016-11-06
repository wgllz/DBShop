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

namespace Region\Controller;

use Admin\Controller\BaseController;

class RegionController extends BaseController
{
    /**
     * 地区列表
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction ()
    {   
        $array = array();
        
        $regionTopId           = (int) $this->params('region_top_id',0);
        $array['region_array'] = $this->getDbshopTable()->listRegion(array('dbshop_region.region_top_id'=>$regionTopId,'e.language'=>$this->getDbshopLang()->getLocale()));
        $array['region_info']  = $this->getDbshopTable()->infoRegion(array('dbshop_region.region_id'=>$regionTopId,'e.language'=>$this->getDbshopLang()->getLocale()));
        $array['region_top_id']= $regionTopId;
        return $array;
    }
    /**
     * 地区添加
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function addAction ()
    {
        if($this->request->isPost()) {
            $regionArray = $this->request->getPost()->toArray();
            $regionName  = explode("\r\n", $regionArray['region_name']);
            $regionArray['region_path'] = $regionArray['region_top_id'];
            $regionArray['language']    = $this->getDbshopLang()->getLocale();
            if(is_array($regionName) and !empty($regionName)) {
                foreach ($regionName as $value) {
                    if(!empty($value)) {
                        $regionArray['region_name'] = $value;
                        $regionId    = $this->getDbshopTable()->addRegion($regionArray);
                        if($regionId) {
                            $regionArray['region_id'] = $regionId;
                            $this->getDbshopTable('RegionExtendTable')->addRegionExtend($regionArray);
                            unset($regionArray['region_id']);
                        }
                    }
                }
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('地区设置'), 'operlog_info'=>$this->getDbshopLang()->translate('添加地区') . '&nbsp;' . @implode(',', $regionName)));
            }
        }
        return $this->redirect()->toRoute('region/default/region_top_id',array('action'=>'index', 'region_top_id'=>intval($regionArray['region_top_id'])));
    }
    /**
     * 编辑地区
     */
    public function editAction ()
    {
        $regionId = (int) $this->request->getPost('region_id');
        if($regionId and $this->request->isPost()) {
            $regionArray = $this->request->getPost()->toArray();
            $this->getDbshopTable()->updateRegion($regionArray,array('region_id'=>$regionId));
            $this->getDbshopTable('RegionExtendTable')->updateRegionExtend($regionArray,array('region_id'=>$regionId,'language'=>$this->getDbshopLang()->getLocale()));
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('地区设置'), 'operlog_info'=>$this->getDbshopLang()->translate('更新地区') . '&nbsp;' . $regionArray['region_name']));
        }
        exit('true');
    }
    /**
     * 批量编辑
     * @return \Zend\Http\Response
     */
    public function editallAction()
    {
        $regionTopId    = (int) $this->params('region_top_id',0);
        $regionId       = $this->request->getPost('region_id');
        if(is_array($regionId) and !empty($regionId)) {
            $regionNameArray = array();
            foreach($regionId as $value) {
                $regionInfo = $this->getDbshopTable()->infoRegion(array('dbshop_region.region_top_id'=>$value));
                if(is_array($regionInfo) and !empty($regionInfo)) {
                    $regionInfo = $this->getDbshopTable()->infoRegion(array('dbshop_region.region_id'=>$value));
                    $regionNameArray[] = $regionInfo['region_name'];
                } else {
                    $this->getDbshopTable()->delRegion(array('region_id'=>$value));
                }
            }
            //if(!empty($regionNameArray)) echo '<script>alert("'.implode(',', $regionNameArray).' '.$this->getDbshopLang()->translate('地区删除失败，该地区还有下级地区存在！').'");</script>';
        }
        return $this->redirect()->toRoute('region/default/region_top_id',array('action'=>'index', 'region_top_id'=>$regionTopId));
    }
    /**
     * 删除地区
     */
    public function delAction ()
    {
        $regionId = (int) $this->request->getPost('region_id');
        if($regionId) {
            $regionInfo = $this->getDbshopTable()->infoRegion(array('dbshop_region.region_top_id'=>$regionId));
            if(is_array($regionInfo) and !empty($regionInfo)) {
                exit('false');
            }
            //为记录操作日志使用
            $regionInfo = $this->getDbshopTable()->infoRegion(array('dbshop_region.region_id'=>$regionId));
            
            if($this->getDbshopTable()->delRegion(array('region_id'=>$regionId))) {
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('地区设置'), 'operlog_info'=>$this->getDbshopLang()->translate('删除地区') . '&nbsp;' . $regionInfo['region_name']));
                exit('true');
            }
        }
        exit('false');
    }
    /**
     * 地区获取
     */
    public function jsonregionAction ()
    {
        $regionId  = (int) $this->request->getPost('region_id');
        if(!$regionId) {
            exit('');
        }
        $regionInfo= $this->getDbshopTable()->infoRegion(array('dbshop_region.region_id'=>$regionId,'e.language'=>$this->getDbshopLang()->getLocale()));
        if(is_array($regionInfo) and !empty($regionInfo)) {
            echo json_encode($regionInfo);
        }
        exit();
    }
    /**
     * 给外部资源使用的地区选择（输出使用json）
     */
    public function selectregionAction ()
    {
        $regionId    = (int) $this->request->getPost('region_id');
        $regionField = trim($this->params('region_type','region_top_id'));
        $regionArray = $this->getDbshopTable()->listRegion(array("dbshop_region.{$regionField}"=>$regionId));
        if(is_array($regionArray) and !empty($regionArray)) {
            echo json_encode($regionArray);
        } else {
            echo json_encode(array());
        }
        exit();
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'RegionTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}
