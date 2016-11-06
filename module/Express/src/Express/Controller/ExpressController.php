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

namespace Express\Controller;

use Admin\Controller\BaseController;

class ExpressController extends BaseController
{
    public function indexAction ()
    {
        $array = array();
        
        $array['express_array'] = $this->getDbshopTable()->listExpress();

        return $array;
    }
    /**
     * 添加配送公式
     */
    public function addAction ()
    {
        $array = array();

        if ($this->request->isPost()) {
            $expressArray = $this->request->getPost()->toArray();
            $addState = $this->getDbshopTable()->addExpress($expressArray);
            if ($addState) {
                //当配送费用设置为G（个性化地区设置）时，将添加的个性信息更新为当前配送id
                if($expressArray['express_set'] == 'G') $this->getDbshopTable('ExpressIndividuationTable')->updateExpressIndividuation(array('express_id'=>$addState), array('express_id'=>0));
                //将费用设置写入ini文件，前台调用可以不查询数据库
                $this->createExpressIni($expressArray, $addState);
                
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('配送设置'), 'operlog_info'=>$this->getDbshopLang()->translate('添加配送方式') . '&nbsp;' . $expressArray['express_name']));
                
                return $this->redirect()->toRoute('express/default');
            }
        }
        
        //配送地区
        $array['region_array'] = $this->getDbshopTable('RegionTable')->listRegion(array(), 'dbshop_region.region_path ASC');
        //动态物流绑定
        $array['express_get_data'] = $this->getExpressNameData();

        return $array;
    }
    /** 
     * 编辑配送方式
     */
    public function editAction ()
    {
        $expressId     = (int) $this->params('express_id', 0);
        if(!$expressId) {
            return $this->redirect()->toRoute('express/default');
        }
        
        if ($this->request->isPost()) {
            $expressArray = $this->request->getPost()->toArray();
            $updateState  = $this->getDbshopTable()->updateExpress($expressArray, array('express_id'=>$expressId));
            //将费用设置写入ini文件，前台调用可以不查询数据库
            $this->createExpressIni($expressArray, $expressId);
            
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('配送设置'), 'operlog_info'=>$this->getDbshopLang()->translate('更新配送方式') . '&nbsp;' . $expressArray['express_name']));
            
            return $this->redirect()->toRoute('express/default');
        }
        
        $array = array();
        //配送方式
        $array['express_info'] = $this->getDbshopTable()->infoExpress(array('express_id'=>$expressId));
        //配送地区
        $array['region_array'] = $this->getDbshopTable('RegionTable')->listRegion(array(), 'dbshop_region.region_path ASC');
        //个性化信息
        $array['indivi_array'] = $this->getDbshopTable('ExpressIndividuationTable')->listExpressIndividuation(array('express_id=' . $expressId . ' or express_id=0'));
        if(is_array($array['indivi_array']) and !empty($array['indivi_array'])) {
            foreach ($array['indivi_array'] as $key => $value) {
                $areaIdArray = array();
                if(!empty($value['express_area'])) $areaIdArray = unserialize($value['express_area']);
                if(!empty($areaIdArray)) {
                    $array['indivi_array'][$key]['area'] = $this->getDbshopTable('RegionTable')->listRegion(array('dbshop_region.region_id IN (' . implode(',', $areaIdArray) . ')'));
                }
                unset($areaIdArray);
            }
        }
        //动态物流绑定
        $array['express_get_data'] = $this->getExpressNameData();
        
        return $array;
    }
    /**
     * 删除配送方式
     */
    public function delAction ()
    {
        $expressId = intval($this->request->getPost('express_id'));
        if($expressId) {
            //为记录操作日志使用
            $expressInfo = $this->getDbshopTable()->infoExpress(array('express_id'=>$expressId));
            //判断是否有快递单号记录，如果有，配送方式不能删除
            if($this->getDbshopTable('ExpressNumberTable')->arrayExpressNumber(array('express_id'=>$expressId))) exit('false');

            if ($this->getDbshopTable()->delExpress(array('express_id'=>$expressId))) {
                //删除配送费用配置文件
                @unlink(DBSHOP_PATH . '/data/moduledata/Express/' . $expressId . '.ini');
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('配送设置'), 'operlog_info'=>$this->getDbshopLang()->translate('删除配送方式') . '&nbsp;' . $expressInfo->express_name));
                
                exit('true');
            }
        }
        echo 'false';
        exit;
    }
    /**
     * 检查配送费用公式
     */
    public function checkexpressAction ()
    {
        $postArray = $this->request->getPost()->toArray();
        $testPrice = $this->getServiceLocator()->get('shop_express')->calculateCost(trim($postArray['express_price']), trim($postArray['test_weight']), trim($postArray['test_total']));
        echo $testPrice;
        exit();
    }
    /** 
     * 个性化地区添加
     */
    public function addgexpressareaAction ()
    {
        $postArray = $this->request->getPost()->toArray();
        //判断是否已经选择了地区
        if(!isset($postArray['express_area']) or empty($postArray['express_area'])) {
            echo json_encode(array('state'=>'area_null'));
            exit;
        }
        $indivId                   = intval($this->request->getPost('indiv_id'));
        $areaIdStr                 = implode(',', $postArray['express_area']);
        $postArray['express_area'] = serialize($postArray['express_area']);
        $editType                  = 'false';
        
        if($indivId == 0) {
            $indivId = $this->getDbshopTable('ExpressIndividuationTable')->addExpressIndividuation($postArray);
        } else {
            $editType = 'true';
            $this->getDbshopTable('ExpressIndividuationTable')->updateExpressIndividuation($postArray, array('indiv_id'=>$indivId));
        }
        
        //如果是编辑配送方式操作，在个性化地区设置时，更新相关配置文件
        if($postArray['express_id'] != '' and $postArray['express_set'] == 'G') {
            $this->createExpressIni($postArray, $postArray['express_id']);
        }
        
        $areaArray    = $this->getDbshopTable('RegionTable')->listRegion(array('dbshop_region.region_id IN (' . $areaIdStr . ')'));
        
        $areaStr      = '';
        if(is_array($areaArray) and !empty($areaArray)) {
            foreach ($areaArray as $value) {
                $areaStr .= $value['region_name'] . ',';
            }
            $areaStr = substr($areaStr, 0, -1);
        }
        echo json_encode(array('state'=>'true', 'edit_type'=>$editType, 'indiv_id'=>$indivId, 'express_price'=>$postArray['express_price'], 'express_area'=>$areaStr));
        exit;
    }
    /** 
     * 个性化地区信息
     */
    public function infogexpressareaAction ()
    {
        $indivId = intval($this->request->getPost('indiv_id'));
        if($indivId == 0) {
            exit;
        }
        $indivInfo = $this->getDbshopTable('ExpressIndividuationTable')->infoExpressIndividuation(array('indiv_id'=>$indivId));
        if($indivInfo) {
            echo json_encode(array('state'=>'true', 'indiv_id'=>$indivId, 'express_price'=>$indivInfo->express_price, 'area_id'=>unserialize($indivInfo->express_area)));
        }
        exit;
    }
    /** 
     * 个性化地区删除
     */
    public function delgexpressareaAction ()
    {
        $indivId = intval($this->request->getPost('indiv_id'));
        if($indivId == 0) {
            echo json_encode(array('state'=>'false'));
            exit;
        }
        $delState = $this->getDbshopTable('ExpressIndividuationTable')->delExpressIndividuation(array('indiv_id'=>$indivId));
        
        //如果是编辑配送方式操作，在个性化地区设置时，更新相关配置文件
        $postArray = $this->request->getPost()->toArray();
        if($postArray['express_id'] != '' and $postArray['express_set'] == 'G') {
            $this->createExpressIni($postArray, $postArray['express_id']);
        }
        
        echo json_encode(array('state'=>'true'));
        exit;
    }
    /** 
     * 物流动态API设置列表
     * @return multitype:
     */
    public function expressapiAction()
    {
        $array = array();
        
        $expressArray = array();
        $xmlReader    = new \Zend\Config\Reader\Xml();
        $xmlPath      = DBSHOP_PATH . '/data/moduledata/Express/api/';
        if(is_dir($xmlPath)) {
            $dh = opendir($xmlPath);
            while (false !== ($fileDir = readdir($dh))) {
                if($fileDir != '.' and $fileDir != '..' and $fileDir != '.DS_Store') {
                    $expressArray[] = $xmlReader->fromFile($xmlPath . '/' . $fileDir . '/express.xml');
                }
            }
        }
        $array['express_api'] = $expressArray;
        
        //获取开启的api信息
        $apiOpenContent = array();
        if(file_exists($xmlPath . '../express.xml')) {
            $apiOpenContent = $xmlReader->fromFile($xmlPath . '../express.xml');
        }
        $array['open_api'] = $apiOpenContent;
        
        return $array;
    }
    /** 
     * 编辑物流动态API
     * @return multitype:
     */
    public function apieditAction()
    {
        $array = array();
        
        $xmlReader    = new \Zend\Config\Reader\Xml();
        $expressApiName = $this->params('express_code');
        $xmlPath        = DBSHOP_PATH . '/data/moduledata/Express/api/';
        //读取指定物流动态api信息
        if(!file_exists($xmlPath . $expressApiName . '/express.xml')) {
            return $this->redirect()->toRoute('express/default', array('action'=>'expressapi'));
        }
        $array['api_info'] = $xmlReader->fromFile($xmlPath . $expressApiName . '/express.xml');

        //获取开启的api信息
        $apiOpenContent = array();
        if(file_exists($xmlPath . '../express.xml')) {
            $apiOpenContent = $xmlReader->fromFile($xmlPath . '../express.xml');
        }
        $array['open_api'] = $apiOpenContent;
        
        if ($this->request->isPost()) {
            $xmlWriter = new \Zend\Config\Writer\Xml();
            $apiArray  = $this->request->getPost()->toArray();
            $array['api_info']['api_code'] = $apiArray['api_key'];
            $xmlWriter->toFile($xmlPath . $expressApiName . '/express.xml', $array['api_info']);
            //如果是开启状态
            if($apiArray['api_state'] == 1) {
                $xmlWriter->toFile($xmlPath . '../express.xml', $array['api_info']);
            } else {
                if(isset($apiOpenContent['name_code']) and $expressApiName == $apiOpenContent['name_code']) {
                    $xmlWriter->toFile($xmlPath . '../express.xml', array());
                }
            }
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('配送动态API'), 'operlog_info'=>$this->getDbshopLang()->translate('编辑配送动态API') . '&nbsp;' . $array['api_info']['name']));
            
            return $this->redirect()->toRoute('express/default', array('action'=>'expressapi'));
        }

        return $array;
    }
    /** 
     * 对配送费用进行文本化设置
     * @param array $data
     */
    private function createExpressIni (array $data, $expressId)
    {
        $array   = array();
        $iniFile = DBSHOP_PATH . '/data/moduledata/Express/' . $expressId . '.ini';
        $array['express_set']       = trim($data['express_set']);
        $array['express_name_code'] = trim($data['express_name_code']);
        $array['express_url']       = trim($data['express_url']);
        $array['cash_on_delivery']  = trim($data['cash_on_delivery']);
        if($data['express_set'] == 'G') {
            $areaArray = $this->getDbshopTable('ExpressIndividuationTable')->listExpressIndividuation(array('express_id=' . $expressId));
            if(is_array($areaArray) and !empty($areaArray)) {
                foreach ($areaArray as $value) {
                    $array['express_price'][] = array('price'=>$value['express_price'], 'area_id'=>unserialize($value['express_area']));
                }
            }
        } else {
            $array['express_price'] = $data['express_price'];
        }
        
        $configWriter = new \Zend\Config\Writer\Ini();
        $configWriter->toFile($iniFile, $array);
    }
    /** 
     * 获取动态显示物流状态的物流信息xml文件
     * @return Ambigous <multitype:, \Zend\Config\Reader\mixed, string>
     */
    private function getExpressNameData()
    {
        $arrayContent = array();
        $xmlReader    = new \Zend\Config\Reader\Xml();
        if(file_exists(DBSHOP_PATH . '/data/moduledata/Express/express.xml')) {
            $apiInfo = $xmlReader->fromFile(DBSHOP_PATH . '/data/moduledata/Express/express.xml');
            if(!empty($apiInfo) and file_exists(DBSHOP_PATH . '/data/moduledata/Express/api/'.$apiInfo['name_code'].'/data.xml')) {
                $arrayContent = $xmlReader->fromFile(DBSHOP_PATH . '/data/moduledata/Express/api/'.$apiInfo['name_code'].'/data.xml');
            }
        }
  
        return $arrayContent;
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'ExpressTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}
