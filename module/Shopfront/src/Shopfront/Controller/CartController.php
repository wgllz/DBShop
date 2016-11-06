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

class CartController extends AbstractActionController
{
    private $dbTables = array();
    private $translator;
    
    /** 
     * 购物车信息
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction ()
    {
        $array = array();
        
        $this->layout()->title_name  = $this->getDbshopLang()->translate('购物车');
        
        $array['cart_array'] = $this->getServiceLocator()->get('frontHelper')->getCartSession();

        //用户优惠与积分中的计算
        $array = $this->promotionsOrIntegralFun($array);

        //统计使用
        $this->layout()->dbTongJiPage             = 'cart';
        $this->layout()->tongji_cart_goods_array  = $array['cart_array'];
        
        return $array;
    }
    /** 
     * 添加购物车
     */
    public function addCartAction ()
    {   
        if($this->request->isPost()) {
            $postGoodsArray = $this->request->getPost()->toArray();
            
            //对购买数进行检查
            $buyNum         = intval($postGoodsArray['buy_goods_num']);
            if($buyNum <= 0) exit($this->getDbshopLang()->translate('没有购物数量，请从新选择！'));
            
            /*----------------------------检查商品是否存在，同时检查当有颜色或者尺寸属性情况下进行的处理----------------------------------*/
            //检查基础商品是否存在，并且状态是开启状态
            $goodsInfo = array();
            $goodsInfo = $this->getDbshopTable('GoodsTable')->infoGoods(array('dbshop_goods.goods_id'=>$postGoodsArray['goods_id'], 'dbshop_goods.goods_state'=>1));
            if(!$goodsInfo) exit($this->getDbshopLang()->translate('商品不存在，无法加入购物车！'));
            
            //判断优惠价格是否存在，是否过期
            $preferentialStart = (intval($goodsInfo->goods_preferential_start_time) == 0 or time() >= $goodsInfo->goods_preferential_start_time) ? true : false;
            $preferentialEnd   = (intval($goodsInfo->goods_preferential_end_time) == 0 or time() <= $goodsInfo->goods_preferential_end_time) ? true : false;
            $goodsInfo->goods_preferential_price = ($preferentialStart and $preferentialEnd and $goodsInfo->goods_preferential_price > 0) ? $goodsInfo->goods_preferential_price : 0;

            $extendGoods = array();
            //当颜色和尺寸同时存在情况下，进行检查获取商品库存及颜色和尺寸值，赋值入基础商品中
            if((isset($postGoodsArray['select_color_value']) and !empty($postGoodsArray['select_color_value'])) and (isset($postGoodsArray['select_size_value']) and !empty($postGoodsArray['select_size_value']))) {
                $extendGoods = $this->getDbshopTable('GoodsPriceExtendGoodsTable')->InfoPriceExtendGoods(array('goods_color'=>$postGoodsArray['select_color_value'], 'goods_size'=>$postGoodsArray['select_size_value'], 'goods_id'=>$postGoodsArray['goods_id']));
                if(!$extendGoods) exit($this->getDbshopLang()->translate('商品没有您选择的属性存在，无法加入购物车！'));
                $goodsInfo->goods_stock      = $extendGoods->goods_extend_stock;
                $goodsInfo->goods_item       = $extendGoods->goods_extend_item;
                $goodsInfo->goods_shop_price = ($goodsInfo->goods_preferential_price <= 0 ? $extendGoods->goods_extend_price : $goodsInfo->goods_preferential_price);
                $goodsInfo->goods_color      = $extendGoods->goods_color;
                $goodsInfo->goods_size       = $extendGoods->goods_size;
                
            } else {//当颜色或者尺寸存在其中一情况下，将其中值赋值入基础商品中
                $array = array();
                $array['goods_color'] = isset($postGoodsArray['select_color_value']) ? $postGoodsArray['select_color_value'] : '';
                $array['goods_size']  = isset($postGoodsArray['select_size_value']) ? $postGoodsArray['select_size_value'] : '';
                $array = array_filter($array);
                if(!empty($array)) {
                    $item = key($array);
                    $goodsInfo->$item = current($array);
                }
                //判断是否有优惠价格存在
                $goodsInfo->goods_shop_price = ($goodsInfo->goods_preferential_price <= 0 ? $goodsInfo->goods_shop_price : $goodsInfo->goods_preferential_price);
            }
            //属性值
            $goodsInfo->goods_color_name = '';
            $goodsInfo->goods_size_name  = '';
            if(isset($goodsInfo->goods_color) and $goodsInfo->goods_color and $goodsInfo->goods_color != 'kongzhi') {
                $colorInfo       = $this->getDbshopTable('GoodsPriceExtendTable')->infoPriceExtend(array('extend_type'=>'one', 'goods_id'=>$goodsInfo->goods_id));
                $extendColorInfo = $this->getDbshopTable('GoodsPriceExtendColorTable')->infoPriceExtendColor(array('color_value'=>$goodsInfo->goods_color, 'goods_id'=>$goodsInfo->goods_id));
                $goodsInfo->goods_color_name = '<strong>' . $colorInfo->extend_name . '</strong>: ' . $extendColorInfo->color_info;
            }
            if(isset($goodsInfo->goods_size) and $goodsInfo->goods_size) {
                $sizeInfo        = $this->getDbshopTable('GoodsPriceExtendTable')->infoPriceExtend(array('extend_type'=>'two', 'goods_id'=>$goodsInfo->goods_id));
                $extendSizeInfo  = $this->getDbshopTable('GoodsPriceExtendSizeTable')->infoPriceExtendSize(array('size_value'=>$goodsInfo->goods_size, 'goods_id'=>$goodsInfo->goods_id));
                $goodsInfo->goods_size_name  = '<strong>' . $sizeInfo->extend_name . '</strong>: ' . $extendSizeInfo->size_info;
            }
            /*----------------------------检查商品是否存在，同时检查当有颜色或者尺寸属性情况下进行的处理----------------------------------*/
            
            //判断购物数是否超过库存数
            if($buyNum > $goodsInfo->goods_stock and $goodsInfo->goods_stock_state_open != 1) exit($this->getDbshopLang()->translate('购物数量超过商品库存数量，请从新购买！'));
            
            //判断是否有最少购买数量限制
            if($goodsInfo->goods_cart_buy_min_num > 0 and $goodsInfo->goods_cart_buy_min_num > $buyNum)  exit(sprintf($this->getDbshopLang()->translate('该商品最少需要购买%s个'),$goodsInfo->goods_cart_buy_min_num));
            
            //判断是否有最多购买数量限制
            if($goodsInfo->goods_cart_buy_max_num > 0 and $goodsInfo->goods_cart_buy_max_num < $buyNum)  exit(sprintf($this->getDbshopLang()->translate('该商品最多购买%s个'),$goodsInfo->goods_cart_buy_max_num));

            //判断在没有数量限制的情况下，是否超过了购买数量
            if($goodsInfo->goods_cart_buy_max_num == 0 and $buyNum > 50) exit(sprintf($this->getDbshopLang()->translate('该商品最多购买%s个'),50));

            $key       = $goodsInfo->goods_id . ((isset($goodsInfo->goods_color) and !empty($goodsInfo->goods_color)) ? $goodsInfo->goods_color : '') . ((isset($goodsInfo->goods_size) and !empty($goodsInfo->goods_size)) ? $goodsInfo->goods_size : '');
            //判断该商品是否已经存在购物车中，如果是存在的，则进行购物数量累加
            if(!$this->getServiceLocator()->get('frontHelper')->checkCartSession($key)) {
                $oldBuyNum = $this->getServiceLocator()->get('frontHelper')->getCartOneGoodsSession($key, 'buy_num');
                $buyNum    = $oldBuyNum + $buyNum;
                $this->editCartGoodsNumCheck($key, $buyNum);
                //修改购物车
                $this->getServiceLocator()->get('frontHelper')->editCartSession($key, 'buy_num', $buyNum);
                exit('true');
                //exit($this->getDbshopLang()->translate('该商品在购物车中已经存在，无需重复购买！'));
            }

            //获取商品的N个分类信息，主要用于优惠规则中
            $classIdArray = $this->getDbshopTable('GoodsInClassTable')->listGoodsInClass(array('goods_id'=>$goodsInfo->goods_id));
            
            $cartGoods = array(
                'goods_id'         => $goodsInfo->goods_id,
                'goods_name'       => $goodsInfo->goods_name,
                'class_id'         => $postGoodsArray['class_id'],
                'goods_image'      => $goodsInfo->goods_thumbnail_image,
                'goods_stock_state'=> $goodsInfo->goods_stock_state_open,//1是开启有货功能，对于库存无限制，0 是使用库存模式
                'goods_stock'      => $goodsInfo->goods_stock,
                'buy_num'          => $buyNum,
                'goods_type'       => $goodsInfo->goods_type,
                'goods_shop_price' => $goodsInfo->goods_shop_price,
                'goods_color'      => (isset($goodsInfo->goods_color)      ? $goodsInfo->goods_color      : ''),
                'goods_size'       => (isset($goodsInfo->goods_size)       ? $goodsInfo->goods_size       : ''),
                'goods_color_name' => (isset($goodsInfo->goods_color_name) ? $goodsInfo->goods_color_name : ''),
                'goods_size_name'  => (isset($goodsInfo->goods_size_name)  ? $goodsInfo->goods_size_name  : ''),
                'goods_item'       => (isset($goodsInfo->goods_item)       ? $goodsInfo->goods_item  : ''),
                'goods_weight'     => (isset($goodsInfo->goods_weight)    ? $goodsInfo->goods_weight : 0),
                'buy_min_num'      => $goodsInfo->goods_cart_buy_min_num,//将最少购物限制写入Session
                'buy_max_num'      => $goodsInfo->goods_cart_buy_max_num,//将最大购物限制写入Session
                'integral_num'     => isset($goodsInfo->goods_integral_num) ? ($goodsInfo->goods_integral_num > 0 ? $goodsInfo->goods_integral_num : 0) : 0,
                'brand_id'         => $goodsInfo->brand_id,//商品品牌
                'class_id_array'   => $classIdArray,//商品拥有的N个分类id
            );
            $this->getServiceLocator()->get('frontHelper')->setCartSession($key, $cartGoods);
            exit('true');
        }
        exit($this->getDbshopLang()->translate('购物车添加失败！'));
    }
    /** 
     * 编辑购物车
     */
    public function editCartGoodsAction ()
    {
        $editTypeKey = trim($this->request->getPost('type'));
        $value       = $this->request->getPost($editTypeKey);
        $cartKey     = trim($this->request->getPost('cart_key'));
        //检查是否有购物数量限制
        if($editTypeKey == 'buy_num') {
            $value = intval($value)<=0 ? 1 : intval($value);//判断是否有别有用心的用户，将下拉数量的值通过浏览器变更为负数
            $this->editCartGoodsNumCheck($cartKey, $value);
        }
        //修改购物车
        $this->getServiceLocator()->get('frontHelper')->editCartSession($cartKey, $editTypeKey, $value);
        exit('true');
    }
    /**
     * 检查购物车商品数量修改
     * @param $cartKey
     * @param $value
     */
    public function editCartGoodsNumCheck($cartKey, $value)
    {
        $goodsInfo = array();
        $goodsInfo = $this->getDbshopTable('GoodsTable')->oneGoodsInfo(array('goods_id'=>$this->getServiceLocator()->get('frontHelper')->getCartOneGoodsSession($cartKey, 'goods_id'), 'goods_state'=>1));
        //判断是否有最少购买数量限制
        if(isset($goodsInfo->goods_cart_buy_min_num) and $goodsInfo->goods_cart_buy_min_num > 0 and $goodsInfo->goods_cart_buy_min_num > $value)  exit(sprintf($this->getDbshopLang()->translate('该商品最少需要购买%s个'),$goodsInfo->goods_cart_buy_min_num));
        //判断是否有最多购买数量限制
        if(isset($goodsInfo->goods_cart_buy_max_num) and $goodsInfo->goods_cart_buy_max_num > 0 and $goodsInfo->goods_cart_buy_max_num < $value)  exit(sprintf($this->getDbshopLang()->translate('该商品最多购买%s个'),$goodsInfo->goods_cart_buy_max_num));
        //如果没有限制最高数量，默认是50
        if($goodsInfo->goods_cart_buy_max_num == 0 and $value > 50) exit(sprintf($this->getDbshopLang()->translate('该商品最多购买%s个'),50));
        //检查是否和购物车限制相同，如果不同进行修改
        if(isset($goodsInfo->goods_cart_buy_min_num) and $goodsInfo->goods_cart_buy_min_num != $this->getServiceLocator()->get('frontHelper')->getCartOneGoodsSession($cartKey, 'buy_min_num')) $this->getServiceLocator()->get('frontHelper')->editCartSession($cartKey, 'buy_min_num', $goodsInfo->goods_cart_buy_min_num);
        if(isset($goodsInfo->goods_cart_buy_max_num) and $goodsInfo->goods_cart_buy_max_num != $this->getServiceLocator()->get('frontHelper')->getCartOneGoodsSession($cartKey, 'buy_max_num')) $this->getServiceLocator()->get('frontHelper')->editCartSession($cartKey, 'buy_max_num', $goodsInfo->goods_cart_buy_max_num);
    }
    /**
     * ajax获取购物车信息
     */
    public function ajaxShopCartAction()
    {
        $cartArray = $this->getServiceLocator()->get('frontHelper')->getCartSession();
        if(is_array($cartArray) and !empty($cartArray)) {
            foreach ($cartArray as $key => $val) {
                $cartArray[$key]['goods_image']      = $this->getServiceLocator()->get('frontHelper')->shopGoodsImage($cartArray[$key]['goods_image']);
                $cartArray[$key]['goods_shop_price'] = $this->getServiceLocator()->get('frontHelper')->shopPrice($cartArray[$key]['goods_shop_price']);
                $cartArray[$key]['amount']           = $cartArray[$key]['goods_shop_price'] * $cartArray[$key]['buy_num'];
                $cartArray[$key]['yun_type']         = (stripos($cartArray[$key]['goods_image'],'}/')!==false ? 'yun' : 'local');;
            }
        }
        exit(json_encode($cartArray));
    }
    /**
     * 获取添加购物车的商品信息，目前主要用于组合商品
     */
    public function getAddCartInfoAction ()
    {
        $goodsId   = (int)$this->request->getPost('goods_id');
        $classId   = (int)$this->request->getPost('class_id');
        if($goodsId != 0) {
            $postArray = array();
    
            if($classId == 0) {
                $inClass = $this->getDbshopTable('GoodsInClassTable')->oneGoodsInClass(array('dbshop_goods_in_class.goods_id'=>$goodsId, 'c.class_state'=>1));
                $classId = $inClass[0]['class_id'];
            }
            $postArray['class_id'] = $classId;
            $postArray['goods_id'] = $goodsId;
    
            $goodsExtendColor = $this->getDbshopTable('GoodsPriceExtendTable')->infoPriceExtend(array('goods_id'=>$goodsId, 'extend_type'=>'one', 'language'=>$this->getDbshopLang()->getLocale()));
            $goodsExtendSize  = $this->getDbshopTable('GoodsPriceExtendTable')->infoPriceExtend(array('goods_id'=>$goodsId, 'extend_type'=>'two', 'language'=>$this->getDbshopLang()->getLocale()));
            if(!empty($goodsExtendColor) and !empty($goodsExtendSize)) {
                $priceExtendGoodsInfo = $this->getDbshopTable('GoodsPriceExtendGoodsTable')->InfoPriceExtendGoods(array('goods_id'=>$goodsId));
                $postArray['select_color_value'] = isset($priceExtendGoodsInfo->goods_color) ? $priceExtendGoodsInfo->goods_color : '';
                $postArray['select_size_value']  = isset($priceExtendGoodsInfo->goods_size) ? $priceExtendGoodsInfo->goods_size : '';
            } elseif (!empty($goodsExtendColor) and empty($goodsExtendSize)) {
                $extendColorInfo = $this->getDbshopTable('GoodsPriceExtendColorTable')->infoPriceExtendColor(array('goods_id'=>$goodsId));
                $postArray['select_color_value'] = isset($extendColorInfo->color_value) ? $extendColorInfo->color_value : '';
            } elseif (empty($goodsExtendColor) and !empty($goodsExtendSize)) {
                $extendSizeInfo  = $this->getDbshopTable('GoodsPriceExtendSizeTable')->infoPriceExtendSize(array('goods_id'=>$goodsId));
                $postArray['select_size_value']  = isset($extendSizeInfo->size_value) ? $extendSizeInfo->size_value : '';
            }
            exit(json_encode($postArray));
        }
    
        exit('');
    }
    /** 
     * 清空购物车
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function clearCartGoodsAction ()
    {
        $this->getServiceLocator()->get('frontHelper')->clearCartSession();
        return $this->redirect()->toRoute('frontcart/default');
    }
    /** 
     * 购物车删除单个商品
     */
    public function delCartGoodsAction ()
    {
        $cartKey = trim($this->request->getPost('cart_key'));
        if(empty($cartKey)) exit();
        
        $this->getServiceLocator()->get('frontHelper')->delCartSession($cartKey);
        exit('true');
    }
    /** 
     * 购物车第二步
     */
    public function setaddressAction()
    {
        //判断是否已经登录或被删除
        $this->checkUserLoginOrDelete();
        
        $array = array();
        $this->layout()->title_name  = $this->getDbshopLang()->translate('设置收货地址');
        
        //购物车商品
        $array['cart_array'] = $this->getServiceLocator()->get('frontHelper')->getCartSession();
        if(count($array['cart_array']) <= 0) return $this->redirect()->toRoute('shopfront/default');

        //检查是购物车中是否全部为虚拟商品，如果是，则跳过配送地址选择
        if($this->checkCartVirtualGoods($array['cart_array'])) return $this->redirect()->toRoute('frontcart/default', array('action'=>'stepvirtual'));

        //收货地址
        $array['address_list'] = $this->getDbshopTable('UserAddressTable')->listAddress(array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        $array['region_array']= $this->getDbshopTable('RegionTable')->listRegion(array('dbshop_region.region_top_id=0'));
        
        return $array;
    }
    /** 
     * 购物车第三步，选择配送方式、支付方式、确认订单
     * @return number|multitype:multitype:Ambigous <multitype:, \Zend\Config\Reader\mixed, string>
     */
    public function stepAction ()
    {
        //判断是否已经登录或被删除
        $this->checkUserLoginOrDelete();
            
        $array = array();
        $this->layout()->title_name  = $this->getDbshopLang()->translate('订单确认');
        //购物车商品
        $array['cart_array'] = $this->getServiceLocator()->get('frontHelper')->getCartSession();
        $step = trim($this->request->getPost('step'));
        if(count($array['cart_array']) <= 0 or $step != 'setaddress') return $this->redirect()->toRoute('shopfront/default');
        $cartTotalPrice = $this->getServiceLocator()->get('frontHelper')->getCartTotal();
        /*----------------------配送方式与收货地址----------------------*/
        //商品总重量
        $array['total_weight'] = $this->getServiceLocator()->get('frontHelper')->getCartTotalWeight();
        //获取配送地址信息
        $addressId    = intval($this->request->getPost('user_address_id'));
        $addressInfo  = $this->getDbshopTable('UserAddressTable')->infoAddress(array('address_id'=>$addressId));
        if(!$addressInfo) return $this->redirect()->toRoute('shopfront/default');
        $array['address_info'] = $addressInfo;
        
        //获取配送地址对应的地区
        $regionInfo   = $this->getDbshopTable('RegionTable')->infoRegion(array('dbshop_region.region_id'=>$addressInfo['region_id']));
        $regionIdArray= explode(',', $regionInfo['region_path']);
        //获取配送方式信息
        $configReader = new \Zend\Config\Reader\Ini();
        $expressArray = array();
        $cashOnDelivery= '';
        $expressArray = $this->getDbshopTable('ExpressTable')->listExpress(array('express_state'=>1));
        if(is_array($expressArray) and !empty($expressArray)) {
            $expressConfigFilePath = DBSHOP_PATH . '/data/moduledata/Express/';
            $i = 0;
            $totalWeight = $array['total_weight'] * 1000;//将千克换算成克
            foreach ($expressArray as $value) {//循环读取配送方式的设置文件
                $expressExist = false;
                if(file_exists($expressConfigFilePath . $value['express_id'] . '.ini')) {
                    $expressConfig = $configReader->fromFile($expressConfigFilePath . $value['express_id'] . '.ini');
                    if($expressConfig['express_set'] == 'T') {//当为统一设置时进行的处理
                        $value['express_price'] = $this->getServiceLocator()->get('shop_express')->calculateCost(trim($expressConfig['express_price']), $totalWeight, $cartTotalPrice);
                        $expressExist = true;
                    } else {//当为个性化设置的时候，进行地区匹配
                        $value['express_price'] = 0;
                        foreach ($expressConfig['express_price'] as $price_value) {
                            $price_value['price'] = $this->getServiceLocator()->get('shop_express')->calculateCost(trim($price_value['price']), $totalWeight, $cartTotalPrice);
                            foreach ($regionIdArray as $regionId) {//循环查询，当前配送地址的地区id及其上级地区，是否在配送地区中有所体现，这里的foreach有点多了，待优化
                                if(in_array($regionId, $price_value['area_id'])) {
                                    $value['express_price'] = ($value['express_price'] > 0 and $value['express_price'] < $price_value['price']) ? $value['express_price'] : $price_value['price'];
                                    $expressExist = true;
                                }
                            }
                        }
                    }
                    //这是获取可以货到付款的配送方式的id字符串相连
                    if($value['cash_on_delivery'] == 1) $cashOnDelivery .= "'".$value['express_id'] . "',";
                }
                if($expressExist) {
                    $array['express_array'][$i] = $value;
                    $i++;
                }
            }
        }
        if(!empty($array['express_array'])) $array['express_array'][0]['selected'] = 1;
        $array['cash_on_delivery_str'] = (!empty($cashOnDelivery) ? substr($cashOnDelivery, 0, -1) : '');
        /*----------------------配送方式与收货地址----------------------*/
        /*----------------------支付方式----------------------*/
        $xmlReader    = new \Zend\Config\Reader\Xml();
        $paymentArray = array();
        $xmlPath      = DBSHOP_PATH . '/data/moduledata/Payment/';
        if(is_dir($xmlPath)) {
            $dh = opendir($xmlPath);
            while (false !== ($fileName = readdir($dh))) {
                if($fileName != '.' and $fileName != '..' and $fileName != '.DS_Store') {
                    $paymentInfo = $xmlReader->fromFile($xmlPath . '/' . $fileName);
                    
                    //判断是否符合当前的货币要求
                    $currencyState = false;
                    if(isset($paymentInfo['payment_currency']['checked']) and !empty($paymentInfo['payment_currency']['checked'])) {
                        $currencyArray = is_array($paymentInfo['payment_currency']['checked']) ? $paymentInfo['payment_currency']['checked'] : array($paymentInfo['payment_currency']['checked']);
                        $currencyState = in_array($this->getServiceLocator()->get('frontHelper')->getFrontDefaultCurrency(), $currencyArray) ? true : false;
                    } elseif (in_array($paymentInfo['editaction'], array('xxzf', 'hdfk'))) {//线下支付或者货到付款时，不进行货币判断
                        $currencyState = true;
                    }
                    
                    if($paymentInfo['payment_state']['checked'] == 1 and $currencyState) {
                        $paymentInfo['payment_fee']['content'] = ((strpos($paymentInfo['payment_fee']['content'], '%') !== false) ? round($cartTotalPrice * str_replace('%', '', $paymentInfo['payment_fee']['content'])/100, 2) : round($this->getServiceLocator()->get('frontHelper')->shopPrice($paymentInfo['payment_fee']['content']), 2));
                        $paymentArray[] = $paymentInfo;
                    }
                }
            }
        }
        //排序操作
        usort($paymentArray, function ($a, $b) {
            if($a['payment_sort']['content'] == $b['payment_sort']['content']) {
                return 0;
            }
            return ($a['payment_sort']['content'] < $b['payment_sort']['content']) ? -1 : 1;
        });
        $array['payment'] = $paymentArray;
        if(!empty($array['payment'])) $array['payment'][0]['selected'] = 1;
        /*----------------------支付方式----------------------*/
        
        //用户优惠与积分中的计算
        $array = $this->promotionsOrIntegralFun($array);
        $array['promotionsCost'] = $this->getServiceLocator()->get('frontHelper')->shopPrice($array['promotionsCost']['discountCost']);

        //支付费用已经进行了汇率转换无需再次转换，配送费用未转换需要转换
        $array['order_total'] = $this->getServiceLocator()->get('frontHelper')->shopPrice($array['express_array'][0]['express_price']) + $array['payment'][0]['payment_fee']['content'] + $cartTotalPrice - $array['promotionsCost'];

        //会员信息，会员消费积分
        $array['user_info'] = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        //获取积分转换比例
        $integralInfo = $this->getDbshopTable('UserIntegralTypeTable')->userIntegarlTypeOneInfo(array('integral_type_id'=>1));
        $currencyRate = $this->getServiceLocator()->get('frontHelper')->shopPriceRate();
        $array['integral_currency_con'] = $integralInfo->integral_currency_con / 100 * $currencyRate;

        //统计使用
        $this->layout()->dbTongJiPage             = 'cart_step_3';
        $this->layout()->tongji_cart_goods_array  = $array['cart_array'];
        
        return $array;
    }
    /**
     * 购物车第三步(虚拟商品)，选择配送方式、支付方式、确认订单
     * @return array|multitype|\Zend\Http\Response
     */
    public function stepvirtualAction()
    {
        //判断是否已经登录或被删除
        $this->checkUserLoginOrDelete();

        $array = array();
        $this->layout()->title_name  = $this->getDbshopLang()->translate('虚拟商品订单确认');
        //购物车商品
        $array['cart_array'] = $this->getServiceLocator()->get('frontHelper')->getCartSession();
        if(count($array['cart_array']) <= 0 or !$this->checkCartVirtualGoods($array['cart_array'])) return $this->redirect()->toRoute('shopfront/default');
        $cartTotalPrice = $this->getServiceLocator()->get('frontHelper')->getCartTotal();

        /*----------------------支付方式----------------------*/
        $xmlReader    = new \Zend\Config\Reader\Xml();
        $paymentArray = array();
        $xmlPath      = DBSHOP_PATH . '/data/moduledata/Payment/';
        if(is_dir($xmlPath)) {
            $dh = opendir($xmlPath);
            while (false !== ($fileName = readdir($dh))) {
                if($fileName != '.' and $fileName != '..' and $fileName != '.DS_Store' and $fileName != 'hdfk.xml') {
                    $paymentInfo = $xmlReader->fromFile($xmlPath . '/' . $fileName);

                    //判断是否符合当前的货币要求
                    $currencyState = false;
                    if(isset($paymentInfo['payment_currency']['checked']) and !empty($paymentInfo['payment_currency']['checked'])) {
                        $currencyArray = is_array($paymentInfo['payment_currency']['checked']) ? $paymentInfo['payment_currency']['checked'] : array($paymentInfo['payment_currency']['checked']);
                        $currencyState = in_array($this->getServiceLocator()->get('frontHelper')->getFrontDefaultCurrency(), $currencyArray) ? true : false;
                    } elseif (in_array($paymentInfo['editaction'], array('xxzf', 'hdfk'))) {//线下支付或者货到付款时，不进行货币判断
                        $currencyState = true;
                    }

                    if($paymentInfo['payment_state']['checked'] == 1 and $currencyState) {
                        $paymentInfo['payment_fee']['content'] = ((strpos($paymentInfo['payment_fee']['content'], '%') !== false) ? round($cartTotalPrice * str_replace('%', '', $paymentInfo['payment_fee']['content'])/100, 2) : round($this->getServiceLocator()->get('frontHelper')->shopPrice($paymentInfo['payment_fee']['content']), 2));
                        $paymentArray[] = $paymentInfo;
                    }
                }
            }
        }
        //排序操作
        usort($paymentArray, function ($a, $b) {
            if($a['payment_sort']['content'] == $b['payment_sort']['content']) {
                return 0;
            }
            return ($a['payment_sort']['content'] < $b['payment_sort']['content']) ? -1 : 1;
        });
        $array['payment'] = $paymentArray;
        if(!empty($array['payment'])) $array['payment'][0]['selected'] = 1;
        /*----------------------支付方式----------------------*/
        //用户优惠与积分中的计算
        $array = $this->promotionsOrIntegralFun($array);
        $array['promotionsCost'] = $this->getServiceLocator()->get('frontHelper')->shopPrice($array['promotionsCost']['discountCost']);

        //支付费用已经进行了汇率转换无需再次转换，配送费用未转换需要转换
        $array['order_total'] = $this->getServiceLocator()->get('frontHelper')->shopPrice($array['express_array'][0]['express_price']) + $array['payment'][0]['payment_fee']['content'] + $cartTotalPrice - $array['promotionsCost'];

        //会员信息，会员消费积分
        $array['user_info'] = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        //获取积分转换比例
        $integralInfo = $this->getDbshopTable('UserIntegralTypeTable')->userIntegarlTypeOneInfo(array('integral_type_id'=>1));
        $currencyRate = $this->getServiceLocator()->get('frontHelper')->shopPriceRate();
        $array['integral_currency_con'] = $integralInfo->integral_currency_con / 100 * $currencyRate;

        //统计使用
        $this->layout()->dbTongJiPage             = 'cart_step_3';
        $this->layout()->tongji_cart_goods_array  = $array['cart_array'];

        return $array;
    }
    /**
     * 虚拟商品订单保存
     * @return multitype
     */
    public function submitvirtualAction()
    {
        $view = new ViewModel();
        $view->setTemplate('/shopfront/cart/submit.phtml');

        //判断是否已经登录或被删除
        $this->checkUserLoginOrDelete();

        $this->layout()->title_name  = $this->getDbshopLang()->translate('订单完成');

        $postArray = $this->request->getPost()->toArray();
        //购物车商品
        $array['cart_array'] = $this->getServiceLocator()->get('frontHelper')->getCartSession();
        $step = trim($this->request->getPost('step'));
        if(count($array['cart_array']) <= 0 or $step != 'stepvirtual' or !$this->checkCartVirtualGoods($array['cart_array'])) return $this->redirect()->toRoute('shopfront/default');

        $cartTotalPrice = $this->getServiceLocator()->get('frontHelper')->getCartTotal();

        $paymentArray = array();
        //获取支付方式信息
        if(file_exists(DBSHOP_PATH . '/data/moduledata/Payment/' . $postArray['pyament_code'] . '.xml')) {
            $xmlReader = new \Zend\Config\Reader\Xml();
            $paymentArray = $xmlReader->fromFile(DBSHOP_PATH . '/data/moduledata/Payment/' . $postArray['pyament_code'] . '.xml');
            $postArray['pay_name'] = $paymentArray['payment_name']['content'];
            $postArray['order_state'] = $paymentArray['orders_state'];

            //获取支付方式的手续费用，虽然在上一页面有传值过来，但是因为html有可能被恶意更改，因此这里从新获取计算
            $paymentFee = (!empty($paymentArray['payment_fee']['content']) ? ((strpos($paymentArray['payment_fee']['content'], '%') !== false) ? round($cartTotalPrice * str_replace('%', '', $paymentArray['payment_fee']['content']) / 100, 2) : round($this->getServiceLocator()->get('frontHelper')->shopPrice($paymentArray['payment_fee']['content']), 2)) : 0);
        }
        //用户优惠与积分中的计算
        $array = $this->promotionsOrIntegralFun($array);
        $array['promotionsCost'] = $this->getServiceLocator()->get('frontHelper')->shopPrice($array['promotionsCost']['discountCost']);

        //订单总金额
        $orderTotalPrice = $paymentFee + $cartTotalPrice - $array['promotionsCost'];

        //是否使用消费积分购买
        $integralBuyState = false;
        $integralBuyPrice = 0;
        $integralBuyNum   = (isset($postArray['integral_buy_num']) and $postArray['integral_buy_num'] > 0) ? intval($postArray['integral_buy_num']) : 0;
        $cartIntegralNum  = $this->getServiceLocator()->get('frontHelper')->getCartTotalIntegral();
        $userIntegralInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        if($userIntegralInfo->user_integral_num < $integralBuyNum or $integralBuyNum > $cartIntegralNum) $integralBuyNum = 0;//如果会员拥有的积分数小于将要购买使用的积分数或者购买积分数大于购物车中的积分数，则将该购买积分数设置为0，因为无效
        if($integralBuyNum > 0 and $cartIntegralNum > 0) {//只有已经使用的积分数和购物车中的积分数都大于0时，积分购买才开启
            //计算积分被转换为货币后的价值是否超过订单总金额，如果超过，则进行无效处理
            $integralInfo = $this->getDbshopTable('UserIntegralTypeTable')->userIntegarlTypeOneInfo(array('integral_type_id'=>1));
            $integralBuyPrice   = $this->getServiceLocator()->get('frontHelper')->shopPrice($integralInfo->integral_currency_con / 100 * $integralBuyNum);
            if($integralBuyPrice <= 0 or $integralBuyPrice > $orderTotalPrice) {
                $integralBuyNum     = 0;
                $integralBuyPrice   = 0;
            } else {
                $integralBuyState   = true;
                $orderTotalPrice    = $orderTotalPrice - $integralBuyPrice;
            }
        }
        /*----------------------订单相关信息保存----------------------*/
        //开启数据库事务处理
        $this->getDbshopTable('dbshopTransaction')->DbshopTransactionBegin();

        //对post过来的价格信息进行重置，以防止恶意使用者修改html中的数值
        $postArray['goods_total_price'] = $cartTotalPrice;
        $postArray['order_total_price'] = $orderTotalPrice;
        $postArray['pay_price']         = $paymentFee;
        $postArray['express_price']     = 0;
        $postArray['buy_pre_price']     = $array['promotionsCost'];
        $postArray['user_pre_price']    = $postArray['user_pre_price'];
        $postArray['integral_buy_num']  = $integralBuyNum;
        $postArray['integral_buy_price']= $integralBuyPrice;
        $postArray['integral_num']      = $array['integralInfo']['integralNum'];
        $postArray['integral_rule_info']= $array['integralInfo']['integalRuleInfo'];
        $postArray['integral_type_2_num']          = $array['integralInfo1']['integralNum'];
        $postArray['integral_type_2_num_rule_info']= $array['integralInfo1']['integalRuleInfo'];

        $orderArray = $this->orderSave($postArray);
        $orderId    = $orderArray['order_id'];

        //消费积分，消费记录保存
        if($integralBuyState) {
            $integralLogArray = array();
            $integralLogArray['user_id']           = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
            $integralLogArray['user_name']         = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name');
            $integralLogArray['integral_log_info'] = $this->getDbshopLang()->translate('商品购物，订单号为：') . $orderArray['order_sn'];
            $integralLogArray['integral_num_log']  = '-'.$integralBuyNum;
            $integralLogArray['integral_log_time'] = time();
            if($this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray)) {
                //会员消费积分更新
                $this->getDbshopTable('UserTable')->updateUserIntegralNum($integralLogArray, array('user_id'=>$integralLogArray['user_id']));
            }
        }

        //保存订单中的商品信息
        $goodsSerialize  = array();
        $goodsStockError = array();
        foreach ($array['cart_array'] as $cart_key => $cart_value) {
            $orderGoodsId = $this->orderGoodsSave($cart_value, array('order_id'=>$orderId));
            if($orderGoodsId != -1) {//库存正确处理
                $goodsSerialize[$orderGoodsId] = array(
                    'goods_id'          => $cart_value['goods_id'],
                    'class_id'          => $cart_value['class_id'],
                    'goods_name'        => $cart_value['goods_name'],
                    'goods_extend_info' => $cart_value['goods_color_name'] . $cart_value['goods_size_name'],
                    'goods_image'       => $cart_value['goods_image'],
                    'goods_type'        => $cart_value['goods_type'],
                    'goods_shop_price'  => $this->getServiceLocator()->get('frontHelper')->shopPrice($cart_value['goods_shop_price']),
                    'buy_num'           => $cart_value['buy_num'],
                    'goods_color'       => isset($cart_value['goods_color']) ? $cart_value['goods_color'] : '',
                    'goods_size'        => isset($cart_value['goods_size']) ? $cart_value['goods_size'] : ''
                );
            } else {
                $goodsStockError[] = $cart_value['goods_name'];
            }
        }
        //判断库存是否不足，如果不足，则启用事务回滚功能
        if(!empty($goodsStockError)) {
            $this->getDbshopTable('dbshopTransaction')->DbshopTransactionRollback();//事务回滚
            $errorMessage = implode('<br>', $goodsStockError) . '<br>' . $this->getDbshopLang()->translate('商品库存不足') . '<a href="'.$this->url()->fromRoute('frontcart/default').'">' . $this->getDbshopLang()->translate('去购物车中删除库存不足的商品') . '</a>';
            exit($errorMessage);
        } else {
            $this->getDbshopTable('dbshopTransaction')->DbshopTransactionCommit();//事务确认
        }
        $this->getDbshopTable('OrderTable')->updateOrder(array('goods_serialize'=>serialize($goodsSerialize)), array('order_id'=>$orderId));
        //清空购物车操作
        $this->getServiceLocator()->get('frontHelper')->clearCartSession();
        /*----------------------订单相关信息保存----------------------*/

        $array['order_sn']    = $orderArray['order_sn'];
        $array['order_id']    = $orderId;
        $array['order_state'] = $postArray['order_state'];
        $array['order_total'] = $this->getServiceLocator()->get('frontHelper')->shopPriceSymbol() . $postArray['order_total_price'] . $this->getServiceLocator()->get('frontHelper')->shopPriceUnit();

        //下面内容为email和手机提醒信息的公共使用内容
        $sendArray = array();
        $sendArray['shopname']      = $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name');
        $sendArray['buyname']       = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name');
        $sendArray['ordersn']       = $array['order_sn'];
        $sendArray['submittime']    = $orderArray['order_time'];
        $sendArray['shopurl']       = 'http://' . $this->getRequest()->getServer('SERVER_NAME') . $this->url()->fromRoute('shopfront/default');
        /*----------------------提醒信息发送----------------------*/
        $sendMessageBody = $this->getServiceLocator()->get('frontHelper')->getSendMessageBody('submit_order');
        if($sendMessageBody != '') {
            $sendArray['subject']       = $sendArray['shopname'] . $this->getDbshopLang()->translate('提交订单提醒');
            $sendArray['send_mail'][]   = $this->getServiceLocator()->get('frontHelper')->getSendMessageBuyerEmail('submit_order_state', $this->getServiceLocator()->get('frontHelper')->getUserSession('user_email'));
            $sendArray['send_mail'][]   = $this->getServiceLocator()->get('frontHelper')->getSendMessageAdminEmail('submit_order_state');

            $sendMessageBody            = $this->getServiceLocator()->get('frontHelper')->createSendMessageContent($sendArray, $sendMessageBody);
            try {
                $sendState = $this->getServiceLocator()->get('shop_send_mail')->SendMesssageMail($sendArray, $sendMessageBody);
                $sendState = ($sendState ? 1 : 2);
            } catch (\Exception $e) {
                $sendState = 2;
            }
            //记录给用户发的电邮
            if($sendArray['send_mail'][0] != '') {
                $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_name'=>$sendArray['buyname']));
                $sendLog = array(
                    'mail_subject' => $sendArray['subject'],
                    'mail_body'    => $sendMessageBody,
                    'send_time'    => time(),
                    'user_id'      => $userInfo->user_id,
                    'send_state'   => $sendState
                );
                $this->getDbshopTable('UserMailLogTable')->addUserMailLog($sendLog);
            }
        }
        /*----------------------提醒信息发送----------------------*/

        /*----------------------手机短信信息发送----------------------*/
        $smsData = array(
            'shopname'   => $sendArray['shopname'],
            'buyname'    => $sendArray['buyname'],
            'ordersn'    => $sendArray['ordersn'],
            'submittime' => $sendArray['submittime'],
        );
        try {
            $this->getServiceLocator()->get('shop_send_sms')->toSendSms(
                $smsData,
                $this->getServiceLocator()->get('frontHelper')->getUserSession('user_phone'),
                'alidayu_submit_order_template_id',
                $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')
            );
        } catch(\Exception $e) {

        }
        /*----------------------手机短信信息发送----------------------*/

        //统计使用
        $this->layout()->dbTongJiPage             = 'submit_order';
        $this->layout()->tongji_order_sn          = $array['order_sn'];
        $this->layout()->tongji_order_price       = $postArray['order_total_price'];
        $this->layout()->tongji_order_goods_array = $array['cart_array'];

        $view->setVariables($array);
        return $view;
    }
    /** 
     * 订单提交
     */
    public function submitAction ()
    {
        //判断是否已经登录或被删除
        $this->checkUserLoginOrDelete();
        
        $this->layout()->title_name  = $this->getDbshopLang()->translate('订单完成');

        $postArray = $this->request->getPost()->toArray();

        //购物车商品
        $array['cart_array'] = $this->getServiceLocator()->get('frontHelper')->getCartSession();
        $step = trim($this->request->getPost('step'));
        if(count($array['cart_array']) <= 0 or $step != 'step' or intval($postArray['express_id']) == 0) return $this->redirect()->toRoute('shopfront/default');

        $cartTotalPrice = $this->getServiceLocator()->get('frontHelper')->getCartTotal();
        
        $paymentArray = array();
        //获取支付方式信息
        if(file_exists(DBSHOP_PATH . '/data/moduledata/Payment/' . $postArray['pyament_code'] . '.xml')) {
            $xmlReader    = new \Zend\Config\Reader\Xml();
            $paymentArray = $xmlReader->fromFile(DBSHOP_PATH . '/data/moduledata/Payment/' . $postArray['pyament_code'] . '.xml');
            $postArray['pay_name']    = $paymentArray['payment_name']['content'];
            $postArray['order_state'] = $paymentArray['orders_state'];
            
            //获取支付方式的手续费用，虽然在上一页面有传值过来，但是因为html有可能被恶意更改，因此这里从新获取计算
            $paymentFee = (!empty($paymentArray['payment_fee']['content']) ? ((strpos($paymentArray['payment_fee']['content'], '%') !== false) ? round($cartTotalPrice * str_replace('%', '', $paymentArray['payment_fee']['content'])/100, 2) : round($this->getServiceLocator()->get('frontHelper')->shopPrice($paymentArray['payment_fee']['content']), 2)) : 0);
        }
        //获取配送方式信息
        $expressArray = array();
        $expressArray = $this->getDbshopTable('ExpressTable')->infoExpress(array('express_id'=>$postArray['express_id'], 'express_state'=>1));
        $postArray['express_id']   = $expressArray->express_id;
        $postArray['express_name'] = $expressArray->express_name;
        //配送费用获取
        $addressInfo  = $this->getDbshopTable('UserAddressTable')->infoAddress(array('address_id'=>$postArray['address_id']));//对应下面的收货地址保存
        
        $regionInfo   = $this->getDbshopTable('RegionTable')->infoRegion(array('dbshop_region.region_id'=>$addressInfo['region_id']));
        $regionIdArray= explode(',', $regionInfo['region_path']);
        
        $totalWeight = $this->getServiceLocator()->get('frontHelper')->getCartTotalWeight() * 1000;//将千克换算成克
        
        $configReader = new \Zend\Config\Reader\Ini();
        $expressConfigFilePath = DBSHOP_PATH . '/data/moduledata/Express/';
        $expressConfig = $configReader->fromFile($expressConfigFilePath . $postArray['express_id'] . '.ini');
        if($expressConfig['express_set'] == 'T') {//当为统一设置时进行的处理
            $expressPrice = $this->getServiceLocator()->get('shop_express')->calculateCost(trim($expressConfig['express_price']), $totalWeight, $cartTotalPrice);
        } else {
            $expressPrice = 0;
            foreach ($expressConfig['express_price'] as $priceValue) {
                $priceValue['price'] = $this->getServiceLocator()->get('shop_express')->calculateCost(trim($priceValue['price']), $totalWeight, $cartTotalPrice);
                foreach ($regionIdArray as $regionId) {
                    $expressPrice = ($expressPrice > 0 and $expressPrice < $priceValue['price']) ? $expressPrice : $priceValue['price'];
                }
            }
        }
        $expressPrice = $this->getServiceLocator()->get('frontHelper')->shopPrice($expressPrice);
        
        //用户优惠与积分中的计算
        $array = $this->promotionsOrIntegralFun($array);
        $array['promotionsCost'] = $this->getServiceLocator()->get('frontHelper')->shopPrice($array['promotionsCost']['discountCost']);

        //订单总金额
        $orderTotalPrice = $expressPrice + $paymentFee + $cartTotalPrice - $array['promotionsCost'];

        //是否使用消费积分购买
        $integralBuyState = false;
        $integralBuyPrice = 0;
        $integralBuyNum   = (isset($postArray['integral_buy_num']) and $postArray['integral_buy_num'] > 0) ? intval($postArray['integral_buy_num']) : 0;
        $cartIntegralNum  = $this->getServiceLocator()->get('frontHelper')->getCartTotalIntegral();
        $userIntegralInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')));
        if($userIntegralInfo->user_integral_num < $integralBuyNum or $integralBuyNum > $cartIntegralNum) $integralBuyNum = 0;//如果会员拥有的积分数小于将要购买使用的积分数或者购买积分数大于购物车中的积分数，则将该购买积分数设置为0，因为无效
        if($integralBuyNum > 0 and $cartIntegralNum > 0) {//只有已经使用的积分数和购物车中的积分数都大于0时，积分购买才开启
            //计算积分被转换为货币后的价值是否超过订单总金额，如果超过，则进行无效处理
            $integralInfo = $this->getDbshopTable('UserIntegralTypeTable')->userIntegarlTypeOneInfo(array('integral_type_id'=>1));
            $integralBuyPrice   = $this->getServiceLocator()->get('frontHelper')->shopPrice($integralInfo->integral_currency_con / 100 * $integralBuyNum);
            if($integralBuyPrice <= 0 or $integralBuyPrice > $orderTotalPrice) {
                $integralBuyNum     = 0;
                $integralBuyPrice   = 0;
            } else {
                $integralBuyState   = true;
                $orderTotalPrice    = $orderTotalPrice - $integralBuyPrice;
            }
        }
        /*----------------------订单相关信息保存----------------------*/
        //开启数据库事务处理
        $this->getDbshopTable('dbshopTransaction')->DbshopTransactionBegin();
        
        //对post过来的价格信息进行重置，以防止恶意使用者修改html中的数值
        $postArray['goods_total_price'] = $cartTotalPrice;
        $postArray['order_total_price'] = $orderTotalPrice;
        $postArray['pay_price']         = $paymentFee;
        $postArray['express_price']     = $expressPrice;
        $postArray['buy_pre_price']     = $array['promotionsCost'];
        $postArray['user_pre_price']    = $postArray['user_pre_price'];
        $postArray['integral_buy_num']  = $integralBuyNum;
        $postArray['integral_buy_price']= $integralBuyPrice;
        $postArray['integral_num']      = $array['integralInfo']['integralNum'];
        $postArray['integral_rule_info']= $array['integralInfo']['integalRuleInfo'];
        $postArray['integral_type_2_num']          = $array['integralInfo1']['integralNum'];
        $postArray['integral_type_2_num_rule_info']= $array['integralInfo1']['integalRuleInfo'];

        $orderArray = $this->orderSave($postArray);
        $orderId    = $orderArray['order_id'];

        //消费积分，消费记录保存
        if($integralBuyState) {
            $integralLogArray = array();
            $integralLogArray['user_id']           = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
            $integralLogArray['user_name']         = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name');
            $integralLogArray['integral_log_info'] = $this->getDbshopLang()->translate('商品购物，订单号为：') . $orderArray['order_sn'];
            $integralLogArray['integral_num_log']  = '-'.$integralBuyNum;
            $integralLogArray['integral_log_time'] = time();
            if($this->getDbshopTable('IntegralLogTable')->addIntegralLog($integralLogArray)) {
                //会员消费积分更新
                $this->getDbshopTable('UserTable')->updateUserIntegralNum($integralLogArray, array('user_id'=>$integralLogArray['user_id']));
            }
        }

        //保存收货地址
        $this->orderDeliveryAddressSave($addressInfo, array('order_id'=>$orderId,'shipping_time'=>$postArray['shipping_time'], 'express_name'=>$postArray['express_name'], 'express_id'=>$postArray['express_id'], 'express_fee'=>$postArray['express_price']));
        
        //保存订单中的商品信息
        $goodsSerialize  = array();
        $goodsStockError = array();
        foreach ($array['cart_array'] as $cart_key => $cart_value) {
            $orderGoodsId = $this->orderGoodsSave($cart_value, array('order_id'=>$orderId));
            if($orderGoodsId != -1) {//库存正确处理
                $goodsSerialize[$orderGoodsId] = array(
                        'goods_id'          => $cart_value['goods_id'],
                        'class_id'          => $cart_value['class_id'],
                        'goods_name'        => $cart_value['goods_name'],
                        'goods_extend_info' => $cart_value['goods_color_name'] . $cart_value['goods_size_name'],
                        'goods_image'       => $cart_value['goods_image'],
                        'goods_shop_price'  => $this->getServiceLocator()->get('frontHelper')->shopPrice($cart_value['goods_shop_price']),
                        'buy_num'           => $cart_value['buy_num'],
                        'goods_color'       => isset($cart_value['goods_color']) ? $cart_value['goods_color'] : '',
                        'goods_size'        => isset($cart_value['goods_size']) ? $cart_value['goods_size'] : ''
                );
            } else {
                $goodsStockError[] = $cart_value['goods_name'];
            }
        }
        //判断库存是否不足，如果不足，则启用事务回滚功能
        if(!empty($goodsStockError)) {
            $this->getDbshopTable('dbshopTransaction')->DbshopTransactionRollback();//事务回滚
            $errorMessage = implode('<br>', $goodsStockError) . '<br>' . $this->getDbshopLang()->translate('商品库存不足') . '<a href="'.$this->url()->fromRoute('frontcart/default').'">' . $this->getDbshopLang()->translate('去购物车中删除库存不足的商品') . '</a>';
            exit($errorMessage);
        } else {
            $this->getDbshopTable('dbshopTransaction')->DbshopTransactionCommit();//事务确认
        }
        
        $this->getDbshopTable('OrderTable')->updateOrder(array('goods_serialize'=>serialize($goodsSerialize)), array('order_id'=>$orderId));
        //清空购物车操作
        $this->getServiceLocator()->get('frontHelper')->clearCartSession();
        /*----------------------订单相关信息保存----------------------*/
        
        $array['order_sn']    = $orderArray['order_sn'];
        $array['order_id']    = $orderId;
        $array['order_state'] = $postArray['order_state'];
        $array['order_total'] = $this->getServiceLocator()->get('frontHelper')->shopPriceSymbol() . $postArray['order_total_price'] . $this->getServiceLocator()->get('frontHelper')->shopPriceUnit();

        //下面内容为email和手机提醒信息的公共使用内容
        $sendArray = array();
        $sendArray['shopname']      = $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name');
        $sendArray['buyname']       = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name');
        $sendArray['ordersn']       = $array['order_sn'];
        $sendArray['submittime']    = $orderArray['order_time'];
        $sendArray['shopurl']       = 'http://' . $this->getRequest()->getServer('SERVER_NAME') . $this->url()->fromRoute('shopfront/default');
        /*----------------------提醒信息发送----------------------*/
        $sendMessageBody = $this->getServiceLocator()->get('frontHelper')->getSendMessageBody('submit_order');
        if($sendMessageBody != '') {
            $sendArray['subject']       = $sendArray['shopname'] . $this->getDbshopLang()->translate('提交订单提醒');
            $sendArray['send_mail'][]   = $this->getServiceLocator()->get('frontHelper')->getSendMessageBuyerEmail('submit_order_state', $this->getServiceLocator()->get('frontHelper')->getUserSession('user_email'));
            $sendArray['send_mail'][]   = $this->getServiceLocator()->get('frontHelper')->getSendMessageAdminEmail('submit_order_state');
			
			$sendMessageBody            = $this->getServiceLocator()->get('frontHelper')->createSendMessageContent($sendArray, $sendMessageBody);
            try {
                $sendState = $this->getServiceLocator()->get('shop_send_mail')->SendMesssageMail($sendArray, $sendMessageBody);
                $sendState = ($sendState ? 1 : 2);
            } catch (\Exception $e) {
                $sendState = 2;
            }
            //记录给用户发的电邮
            if($sendArray['send_mail'][0] != '') {
                $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_name'=>$sendArray['buyname']));
                $sendLog = array(
                        'mail_subject' => $sendArray['subject'],
                        'mail_body'    => $sendMessageBody,
                        'send_time'    => time(),
                        'user_id'      => $userInfo->user_id,
                        'send_state'   => $sendState
                );
                $this->getDbshopTable('UserMailLogTable')->addUserMailLog($sendLog);
            }
        }
        /*----------------------提醒信息发送----------------------*/

        /*----------------------手机短信信息发送----------------------*/
        $smsData = array(
            'shopname'   => $sendArray['shopname'],
            'buyname'    => $sendArray['buyname'],
            'ordersn'    => $sendArray['ordersn'],
            'submittime' => $sendArray['submittime'],
        );
        try {
            $this->getServiceLocator()->get('shop_send_sms')->toSendSms(
                $smsData,
                $this->getServiceLocator()->get('frontHelper')->getUserSession('user_phone'),
                'alidayu_submit_order_template_id',
                $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id')
            );
        } catch(\Exception $e) {

        }
        /*----------------------手机短信信息发送----------------------*/

        //统计使用
        $this->layout()->dbTongJiPage             = 'submit_order';
        $this->layout()->tongji_order_sn          = $array['order_sn'];
        $this->layout()->tongji_order_price       = $postArray['order_total_price'];
        $this->layout()->tongji_order_goods_array = $array['cart_array'];
        
        return $array;
    }
    /** 
     * 订单商品保存
     * @param array $goodsArray
     * @param array $data
     */
    private function orderGoodsSave (array $goodsArray, array $data)
    {
        //判断库存是否正常，如不正常，停止该次操作返回 -1
        if(!$this->goodsStockOper($goodsArray)) {
            return -1;
        }
        //正常，继续往下处理
        $array = array();
        $array['order_id']          = $data['order_id'];
        $array['goods_id']          = $goodsArray['goods_id'];
        $array['class_id']          = $goodsArray['class_id'];
        $array['goods_item']        = $goodsArray['goods_item'];
        $array['goods_name']        = $goodsArray['goods_name'];
        $array['goods_extend_info'] = ($goodsArray['goods_color_name'] != '' ? '<p>' . $goodsArray['goods_color_name'] . '</p>' : '') . ($goodsArray['goods_size_name'] != '' ? '<p>' . $goodsArray['goods_size_name'] . '</p>' : '');
        $array['goods_color']       = $goodsArray['goods_color'];
        $array['goods_size']        = $goodsArray['goods_size'];
        $array['goods_type']        = $goodsArray['goods_type'];
        $array['goods_shop_price']  = $this->getServiceLocator()->get('frontHelper')->shopPrice($goodsArray['goods_shop_price']);
        $array['buy_num']           = $goodsArray['buy_num'];
        $array['goods_image']       = $goodsArray['goods_image'];
        $array['goods_amount']      = $this->getServiceLocator()->get('frontHelper')->shopPrice($goodsArray['goods_shop_price'] * $goodsArray['buy_num']);
        $array['goods_count_weight']= $goodsArray['goods_weight'] * $goodsArray['buy_num'];
        $array['buyer_id']          = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        
        return $this->getDbshopTable('OrderGoodsTable')->addOrderGoods($array);
    }
    /** 
     * 库存操作处理
     * @param array $data
     * @return void|boolean
     */
    private function goodsStockOper(array $data)
    {
        $where       = array();
        $whereExtend = array();
        $stockNum    = 0;
        $extendState = false;//是否有扩展商品信息，默认是无
        
        $where['goods_id'] = $data['goods_id'];
        $goodsInfo         = $this->getDbshopTable('GoodsTable')->oneGoodsInfo($where);
        //当后台管理开启库存状态显示时，直接返回即可，无需扣除库存
        if($goodsInfo->goods_stock_state_open == 1) return true;
        //否则得到当前商品库存
        $stockNum          = $goodsInfo->goods_stock;
        //判断是否有属性，如果有属性获取扩展表中的商品信息
        if(isset($data['goods_color']) and !empty($data['goods_color']) and isset($data['goods_size']) and !empty($data['goods_size'])) {
            $whereExtend['goods_id']    = $data['goods_id'];
            $whereExtend['goods_color'] = $data['goods_color'];
            $whereExtend['goods_size']  = $data['goods_size'];
            $extendGoods                = $this->getDbshopTable('GoodsPriceExtendGoodsTable')->InfoPriceExtendGoods($whereExtend);
            if($extendGoods) {
                $stockNum    = $extendGoods->goods_extend_stock;//默认库存
                $extendState = true;//有扩展信息
            }
        }
        //判断库存是否符合要求，并于购物数量进行比较
        if($stockNum > 0 and $data['buy_num'] > 0 and $stockNum >= $data['buy_num']) {
            $stockNum = $stockNum - $data['buy_num'];
            if($extendState) {
                $this->getDbshopTable('GoodsPriceExtendGoodsTable')->updatePriceExtendGoods(array('goods_extend_stock'=>$stockNum), $whereExtend);
            } else {
                $this->getDbshopTable('GoodsTable')->oneUpdateGoods(array('goods_stock'=>$stockNum), $where);
            }
            return true;
        }
        
        return false;
    }
    /**
     * 优惠打折与积分获取的统一方法
     * @param array $goodsCart
     * @return multitype:NULL
     */
    private function promotionsOrIntegralFun(array $array)
    {
        //用户优惠和积分中的计算
        $userGroup = $this->getServiceLocator()->get('frontHelper')->getUserSession('group_id');
        //优惠金额计算结果
        $array['promotionsCost'] = $this->getServiceLocator()->get('PromotionsRuleService')->promotionsRuleCalculation(array('cartGoods'=>$array['cart_array'], 'user_group'=>$userGroup));
        //获取积分计算结果
        $array['integralInfo']   = $this->getServiceLocator()->get('IntegralRuleService')->integralRuleCalculation(array('cartGoods'=>$array['cart_array'], 'user_group'=>$userGroup));     //消费积分
        $array['integralInfo1']   = $this->getServiceLocator()->get('IntegralRuleService')->integralRuleCalculation(array('cartGoods'=>$array['cart_array'], 'user_group'=>$userGroup), 2); //等级积分

        return $array;
    }
    /** 
     * 保存订单收货地址
     * @param unknown $address
     * @param array $data
     */
    private function orderDeliveryAddressSave ($address, array $data)
    {
        $array = array();
        $array['order_id']      = $data['order_id'];
        $array['delivery_name'] = $address['true_name'];
        $array['region_id']     = $address['region_id'];
        $array['region_info']   = $address['region_value'];
        $array['region_address']= $address['address'];
        $array['zip_code']      = substr($address['zip_code'], 0, 6);
        $array['tel_phone']     = ($address['tel_area_code']=='' ? '' : $address['tel_area_code'] . '-') . $address['tel_phone'] . ($address['tel_ext']=='' ? '' : '-' . $address['tel_ext']);
        $array['mod_phone']     = $address['mod_phone'];
        $array['express_name']  = $data['express_name'];
        $array['express_time_info'] = $data['shipping_time'];
        $array['express_fee']   = $data['express_fee'];
        $array['express_id']    = $data['express_id'];

        $this->getDbshopTable('OrderDeliveryAddressTable')->addDeliveryAddress($array);
    }
    /** 
     * 订单保存
     * @param array $orderArray
     * @return unknown
     */
    private function orderSave(array $orderArray)
    {
        $array = array();
        $array['order_id']            = '';
        $array['order_sn']            = $this->createOrderSn();
        $array['order_out_sn']        = '';//out' . $array['order_sn'];
        $array['goods_amount']        = (empty($orderArray['goods_total_price']) ? '0.00' : $orderArray['goods_total_price']);
        $array['order_amount']        = (empty($orderArray['order_total_price']) ? '0.00' : $orderArray['order_total_price']);
        $array['pay_fee']             = $orderArray['pay_price'];
        $array['express_fee']         = $orderArray['express_price'];
        $array['user_pre_fee']        = $orderArray['user_pre_price'];
        $array['user_pre_info']       = '';//$orderArray[''];
        $array['buy_pre_fee']         = $orderArray['buy_pre_price'];
        $array['integral_buy_num']    = $orderArray['integral_buy_num'];
        $array['integral_buy_price'] = $orderArray['integral_buy_price'];
        $array['goods_weight_amount'] = $orderArray['goods_count_weight'];
        $array['order_state']         = $orderArray['order_state'];
        $array['pay_code']            = $orderArray['pyament_code'];
        $array['pay_name']            = $orderArray['pay_name'];
        $array['express_id']          = $orderArray['express_id'];
        $array['buyer_id']            = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        $array['buyer_name']          = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_name');
        $array['buyer_email']         = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_email');
        $array['express_name']        = $orderArray['express_name'];
        $array['order_time']          = time();
        $array['currency']            = $this->getServiceLocator()->get('frontHelper')->shopCurrency();
        $array['currency_symbol']     = $this->getServiceLocator()->get('frontHelper')->shopPriceSymbol();
        $array['currency_unit']       = $this->getServiceLocator()->get('frontHelper')->shopPriceUnit();
        $array['order_message']       = $orderArray['order_message'];
        $array['integral_num']        = $orderArray['integral_num'];
        $array['integral_rule_info']  = $orderArray['integral_rule_info'];
        $array['integral_type_2_num']            = $orderArray['integral_type_2_num'];
        $array['integral_type_2_num_rule_info']  = $orderArray['integral_type_2_num_rule_info'];
        //发票内容
        if($this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_invoice') == 'true') {
            if($orderArray['invoice_content'] != '' or $orderArray['invoice_title']) {
                $array['invoice_content'] = $orderArray['navigation_type'] . ' - ';
                if(!empty($orderArray['invoice_title'])) $array['invoice_content'] .= $this->getDbshopLang()->translate('发票抬头').'：' . $orderArray['invoice_title'] . ' - ';
                if(!empty($orderArray['invoice_content'])) $array['invoice_content'] .= $this->getDbshopLang()->translate('发票内容').'：' . $orderArray['invoice_content'];
            }
        }

        $orderId = $this->getDbshopTable('OrderTable')->addOrder($array);
        
        return array('order_id'=>$orderId, 'order_sn'=>$array['order_sn'], 'order_time'=>$array['order_time']);
    }
    /**
     * 订单编号生成
     * @return mixed
     */
    private function createOrderSn()
    {
        /**
         * 以字符串为主的订单号码
         * $chars   = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");
        $orderSn = $chars[rand(0, 25)] . $chars[rand(0, 25)] . str_replace('.', '',microtime(true));*/

        //以数字为主的订单号码
        mt_srand((double) microtime() * 1000000);
        $orderSn = date('Ymd') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

        return $orderSn;
    }
    /**
     * 检查购物车是否全部为虚拟商品，如果是返回true，如果不是返回false
     * @param $cart
     * @return bool
     */
    private function checkCartVirtualGoods($cart)
    {
        $state = true;
        if(is_array($cart) and !empty($cart)) {
            foreach($cart as $value) {
                if($value['goods_type'] == 1) $state = false;
            }
        }
        return $state;
    }
    /** 
     * 判断会员是否已经登录或者已经被删除
     */
    private function checkUserLoginOrDelete()
    {
        $userId = $this->getServiceLocator()->get('frontHelper')->getUserSession('user_id');
        //判断是否登录
        if($userId == '') return $this->redirect()->toRoute('frontuser/default',array('action'=>'login'));
        
        //判断该用户是否在登录后，后台被管理员删除
        $userInfo = $this->getDbshopTable('UserTable')->infoUser(array('user_id'=>$userId));
        if($userInfo == null) {
            $userSession = new \Zend\Session\Container();
    		$userSession->getManager()->getStorage()->clear('user_info');
    		
            return $this->redirect()->toRoute('frontuser/default', array('action'=>'login'));
        }
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
