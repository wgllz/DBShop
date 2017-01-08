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

namespace Mobile\Controller;

use Zend\View\Model\ViewModel;

class AddressController extends MobileHomeController
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
        $view->setTemplate('/mobile/home/address.phtml');

        $array['address_list'] = $this->getDbshopTable('UserAddressTable')->listAddress(array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        $array['region_array']= $this->getDbshopTable('RegionTable')->listRegion(array('dbshop_region.region_top_id=0'));

        $view->setVariables($array);
        return $view;
    }

    /**
     * @return array
     */
    public function addAddressAction()
    {
        $view = new ViewModel();
        $view->setTemplate('/mobile/home/add-address.phtml');

        $array = array();

        if($this->request->isPost()) {//添加或者编辑收货地址
            $postAddressId    = (int) $this->request->getPost('address_id');
            $addressArray     = $this->request->getPost()->toArray();
            $addressArray['user_id'] = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
            if($postAddressId == 0) {
                $this->getDbshopTable('UserAddressTable')->addAddress($addressArray);
            } else {
                $this->getDbshopTable('UserAddressTable')->updateAddress($addressArray,array('address_id'=>$postAddressId, 'user_id'=>$addressArray['user_id']));
            }
            return $this->redirect()->toRoute('m_address/default');
        }

        $addressId = (int) $this->params('address_id', 0);

        if($addressId > 0) {//编辑收货地址
            $title_name = $this->getDbshopLang()->translate('编辑收货地址');
            $array['addressInfo'] = $this->getDbshopTable('UserAddressTable')->infoAddress(array('address_id'=>$addressId, 'user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        } else {
            $title_name = $this->getDbshopLang()->translate('添加收货地址');
        }
        //顶部title使用
        $this->layout()->title_name = $title_name;

        $array['region_array']= $this->getDbshopTable('RegionTable')->listRegion(array('dbshop_region.region_top_id=0'));

        $view->setVariables($array);
        return $view;
    }

    /**
     * @return \Zend\Http\Response
     */
    public function deladdressAction()
    {
        $addressId = (int) $this->params('address_id', 0);
        if($addressId > 0) {
            $this->getDbshopTable('UserAddressTable')->delAddress(array('address_id'=>$addressId , 'user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        }
         return $this->redirect()->toRoute('m_address/default');
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