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

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class GoodslistController extends AbstractActionController
{
    private $dbTables = array();
    private $translator;
    /**
     * The default action - show the home page
     */
    public function indexAction ()
    {
        $view = new ViewModel();

        $array = array();
        $view->setTemplate('/shopfront/goods/goodslist.phtml');
        
        //获取商品分类信息
        $classId = (int) $this->params('class_id');
        if($classId <= 0) return $this->redirect()->toRoute('shopfront/default');
        $array['class_info'] = $this->getDbshopTable('GoodsClassTable')->infoGoodsClass(array('class_id'=>$classId, 'class_state'=>1));
        if($array['class_info'] == null) return $this->redirect()->toRoute('shopfront/default');

        //判断是否为手机端访问
        if($this->getServiceLocator()->get('frontHelper')->isMobile()) return $this->redirect()->toRoute('m_class/default', array('action'=>'list', 'controller'=>'Class', 'class_id'=>$classId));

        //面包屑导航
        $array['class_menu'] = $this->getDbshopTable('GoodsClassTable')->selectGoodsClass('class_id IN ('.$array['class_info']->class_path.')', array('class_path ASC'));
        
        //统计使用
        $this->layout()->dbTongJiPage = 'goods_class';
        $this->layout()->tj_class_name= $array['class_info']->class_name;
        $this->layout()->tj_class_id  = $classId;
        
        //商品分类信息输出到layout
        $this->layout()->title_name         = $array['class_info']->class_name;
        $this->layout()->extend_title_name  = $array['class_info']->class_title_extend;
        $this->layout()->extend_keywords    = $array['class_info']->class_keywords;
        $this->layout()->extend_description = $array['class_info']->class_description;
        
        //商品同级分类
        $array['current_class'] = $this->getDbshopTable('GoodsClassTable')->listGoodsClass(array('dbshop_goods_class.class_top_id'=>$array['class_info']->class_top_id));
        //商品下级分类
        $array['sub_class']     = $this->getDbshopTable('GoodsClassTable')->listGoodsClass(array('dbshop_goods_class.class_top_id'=>$classId));

        $getArray       = $this->request->getQuery()->toArray();
        /*===========================个性属性检索=================================*/
        //这里是获取最新的tag_id检索值
        $sTagArray      = ((isset($getArray['tag_id']) and !empty($getArray['tag_id'])) ? explode('|', $getArray['tag_id']) : array());
        //这里是对打散的tag内容进行get获取并组合
        $tagCArray      = ((isset($getArray['tag_c']) and !empty($getArray['tag_c'])) ? unserialize(base64_decode($getArray['tag_c'])) : array());
        if(!empty($sTagArray[0])) {
            //对tag_id进行整型处理，防止出现其他操作
            $sTagArray[1]   = (int) $sTagArray[1];
            $sTagArray[0]   = (int) $sTagArray[0];

            $tagCArray[$sTagArray[1]] = $sTagArray[0];
        } elseif (empty($sTagArray[0]) and !empty($sTagArray[1])) {
            unset($tagCArray[$sTagArray[1]]);
        }
        $array['s_tag'] = $tagCArray;
        //将已经检索的tag内容打散传入页面中
        $array['tag_c'] = !empty($array['s_tag']) ? str_replace('=', '', base64_encode(serialize($array['s_tag']))) : '';
        
        //生成tag_id的搜索字符串
        $sTagStr = '';
        if(!empty($array['s_tag'])){
            foreach ($array['s_tag'] as $st_val) {
                $st_val = (int) $st_val;//对组合后的tag_id进行重新整型处理，防止出现其他操作
                $sTagStr .= 'dbshop_goods.goods_tag_str like \'%,' . $st_val .',%\' and '; 
            }
            $sTagStr  = substr($sTagStr, 0, -5);
        }
        //获取商品列表 商品分页
        $searchArray  = array('class_id'=>$classId, 'goods_state'=>1);
        if($sTagStr != '') $searchArray['goods_tag_str'] = $sTagStr;
        
        /*===========================个性属性检索=================================*/
        /*===========================排序检索=================================*/
        $sortArray = array();
        if(isset($getArray['time_sort'])  and !empty($getArray['time_sort']))  $sortArray['goods_add_time']  = $getArray['time_sort'];
        if(isset($getArray['click_sort']) and !empty($getArray['click_sort'])) $sortArray['goods_click']     = $getArray['click_sort'];
        if(isset($getArray['price_sort']) and !empty($getArray['price_sort'])) $sortArray['goods_shop_price+1']= $getArray['price_sort'];
        $sortArray       = (is_array($sortArray) and !empty($sortArray)) ? $sortArray : ((isset($getArray['sort_c']) and !empty($getArray['sort_c'])) ? unserialize(base64_decode($getArray['sort_c'])) : array());
        $array['sort_c'] = '';
        $sortStr         = 'goods_in.class_goods_sort ASC';
        if(!empty($sortArray)) {
            $sortKey         = key($sortArray);
            $sortValue       = current($sortArray);
            if(in_array($sortKey, array('goods_add_time', 'goods_click', 'goods_shop_price+1')) and in_array($sortValue, array('ASC', 'DESC'))) {
                $array['sort_c'] = base64_encode(serialize($sortArray));
                $searchArray     = array_merge($searchArray, $sortArray);
                $sortStr         = 'dbshop_goods.' . $sortKey . ' ' . $sortValue;

                //这里之所以这样处理，是因为商品价格处使用char类型，需要+1排序才正常，下面的？语句，是为了对应模板中的排序选中状态
                $array['sort_selected'] = ($sortKey=='goods_shop_price+1' ? 'goods_shop_price' : $sortKey).$sortValue;
            }
        }
        /*===========================排序检索=================================*/
        
        $innerTable   = array('goods_in_class'=>true);
        $page 		  = $this->params('page',1);
        $array['goods_list'] = $this->getDbshopTable('GoodsTable')->goodsPageList(array('page'=>$page, 'page_num'=>16), $searchArray, $innerTable, $sortStr);
        //个性属性
        $array['class_tag_group'] = $this->getDbshopTable('GoodsClassShowTable')->arrayGoodsClassTagGroup(array('class_id'=>$classId));
        if(is_array($array['class_tag_group']) and !empty($array['class_tag_group'])) {
            $tagArray   = $this->getDbshopTable('GoodsTagTable')->listGoodsTag(array('dbshop_goods_tag.tag_group_id IN (' . implode(',', $array['class_tag_group']) . ')', 'e.language'=>$this->getDbshopLang()->getLocale()) , array('tag_group_sort ASC', 'dbshop_goods_tag.tag_sort ASC'));
            $array['goods_tag'] = array();
            $array['goods_tag_group'] = array();
            if(is_array($tagArray) and !empty($tagArray)) {
                foreach ($tagArray as $tag_value) {
                    $array['goods_tag'][$tag_value['tag_group_id']][] = array('tag_id'=>$tag_value['tag_id'],'tag_name'=>$tag_value['tag_name']);
                    $array['goods_tag_group'][$tag_value['tag_group_id']] = $tag_value['tag_group_name'];
                }
            }
        }
        
        //分类推荐商品
        $array['recommend_goods']  = $this->getDbshopTable('GoodsTable')->allGoods(array('class_id'=>$classId, 'goods_state'=>1, 'class_recommend'=>1), array('goods_in_class'=>true), 'goods_in.class_goods_sort ASC');

        //客服代码
        $this->layout()->kefu_html = $this->getServiceLocator()->get('frontHelper')->getOnlineService('class');

        $view->setVariables($array);
        return $view;
    }
    /**
     * 商品搜索
     * @return \Zend\View\Model\ViewModel
     */
    public function goodsSearchAction ()
    {
        $view = new ViewModel();
        
        $array = array();
        //顶部title使用
        $this->layout()->title_name = $this->getDbshopLang()->translate('商品搜索');

        $view->setTemplate('/shopfront/goods/goods-search.phtml');

        $searchArray = array();
        $sortArray   = array();
        $searchWhere = array();
        $sortStr     = '';
        if($this->request->isGet()) {
            $searchArray               = $this->request->getQuery()->toArray();
            $array['keywords']         = isset($searchArray['keywords']) ? htmlentities($searchArray['keywords'], ENT_QUOTES, "UTF-8") : '';
            $searchWhere['goods_name'] = $array['keywords'];
            
            /*===========================排序检索=================================*/
            if(isset($searchArray['time_sort'])  and !empty($searchArray['time_sort']))  $sortArray['goods_add_time']  = $searchArray['time_sort'];
            if(isset($searchArray['click_sort']) and !empty($searchArray['click_sort'])) $sortArray['goods_click']     = $searchArray['click_sort'];
            if(isset($searchArray['price_sort']) and !empty($searchArray['price_sort'])) $sortArray['goods_shop_price+1']= $searchArray['price_sort'];
            $sortArray       = (is_array($sortArray) and !empty($sortArray)) ? $sortArray : ((isset($getArray['sort_c']) and !empty($searchArray['sort_c'])) ? unserialize(base64_decode($searchArray['sort_c'])) : array());
            $array['sort_c'] = '';
            if(!empty($sortArray)) {
                $sortKey         = key($sortArray);
                $sortValue       = current($sortArray);
                if(in_array($sortKey, array('goods_add_time', 'goods_click', 'goods_shop_price+1')) and in_array($sortValue, array('ASC', 'DESC'))) {
                    $array['sort_c'] = base64_encode(serialize($sortArray));
                    $searchWhere     = array_merge($searchWhere, $sortArray);
                    $sortStr         = 'dbshop_goods.' . $sortKey . ' ' . $sortValue;

                    //这里之所以这样处理，是因为商品价格处使用char类型，需要+1排序才正常，下面的？语句，是为了对应模板中的排序选中状态
                    $array['sort_selected'] = ($sortKey=='goods_shop_price+1' ? 'goods_shop_price' : $sortKey).$sortValue;
                }
            }
            /*===========================排序检索=================================*/
        }
        //获取商品索引的状态，是否开启
        $goodsIndexState = $this->getServiceLocator()->get('frontHelper')->getDbshopGoodsIni('goods_index', '');
        //获取搜索商品列表 商品分页
        $searchWhere['goods_state']  = 1;
        $page = $this->params('page',1);
        if($goodsIndexState == 'true' and trim($array['keywords']) !='') {
            $array['goods_list'] = $this->getDbshopTable('GoodsIndexTable')->searchGoods(array('page'=>$page, 'page_num'=>16), $searchWhere, $sortStr);
        } else {
            $array['goods_list'] = $this->getDbshopTable('GoodsTable')->searchGoods(array('page'=>$page, 'page_num'=>16), $searchWhere, $sortStr);
        }

        //统计使用
        $this->layout()->dbTongJiPage      = 'goods_search';
        $this->layout()->tj_search_keywords= $array['keywords'];
        $this->layout()->tj_search_count   = $array['goods_list']->getTotalItemCount();
        
        $view->setVariables($array);
        return $view;
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
