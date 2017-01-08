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

namespace Navigation\Controller;

use Admin\Controller\BaseController;
use Admin\Service\DbshopOpcache;

class NavigationController extends BaseController
{
    /** 
     * 导航列表
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $array = array();
        
        $array['navigation_list'] = $this->getDbshopTable()->listNavigation(array('e.language'=>$this->getDbshopLang()->getLocale()));
        
        return $array;
    }
    /** 
     * 添加导航信息
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function addAction()
    {
        if($this->request->isPost()) {
            $navigationArray = $this->request->getPost()->toArray();
            $navigationId    = $this->getDbshopTable()->addNavigation($navigationArray);
            if($navigationId) {
                $navigationArray['navigation_id'] = $navigationId;
                $navigationArray['language']      = $this->getDbshopLang()->getLocale();
                $this->getDbshopTable('NavigationExtendTable')->addNavigationExtend($navigationArray);
                //导航数组保存
                $this->writerPhpArray();
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('导航设置'), 'operlog_info'=>$this->getDbshopLang()->translate('添加导航') . '&nbsp;' . $navigationArray['navigation_title']));
            }
            unset($navigationArray);
            //跳转处理
            return $this->redirect()->toRoute('navigation/default');
        }

        $array = array();
        //商品分类
        $array['goods_class']  = $this->getDbshopTable('GoodsClassTable')->classOptions(0,$this->getDbshopTable('GoodsClassTable')->listGoodsClass());

        return $array;
    }
    /** 
     * 编辑导航信息
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:NULL
     */
    public function editAction()
    {
        $navigationId = (int) $this->params('navigation_id', 0);
        if($navigationId == 0) return $this->redirect()->toRoute('navigation/default');
        if($this->request->isPost()) {
            $navigationArray = $this->request->getPost()->toArray();
            $update          = $this->getDbshopTable()->updateNavigation($navigationArray, array('navigation_id'=>$navigationId));
            if($update) {
                $this->getDbshopTable('NavigationExtendTable')->updateNavigationExtend($navigationArray, array('navigation_id'=>$navigationId, 'language'=>$this->getDbshopLang()->getLocale()));
                //导航数组保存
                $this->writerPhpArray();
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('导航设置'), 'operlog_info'=>$this->getDbshopLang()->translate('更新导航') . '&nbsp;' . $navigationArray['navigation_title']));
            }
            unset($navigationArray);
            //跳转处理
            return $this->redirect()->toRoute('navigation/default');
        }
        
        $array = array();
        $array['navigation_info'] = $this->getDbshopTable()->infoNavigation(array('dbshop_navigation.navigation_id'=>$navigationId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        //商品分类
        $array['goods_class']  = $this->getDbshopTable('GoodsClassTable')->classOptions(0,$this->getDbshopTable('GoodsClassTable')->listGoodsClass());

        return $array;
    }
    public function allUpdateAction()
    {
        if($this->request->isPost()) {
            $navigationArray  = $this->request->getPost()->toArray();
            if(is_array($navigationArray) and !empty($navigationArray)) {
                foreach($navigationArray['navigation_sort'] as $key => $value) {
                    $this->getDbshopTable()->allUpdateNavigation(array('navigation_sort'=>$value), array('navigation_id'=>$key));
                }
            }
            //导航数组保存
            $this->writerPhpArray();
        }

        return $this->redirect()->toRoute('navigation/default');
    }
    /** 
     * 删除导航信息
     */
    public function delAction()
    {
        $navigationId = (int) $this->request->getPost('navigation_id');
        if($navigationId != 0) {
            $array = array();
            $array['navigation_info'] = $this->getDbshopTable()->infoNavigation(array('dbshop_navigation.navigation_id'=>$navigationId, 'e.language'=>$this->getDbshopLang()->getLocale()));
            
            if($this->getDbshopTable()->delNavigation(array('navigation_id'=>$navigationId))) {
                //导航数组保存
                $this->writerPhpArray();
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('导航设置'), 'operlog_info'=>$this->getDbshopLang()->translate('删除导航') . '&nbsp;' . $array['navigation_info']->navigation_title));
                
                exit('true');
            }
        }
        exit('false');
    }
    /** 
     * 将导航信息以数组的形式保存，用于前台调用
     */
    private function writerPhpArray ()
    {
        $phpWriter           = new \Zend\Config\Writer\PhpArray();
        $navigationTypeArray = array(1,2,3);
        foreach ($navigationTypeArray as $navigationType) {
            $array = array();
            $navigationArray= $this->getDbshopTable()->listNavigation(array('dbshop_navigation.navigation_type'=>$navigationType, 'e.language'=>$this->getDbshopLang()->getLocale()));
            if(is_array($navigationArray) and !empty($navigationArray)) {
                foreach ($navigationArray as $key => $value) {
                    $array[$key] = array('navigation_title'=>$value['navigation_title'], 'navigation_url'=>$value['navigation_url'], 'navigation_new_window'=>$value['navigation_new_window']);
                    //当导航位置是中部，且商品分类非0的情况下，检查该分类是否有下级分类，如果有，写入子导航
                    if($navigationType == 2 and $value['goods_class_id'] !=0) {
                        $classArray      = array();
                        $goodsClassArray = array();
                        $goodsClassArray = $this->getDbshopTable('GoodsClassTable')->listGoodsClass(array('dbshop_goods_class.class_top_id'=>$value['goods_class_id']));
                        if(is_array($goodsClassArray) and !empty($goodsClassArray)) {
                            foreach($goodsClassArray as $classValue) {
                                $classArray[] = array(
                                    'navigation_title'=>$classValue['class_name'],
                                    'navigation_url'  =>$this->url()->fromRoute('frontgoodslist/default', array('class_id'=>$classValue['class_id'])),
                                    'navigation_new_window'=>$value['navigation_new_window']);
                            }
                            $array[$key]['sub_navigation'] = $classArray;
                        }
                    }
                }
            }
            $phpWriter->toFile(DBSHOP_PATH . '/data/moduledata/Navigation/' . $navigationType . '.php', $array);
            //废除启用opcache时，在修改时，被缓存的配置
            DbshopOpcache::invalidate(DBSHOP_PATH . '/data/moduledata/Navigation/' . $navigationType . '.php');
        }
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName='NavigationTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}
