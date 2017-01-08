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

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class GoodsController extends AbstractActionController
{
    private $dbTables = array();
    private $translator;

    public function indexAction()
    {
        $array = array();

        $goodsId = (int) $this->params('goods_id');
        $classId = (int) $this->params('class_id');

        if($goodsId <= 0 or $classId <= 0) return $this->redirect()->toRoute('mobile/default');

        //判断是否为手机端访问
        if(!$this->getServiceLocator()->get('frontHelper')->isMobile()) return $this->redirect()->toRoute('frontgoods/default', array('goods_id'=>$goodsId, 'class_id'=>$classId));

        $array['class_id']   = $classId;

        $this->layout()->dbTongJiPage = 'goods_body';
        $this->layout()->mobile_title_name = $this->getDbshopLang()->translate('商品详情');

        //商品基本信息
        $array['goods_info']   = $this->getDbshopTable('GoodsTable')->infoGoods(array('dbshop_goods.goods_id'=>$goodsId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        if(!$array['goods_info']) return $this->redirect()->toRoute('mobile/default');

        //判断优惠价格是否存在，是否过期
        $preferentialStart = (intval($array['goods_info']->goods_preferential_start_time) == 0 or time() >= $array['goods_info']->goods_preferential_start_time) ? true : false;
        $preferentialEnd   = (intval($array['goods_info']->goods_preferential_end_time) == 0 or time() <= $array['goods_info']->goods_preferential_end_time) ? true : false;
        $array['goods_info']->goods_preferential_price = ($preferentialStart and $preferentialEnd and $array['goods_info']->goods_preferential_price > 0) ? $array['goods_info']->goods_preferential_price : 0;

        //商品库存显示
        $array['goods_stock'] = $array['goods_info']->goods_stock;//默认库存数
        $stock_state_id       = '';
        if ($array['goods_info']->goods_stock <= $array['goods_info']->goods_out_of_stock_set) {//当库存达到缺货时
            $stock_state_id = $array['goods_info']->goods_out_stock_state;
        } elseif($array['goods_info']->goods_stock_state_open == 1) {//当启用库存状态显示，且库存充足
            $stock_state_id = $array['goods_info']->goods_stock_state;
        }
        if($stock_state_id != '') {//说明有文字库存显示，替换默认库存
            $stockStateInfo       = $this->getDbshopTable('StockStateTable')->infoStockState(array('e.stock_state_id'=>$stock_state_id, 'e.language'=>$this->getDbshopLang()->getLocale()));
            $array['goods_stock'] = $stockStateInfo->stock_state_name;
        }

        //商品图片
        $array['goods_images'] = $this->getDbshopTable('GoodsImageTable')->listImage(array('goods_id='.$goodsId, 'image_slide=1'))->toArray();

        //颜色和尺寸扩展
        $array['goods_color']       = $this->getDbshopTable('GoodsPriceExtendTable')->infoPriceExtend(array('extend_type'=>'one', 'goods_id'=>$array['goods_info']->goods_id));
        if($array['goods_color']) {
            $array['goods_color_array'] = $this->getDbshopTable('GoodsPriceExtendColorTable')->listPriceExtendColor(array('goods_id'=>$array['goods_info']->goods_id, 'extend_id'=>$array['goods_color']->extend_id));
        }

        $array['goods_size']   = $this->getDbshopTable('GoodsPriceExtendTable')->infoPriceExtend(array('extend_type'=>'two', 'goods_id'=>$array['goods_info']->goods_id));
        if($array['goods_size']) {
            $array['goods_size_array']  = $this->getDbshopTable('GoodsPriceExtendSizeTable')->listPriceExtendSize(array('goods_id'=>$array['goods_info']->goods_id, 'extend_id'=>$array['goods_size']->extend_id));
        }

        //商品自定义信息
        $array['goods_custom'] = $this->getDbshopTable('GoodsCustomTable')->listGoodsCustom(array('goods_id'=>$goodsId));

        //商品品牌
        if($array['goods_info']->brand_id != '') $array['brand_info'] = $this->getDbshopTable('GoodsBrandTable')->infoBrand(array('e.brand_id'=>$array['goods_info']->brand_id, 'e.language'=>$this->getDbshopLang()->getLocale()));

        //商品销量
        $array['order_count']  = $this->getDbshopTable('OrderGoodsTable')->countOrderGoodsNum(array('o.order_state!=0', 'dbshop_order_goods.goods_id='. $goodsId));
        $array['order_count']  = $array['order_count'] + intval($array['goods_info']->virtual_sales);
        //商品属性
        if($array['goods_info']->attribute_group_id != '') {
            $array['goods_attribute'] = $this->getAttributeArray($array['goods_info']->attribute_group_id, $goodsId);
        }

        //顶部title使用
        $this->layout()->title_name         = $array['goods_info']->goods_name;
        $this->layout()->extend_title_name  = $array['goods_info']->goods_extend_name;
        $this->layout()->extend_keywords    = $array['goods_info']->goods_keywords;
        $this->layout()->extend_description = $array['goods_info']->goods_description;

        //检查是否已经被收藏
        $array['favorites_state'] = 'false';
        if($this->getServiceLocator()->get('frontHelper')->getUserSession('user_id') != '') {
            $favoritesInfo = $this->getDbshopTable('UserFavoritesTable')->infoFavorites(array('goods_id'=>$goodsId, 'user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
            if($favoritesInfo) $array['favorites_state'] = 'true';
        }


        return $array;
    }
    public function goodsAskAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);

        $array = array();
        $array['goods_id'] = (int)$this->request->getQuery('goods_id');
        $array['class_id'] = (int)$this->request->getQuery('class_id');
        //商品咨询
        $askPage = $this->params('page',1);
        $array['goods_ask_list'] = $this->getDbshopTable('GoodsAskTable')->listGoodsAsk(array('page'=>$askPage, 'page_num'=>16), array('dbshop_goods_ask.ask_show_state'=>1, 'dbshop_goods_ask.goods_id'=>$array['goods_id'], 'e.language'=>$this->getDbshopLang()->getLocale()));

        return $view->setVariables($array);
    }
    /**
     * 商品评价列表ajax
     */
    public function listCommentAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);

        $array = array();
        $array['goods_id'] = (int)$this->request->getQuery('goods_id');
        $array['class_id'] = (int)$this->request->getQuery('class_id');
        //商品评价
        $page = $this->params('page',1);
        $array['goods_comment'] = $this->getDbshopTable('GoodsCommentTable')->listGoodsComment(array('page'=>$page, 'page_num'=>16), array('goods_id'=>$array['goods_id'], 'comment_show_state'=>1), true);

        return $view->setVariables($array);
    }
    /**
     * 获取商品对应的属性数组
     * @param unknown $attributeGroupId
     * @param unknown $goodsId
     * @return multitype:string
     */
    private function getAttributeArray($attributeGroupId, $goodsId)
    {
        $attributeArray      = $this->getDbshopTable('GoodsAttributeTable')->listAttribute(array('dbshop_goods_attribute.attribute_group_id'=>$attributeGroupId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        $attributeValueArray = $this->getDbshopTable('GoodsAttributeValueTable')->listAttributeValue(array('dbshop_goods_attribute_value.attribute_group_id'=>$attributeGroupId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        $valueArray = array();
        if(is_array($attributeValueArray) and !empty($attributeValueArray)) {
            foreach ($attributeValueArray as $v_value) {
                $valueArray[$v_value['attribute_id']][$v_value['value_id']] = $v_value['value_name'];
            }
        }

        //获取已经插入商品中的属性值
        $goodsInAttribute = array();
        if($goodsId != '') {
            $goodsAttribute = $this->getDbshopTable('GoodsInAttributeTable')->listGoodsInAttribute(array('goods_id'=>$goodsId));
            if(is_array($goodsAttribute) and !empty($goodsAttribute)) {
                foreach ($goodsAttribute as $gA_value) {
                    $goodsInAttribute[$gA_value['attribute_id']] = $gA_value['attribute_body'];
                }
            }
        }

        $array = array();
        if(is_array($attributeArray) and !empty($attributeArray)) {
            foreach ($attributeArray as $a_value) {
                if(isset($goodsInAttribute[$a_value['attribute_id']]) and !empty($goodsInAttribute[$a_value['attribute_id']])) {
                    switch ($a_value['attribute_type']) {
                        case 'select'://下拉菜单
                        case 'radio'://单选菜单
                            $array[] = '<strong>' .$a_value['attribute_name']. '：</strong>' . $valueArray[$a_value['attribute_id']][$goodsInAttribute[$a_value['attribute_id']]];
                            break;
                        case 'checkbox'://复选菜单
                            $checkboxChecked = explode(',', $goodsInAttribute[$a_value['attribute_id']]);
                            $checkboxV       = '';
                            foreach ($checkboxChecked as $valueId) {
                                $checkboxV .= $valueArray[$a_value['attribute_id']][$valueId] . ' , ';
                            }
                            $array[] = '<strong>' .$a_value['attribute_name']. '：</strong>' . substr($checkboxV, 0, -2);
                            break;
                        case 'input'://输入表单
                        case 'textarea'://文本域表单
                            $array[] = '<strong>' .$a_value['attribute_name']. '：</strong>' . $goodsInAttribute[$a_value['attribute_id']];
                            break;
                    }
                }
            }
        }

        return $array;
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