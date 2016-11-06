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

namespace Shopfront\Controller;

use Zend\View\Model\ViewModel;

class HomeaddressController extends FronthomeController
{
    private $dbTables = array();
    private $translator;
    /** 
     * 收货地址列表
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction ()
    {
        $view = new ViewModel();
        
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('收货地址');
        
        $array = array();
        $view->setTemplate('/shopfront/home/address.phtml');

        $array['address_list'] = $this->getDbshopTable('UserAddressTable')->listAddress(array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        $array['region_array']= $this->getDbshopTable('RegionTable')->listRegion(array('dbshop_region.region_top_id=0'));
        
        $view->setVariables($array);
        return $view;
    }
    /** 
     * 编辑收货地址
     */
    public function editAddressAction ()
    {
        $addressId = intval($this->request->getPost('address_id'));
        $addressInfo = $this->getDbshopTable('UserAddressTable')->infoAddress(array('address_id'=>$addressId, 'user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        if(!empty($addressInfo)) {
            $addressInfo['default_value'] = $addressInfo['addr_default'];//从新赋值，html页面js对default在低版本浏览器会报js错误
            echo json_encode($addressInfo);
        }
        exit();
    }
    /** 
     * 收货地址添加或编辑操作
     */
    public function saveaddressAction ()
    {
        $addState = 'false';
        if($this->request->isPost()) {
            $addressId    = (int) $this->request->getPost('address_id');
            $addressArray = $this->request->getPost()->toArray();
            $addressArray['user_id'] = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
            if($addressId == 0) {
                $this->getDbshopTable('UserAddressTable')->addAddress($addressArray);
            } else {
                $this->getDbshopTable('UserAddressTable')->updateAddress($addressArray,array('address_id'=>$addressId, 'user_id'=>$addressArray['user_id']));
            }
            $addState = 'true';
        }
        exit($addState);        
    }
    /**
     * 删除收货地址
     */
    public function deladdressAction ()
    {
        $addressId = (int) $this->request->getPost('address_id');
        $delState  = 'false';
        if($this->getDbshopTable('UserAddressTable')->delAddress(array('address_id'=>$addressId , 'user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')))) {
            $delState = 'true';
        }
        exit($delState);
    }
    /**
     * 给外部资源使用的地区选择（输出使用json）
     */
    public function selectAreaAction ()
    {
        $regionId    = (int) $this->request->getPost('region_id');
        $regionField = trim($this->params('region_type','region_top_id'));
        $regionArray = $this->getDbshopTable('RegionTable')->listRegion(array("dbshop_region.{$regionField}"=>$regionId));
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

?>