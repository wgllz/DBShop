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
use Zend\Config\Reader\Ini;

class GoodsController extends BaseController
{
    /**
     * 商品列表
     */
    public function indexAction ()
    {        
        $array        = array();

        $searchArray  = array();
        $innerTable   = array();//主要用于按照分类检索商品
        if($this->request->isGet()) {
            $searchArray = $this->request->getQuery()->toArray();

            if(isset($searchArray['class_id']) and $searchArray['class_id'] > 0) $innerTable   = array('goods_in_class'=>true);
        }
        //商品分页
        $page = $this->params('page',1);
        $array['page'] = $page;
        $array['query']= $searchArray;

        $array['goods_list'] = $this->getDbshopTable()->goodsPageList(array('page'=>$page, 'page_num'=>20), $searchArray, $innerTable);
        $array['searchArray']= $searchArray;

        //商品属性组
        $array['attribute_group'] = $this->getDbshopTable('GoodsAttributeGroupTable')->listAttributeGroup(array('e.language'=>$this->getDbshopLang()->getLocale()));

        //商品分类
        $array['goods_class']  = $this->getDbshopTable('GoodsClassTable')->classOptions(0,$this->getDbshopTable('GoodsClassTable')->listGoodsClass());

        //是否有商品图片需要清理
        $array['clear_image_num'] = $this->getDbshopTable('GoodsImageTable')->listImage(array('goods_id=0'))->count();

        return $array;
    }

    /**
     * 商品添加
     * @return multitype:NULL
     */
    public function addAction ()
    {
        $array = array();
        if($this->request->isPost()) {
            $goodsArray = $this->request->getPost()->toArray();
            
            if(isset($goodsArray['tag_id']) and !empty($goodsArray['tag_id'])) {
                $goodsArray['goods_tag_str'] = ',' . implode(',', $goodsArray['tag_id']) . ',';
            }
            //获取商品分类信息，与下面的商品分类结合使用，之所以放在这里是为了获取是否有分类，设置商品基础表中的goods_class_have_true字段使用
            $goodsArray['class_id'] = (isset($goodsArray['class_id']) and !empty($goodsArray['class_id'])) ? $goodsArray['class_id'] : array();
            if(!empty($goodsArray['class_id'])) $goodsArray['goods_class_have_true'] = 1;//有分类设置

            //判断是否有输入商品货号，如果没有，进行自动生成
            $goodsArray['goods_item'] = empty($goodsArray['goods_item']) ? $this->getDbshopTable()->autoCreateGoodsItem($this->getServiceLocator()->get('frontHelper')->getDbshopGoodsIni('dbshop_goods_sn_prefix')) : $goodsArray['goods_item'];

            //商品基本信息
            $goodsId    = $this->getDbshopTable()->addGoods($goodsArray);
            if($goodsId) {
                //商品扩展
                $goodsArray['goods_id']  = $goodsId;
                $goodsArray['language']  = $this->getDbshopLang()->getLocale();
                //如果没有填写关键词则使用自动分词功能
                if(empty($goodsArray['goods_keywords']) and !empty($goodsArray['goods_body'])) {
                    $goodsArray['goods_keywords'] = $this->getServiceLocator()->get('adminHelper')->dbshopPhpAnalysis($goodsArray['goods_body'], 200);
                }
                //如果没有填写描述内容，则使用上面的关键字
                if(empty($goodsArray['goods_description']) and !empty($goodsArray['goods_keywords'])) {
                    $goodsArray['goods_description'] = $goodsArray['goods_keywords'];
                }
                $this->getDbshopTable('GoodsExtendTable')->addGoodsExtend($goodsArray);
                //商品图片
                $imgUpdateWhere[] = 'goods_id="0"';
                if(isset($goodsArray['image_more']) and !empty($goodsArray['image_more'])) $imgUpdateWhere[] = 'goods_image_id IN (' . implode(',', $goodsArray['image_more']) . ')';
                $this->getDbshopTable('GoodsImageTable')->updateImage(array('goods_id'=>$goodsId), $imgUpdateWhere);
                //将编辑器上传的商品图片进行更新（修改为对应当前管理员上传的图片进行goods_id赋值）
                $this->getDbshopTable('GoodsImageTable')->updateImage(array('goods_id'=>$goodsId, 'editor_session_str'=>''), array('goods_id="0"', 'editor_session_str="'.md5(session_id()).'"'));
                //如果存在封面图片，则进行如下处理，既是封面也是幻灯片
                if(isset($goodsArray['default_image']) and !empty($goodsArray['default_image'])) {
                    $this->getDbshopTable('GoodsImageTable')->updateImage(array('image_slide'=>1, 'image_sort'=>$goodsArray['image_sort_'.$goodsArray['default_image']]), array('goods_image_id='.$goodsArray['default_image']));
                }
                //图片批量处理排序
                if(isset($goodsArray['image_more']) and count($goodsArray['image_more']) > 0) {
                    $this->getDbshopTable('GoodsImageTable')->updateImagesSort($goodsArray, $goodsArray['image_more']);
                }

                //商品分类
                $goodsInClassArray = $this->getGoodsInClassState($goodsArray['class_id']);
                $this->getDbshopTable('GoodsInClassTable')->addGoodsInClass($goodsId,$goodsInClassArray);
                //商品销售属性，颜色
                if(isset($goodsArray['goods_color_value']) and is_array($goodsArray['goods_color_value']) and !empty($goodsArray['goods_color_value'])) {
                    $priceExtendArray = array();
                    $priceExtendArray['extend_name'] = $goodsArray['goods_options_one'];
                    $priceExtendArray['goods_id']    = $goodsId;
                    $priceExtendArray['extend_type'] = 'one';
                    $priceExtendArray['extend_show_type'] = $goodsArray['extend_show_type'];
                    $priceExtendArray['language']    = $this->getDbshopLang()->getLocale();
                    $extendId = $this->getDbshopTable('GoodsPriceExtendTable')->editPriceExtend($priceExtendArray,array('goods_id'=>$goodsId,'extend_type'=>'one'));
                
                    $this->getDbshopTable('GoodsPriceExtendColorTable')->delGoodsPriceExtendColor(array('goods_id'=>$goodsId,'extend_id'=>$extendId));
                    foreach ($goodsArray['goods_color_value'] as  $color_key => $color_value) {
                        $extendColorArray = array();
                        $extendColorArray['color_value'] = $color_value;
                        $extendColorArray['color_info']  = trim($goodsArray['goods_color_info'][$color_key]);
                        $extendColorArray['extend_id']   = $extendId;
                        $extendColorArray['goods_id']    = $goodsId;
                        $this->getDbshopTable('GoodsPriceExtendColorTable')->addGoodsPriceExtendColor($extendColorArray);
                    }
                }
                //商品销售属性，尺寸
                if(isset($goodsArray['goods_size_value']) and is_array($goodsArray['goods_size_value']) and !empty($goodsArray['goods_size_value'])) {
                    $priceExtendArray = array();
                    $priceExtendArray['extend_name'] = $goodsArray['goods_options_two'];
                    $priceExtendArray['goods_id']    = $goodsId;
                    $priceExtendArray['extend_type'] = 'two';
                    $priceExtendArray['language']    = $this->getDbshopLang()->getLocale();
                    $extendId = $this->getDbshopTable('GoodsPriceExtendTable')->editPriceExtend($priceExtendArray,array('goods_id'=>$goodsId,'extend_type'=>'two'));
                
                    $this->getDbshopTable('GoodsPriceExtendSizeTable')->delPriceExtendSize(array('goods_id'=>$goodsId,'extend_id'=>$extendId));
                    foreach ($goodsArray['goods_size_value'] as $size_key => $size_val) {
                        $extendSizeArray = array();
                        $extendSizeArray['size_value'] = $size_val;
                        $extendSizeArray['size_info']  = trim($goodsArray['goods_size_info'][$size_key]);
                        $extendSizeArray['extend_id']  = $extendId;
                        $extendSizeArray['goods_id']   = $goodsId;
                        $this->getDbshopTable('GoodsPriceExtendSizeTable')->addPriceExtendSize($extendSizeArray);
                    }
                }
                //当尺寸和颜色都存在时，进行处理
                if(isset($goodsArray['goods_size_value']) and is_array($goodsArray['goods_size_value']) and !empty($goodsArray['goods_size_value']) and isset($goodsArray['goods_color_value']) and is_array($goodsArray['goods_color_value']) and !empty($goodsArray['goods_color_value'])) {
                    $this->getDbshopTable('GoodsPriceExtendGoodsTable')->delPriceExtendGoods(array('goods_id'=>$goodsId));
                    $i = 1;//规格商品货号，以基本商品货号后加 ‘-数字’的方式出现
                    foreach ($goodsArray['color_size_color'] as $c_z_k => $c_z_v) {
                        $color_size_item = trim($goodsArray['color_size_item'][$c_z_k]);

                        $extendGoodsArray = array();
                        $extendGoodsArray['goods_color']        = trim($c_z_v);
                        $extendGoodsArray['goods_size']         = trim($goodsArray['color_size_size'][$c_z_k]);
                        $extendGoodsArray['goods_extend_price'] = trim($goodsArray['color_size_price'][$c_z_k]);
                        $extendGoodsArray['goods_extend_stock'] = trim($goodsArray['color_size_stock'][$c_z_k]);
                        $extendGoodsArray['goods_id']           = $goodsId;
                        if(empty($color_size_item)) {
                            $extendGoodsArray['goods_extend_item']  = $goodsArray['goods_item'] . '-' .$i;
                            $i++;
                        } else $extendGoodsArray['goods_extend_item'] = $color_size_item;

                        $this->getDbshopTable('GoodsPriceExtendGoodsTable')->addPriceExtendGoods($extendGoodsArray);
                    }
                }
                //商品标签
                if(isset($goodsArray['tag_id']) and !empty($goodsArray['tag_id'])) {
                        $this->getDbshopTable('GoodsTagInGoodsTable')->addTagInGoods($goodsId, $goodsArray['tag_id']);
                }
                //自定义功能
                if(isset($goodsArray['custom_title']) and is_array($goodsArray['custom_title']) and !empty($goodsArray['custom_title'])) {
                    $goodsArray['custom_title'] = array_filter($goodsArray['custom_title']);
                    foreach ($goodsArray['custom_title'] as $c_key => $c_value) {
                        $customArray = array();
                        $customArray['custom_title']   = $c_value;
                        $customArray['custom_content'] = isset($goodsArray['custom_content'][$c_key]) ? $goodsArray['custom_content'][$c_key] : '';
                        $customArray['goods_id']       = $goodsId;
                        $customArray['custom_key']     = $c_key;
                        $this->getDbshopTable('GoodsCustomTable')->addGoodsCustom($customArray);
                    }
                }
                //商品属性添加
                if(isset($goodsArray['attribute_type']) and !empty($goodsArray['attribute_type']) and isset($goodsArray['attribute_group_id']) and !empty($goodsArray['attribute_group_id'])) {
                    //插入属性
                    foreach ($goodsArray['attribute_type'] as $attr_key => $attr_val) {
                        $goodsInAttributeArray = array();
                        $goodsInAttributeArray['goods_id']       = $goodsId;
                        $goodsInAttributeArray['attribute_id']   = $attr_key;
                        if(isset($goodsArray['attribute_value'][$attr_key]) and !empty($goodsArray['attribute_value'][$attr_key])) {
                            $goodsInAttributeArray['attribute_body'] = $this->getAttributeInputValue($attr_val, $goodsArray['attribute_value'][$attr_key]);
                            $this->getDbshopTable('GoodsInAttributeTable')->addGoodsInAttribute($goodsInAttributeArray);
                        }
                    }
                }
                //关联商品操作
                $this->getDbshopTable('GoodsRelatedTable')->updateRelatedGoods(array('goods_id'=>$goodsId), array('goods_id'=>'0'));
                //组合商品操作
                $this->getDbshopTable('GoodsCombinationTable')->updateCombinationGoods(array('goods_id'=>$goodsId), array('goods_id'=>'0'));
            }

            //把该商品纳入商品索引中
            $classId = '';
            if(is_array($goodsInClassArray) and !empty($goodsInClassArray)) {
                foreach($goodsInClassArray as $gKey => $gValue) {
                    if($gValue == 1) {
                        $classId = $gKey;
                        break;
                    }
                }
            }
            if(!empty($classId)) {
                $this->goodsIndexCreateOrUpdate(array(
                    'goods_id'          =>$goodsId,
                    'one_class_id'      => $classId,
                ));
            }

            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理商品'), 'operlog_info'=>$this->getDbshopLang()->translate('添加商品') . '&nbsp;' . $goodsArray['goods_name']));

            unset($goodsArray);
            return $this->redirect()->toRoute('goods/default',array('controller'=>'goods','action'=>'index'));
        }
        //商品品牌
        $array['goods_brand'] = $this->getDbshopTable('GoodsBrandTable')->listGoodsBrand();
        //商品图片,这里注释掉，原因是多人添加图片时，容易混乱
        //$array['goods_images'] = $this->getDbshopTable('GoodsImageTable')->listImage(array('goods_id=0'));
        //商品分类
        $array['goods_class']  = $this->getDbshopTable('GoodsClassTable')->classOptions(0,$this->getDbshopTable('GoodsClassTable')->listGoodsClass());
        //商品标签
        $tagArray   = $this->getDbshopTable('GoodsTagTable')->listGoodsTag(array("(dbshop_goods_tag.template_tag='" . DBSHOP_TEMPLATE . "' and dbshop_goods_tag.show_type='pc') or (dbshop_goods_tag.template_tag='" . DBSHOP_PHONE_TEMPLATE . "' and dbshop_goods_tag.show_type='phone')".' or (dbshop_goods_tag.template_tag="" and dbshop_goods_tag.tag_type is NULL)', 'e.language'=>$this->getDbshopLang()->getLocale()), array('dbshop_goods_tag.tag_sort ASC', 'dbshop_goods_tag.tag_id ASC'));
        $array['goods_tag'] = array();
        $array['goods_tag_group'] = array();
        if(is_array($tagArray) and !empty($tagArray)) {
            foreach ($tagArray as $tag_value) {
                $array['goods_tag'][$tag_value['tag_group_id']][] = array('tag_id'=>$tag_value['tag_id'],'tag_name'=>$tag_value['tag_name']);
                $array['goods_tag_group'][$tag_value['tag_group_id']] = (!empty($tag_value['tag_group_mark']) ? '<strong>['.$tag_value['tag_group_mark'].']</strong>' : '').$tag_value['tag_group_name'];
            }
        }
        //商品属性组
        $array['attribute_group'] = $this->getDbshopTable('GoodsAttributeGroupTable')->listAttributeGroup(array('e.language'=>$this->getDbshopLang()->getLocale()));
        //库存状态
        $array['stock_state_array']  = $this->getDbshopTable('StockStateTable')->listStockState();
        
        $array['get_class_id'] = (int) $this->params('goods_class_id', 0);
        if($array['get_class_id'] != 0) {
            $array['get_class_info'] = $this->getDbshopTable('GoodsClassTable')->infoGoodsClass(array('class_id'=>$array['get_class_id']));
        }

        //语言标识输出
        $array['dbshop_language'] = $this->getDbshopLang()->getLocale();

        return $array;
    }
    /**
     * 商品编辑
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:NULL
     */
    public function editAction ()
    {
        $goodsId =  (int) $this->params('goods_id', 0);
        if(!$goodsId) {//商品id不存在时
            return $this->redirect()->toRoute('goods/default',array('controller'=>'goods','action'=>'index'));
        }
        $array = array();
        //用于返回对应的分页数
        $page = $this->params('page',1);
        $array['page']     = $page;
        $array['query']= $this->request->getQuery()->toArray();

        if($this->request->isPost()) {
            $goodsArray  = $this->request->getPost()->toArray();
            
            if(isset($goodsArray['tag_id']) and !empty($goodsArray['tag_id'])) {
                $goodsArray['goods_tag_str'] = ',' . implode(',', $goodsArray['tag_id']) . ',';
            } else {
                $goodsArray['goods_tag_str'] = ',';
            }
            //获取商品分类信息，与下面的商品分类结合使用，之所以放在这里是为了获取是否有分类，设置商品基础表中的goods_class_have_true字段使用
            $goodsArray['class_id'] = (isset($goodsArray['class_id']) and !empty($goodsArray['class_id'])) ? $goodsArray['class_id'] : array();
            if(!empty($goodsArray['class_id'])) $goodsArray['goods_class_have_true'] = 1;//有分类设置

            //判断是否有输入商品货号，如果没有，进行自动生成
            $goodsArray['goods_item'] = empty($goodsArray['goods_item']) ? $this->getDbshopTable()->autoCreateGoodsItem($this->getServiceLocator()->get('frontHelper')->getDbshopGoodsIni('dbshop_goods_sn_prefix'), $goodsId) : $goodsArray['goods_item'];

            //商品基本信息更新
            $updateState = $this->getDbshopTable()->updateGoods($goodsArray,array('goods_id'=>$goodsId));
            if($updateState) {
                //扩展信息更新
                //如果没有填写关键词则使用自动分词功能
                if(empty($goodsArray['goods_keywords']) and !empty($goodsArray['goods_body'])) {
                    $goodsArray['goods_keywords'] = $this->getServiceLocator()->get('adminHelper')->dbshopPhpAnalysis($goodsArray['goods_body'], 200);
                }
                //如果没有填写描述内容，则使用上面的关键字
                if(empty($goodsArray['goods_description']) and !empty($goodsArray['goods_keywords'])) {
                    $goodsArray['goods_description'] = $goodsArray['goods_keywords'];
                }
                $this->getDbshopTable('GoodsExtendTable')->updateGoodsExtend($goodsArray,array('goods_id'=>$goodsId,'language'=>$this->getDbshopLang()->getLocale()));
                //商品图片
                $imgUpdateWhere[] = 'goods_id="0"';
                if(isset($goodsArray['image_more']) and !empty($goodsArray['image_more'])) $imgUpdateWhere[] = 'goods_image_id IN (' . implode(',', $goodsArray['image_more']) . ')';
                $this->getDbshopTable('GoodsImageTable')->updateImage(array('goods_id'=>$goodsId), $imgUpdateWhere);
                //将编辑器上传的商品图片进行更新（修改为对应当前管理员上传的图片进行goods_id赋值）
                $this->getDbshopTable('GoodsImageTable')->updateImage(array('goods_id'=>$goodsId, 'editor_session_str'=>''), array('goods_id="0"', 'editor_session_str="'.md5(session_id()).'"'));
                //如果存在封面图片，则进行如下处理，既是封面也是幻灯片
                if(isset($goodsArray['default_image']) and !empty($goodsArray['default_image'])) {
                    $this->getDbshopTable('GoodsImageTable')->updateImage(array('image_slide'=>1, 'image_sort'=>$goodsArray['image_sort_'.$goodsArray['default_image']]), array('goods_image_id='.$goodsArray['default_image']));
                }
                //图片批量处理排序
                if(isset($goodsArray['image_more']) and count($goodsArray['image_more']) > 0) {
                    $this->getDbshopTable('GoodsImageTable')->updateImagesSort($goodsArray, $goodsArray['image_more']);
                }

                //商品分类
                $goodsInClassArray = $this->getGoodsInClassState($goodsArray['class_id']);
                $this->getDbshopTable('GoodsInClassTable')->addGoodsInClass($goodsId,$goodsInClassArray);
               
                //商品销售属性，颜色
                if(isset($goodsArray['goods_color_value']) and is_array($goodsArray['goods_color_value']) and !empty($goodsArray['goods_color_value'])) {
                    $priceExtendArray = array();
                    $priceExtendArray['extend_name'] = $goodsArray['goods_options_one'];
                    $priceExtendArray['goods_id']    = $goodsId;
                    $priceExtendArray['extend_type'] = 'one';
                    $priceExtendArray['extend_show_type'] = $goodsArray['extend_show_type'];
                    $priceExtendArray['language']    = $this->getDbshopLang()->getLocale();
                    $extendId = $this->getDbshopTable('GoodsPriceExtendTable')->editPriceExtend($priceExtendArray,array('goods_id'=>$goodsId,'extend_type'=>'one'));

                    $this->getDbshopTable('GoodsPriceExtendColorTable')->delGoodsPriceExtendColor(array('goods_id'=>$goodsId,'extend_id'=>$extendId));
                    foreach ($goodsArray['goods_color_value'] as  $color_key => $color_value) {
                        $extendColorArray = array();
                        $extendColorArray['color_value'] = $color_value;
                        $extendColorArray['color_info']  = trim($goodsArray['goods_color_info'][$color_key]);
                        $extendColorArray['extend_id']   = $extendId;
                        $extendColorArray['goods_id']    = $goodsId;
                        $this->getDbshopTable('GoodsPriceExtendColorTable')->addGoodsPriceExtendColor($extendColorArray);
                    }
                } else {
                    $this->getDbshopTable('GoodsPriceExtendColorTable')->delGoodsPriceExtendColor(array('goods_id'=>$goodsId));
                }
                
                //商品销售属性，尺寸
                if(isset($goodsArray['goods_size_value']) and is_array($goodsArray['goods_size_value']) and !empty($goodsArray['goods_size_value'])) {
                    $priceExtendArray = array();
                    $priceExtendArray['extend_name'] = $goodsArray['goods_options_two'];
                    $priceExtendArray['goods_id']    = $goodsId;
                    $priceExtendArray['extend_type'] = 'two';
                    $priceExtendArray['language']    = $this->getDbshopLang()->getLocale();
                    $extendId = $this->getDbshopTable('GoodsPriceExtendTable')->editPriceExtend($priceExtendArray,array('goods_id'=>$goodsId,'extend_type'=>'two'));
                    
                    $this->getDbshopTable('GoodsPriceExtendSizeTable')->delPriceExtendSize(array('goods_id'=>$goodsId,'extend_id'=>$extendId));
                    foreach ($goodsArray['goods_size_value'] as $size_key => $size_val) {
                        $extendSizeArray = array();
                        $extendSizeArray['size_value'] = $size_val;
                        $extendSizeArray['size_info']  = trim($goodsArray['goods_size_info'][$size_key]);
                        $extendSizeArray['extend_id']  = $extendId;
                        $extendSizeArray['goods_id']   = $goodsId;
                        $this->getDbshopTable('GoodsPriceExtendSizeTable')->addPriceExtendSize($extendSizeArray);                  
                    }
                } else {
                    $this->getDbshopTable('GoodsPriceExtendSizeTable')->delPriceExtendSize(array('goods_id'=>$goodsId));
                }
                
                //当尺寸和颜色都存在时，进行处理
                if(isset($goodsArray['goods_size_value']) and is_array($goodsArray['goods_size_value']) and !empty($goodsArray['goods_size_value']) and isset($goodsArray['goods_color_value']) and is_array($goodsArray['goods_color_value']) and !empty($goodsArray['goods_color_value'])) {
                    $this->getDbshopTable('GoodsPriceExtendGoodsTable')->delPriceExtendGoods(array('goods_id'=>$goodsId));
                    $i = 1;//规格商品货号，以基本商品货号后加 ‘-数字’的方式出现
                    foreach ($goodsArray['color_size_color'] as $c_z_k => $c_z_v) {
                        $color_size_item = trim($goodsArray['color_size_item'][$c_z_k]);

                        $extendGoodsArray = array();
                        $extendGoodsArray['goods_color']        = trim($c_z_v);
                        $extendGoodsArray['goods_size']         = trim($goodsArray['color_size_size'][$c_z_k]);
                        $extendGoodsArray['goods_extend_price'] = trim($goodsArray['color_size_price'][$c_z_k]);
                        $extendGoodsArray['goods_extend_stock'] = trim($goodsArray['color_size_stock'][$c_z_k]);
                        $extendGoodsArray['goods_id']           = $goodsId;
                        if(empty($color_size_item)) {
                            $extendGoodsArray['goods_extend_item']  = $goodsArray['goods_item'] . '-' .$i;
                            $i++;
                        } else $extendGoodsArray['goods_extend_item'] = $color_size_item;

                        $this->getDbshopTable('GoodsPriceExtendGoodsTable')->addPriceExtendGoods($extendGoodsArray);
                    }
                } else {
                    $this->getDbshopTable('GoodsPriceExtendGoodsTable')->delPriceExtendGoods(array('goods_id'=>$goodsId));
                }
                
                //商品标签
                if(isset($goodsArray['tag_id']) and !empty($goodsArray['tag_id'])) {
                    $this->getDbshopTable('GoodsTagInGoodsTable')->addTagInGoods($goodsId, $goodsArray['tag_id']);
                } else {
                    $this->getDbshopTable('GoodsTagInGoodsTable')->delTagInGoods(array('goods_id'=>$goodsId));
                }
                //自定义功能
                if(isset($goodsArray['custom_title']) and is_array($goodsArray['custom_title']) and !empty($goodsArray['custom_title'])) {
                    $goodsArray['custom_title'] = array_filter($goodsArray['custom_title']);
                    $this->getDbshopTable('GoodsCustomTable')->delGoodsCustom(array('goods_id'=>$goodsId));
                    foreach ($goodsArray['custom_title'] as $c_key => $c_value) {
                        $customArray = array();
                        $customArray['custom_title']   = $c_value;
                        $customArray['custom_content'] = isset($goodsArray['custom_content'][$c_key]) ? $goodsArray['custom_content'][$c_key] : '';
                        $customArray['goods_id']       = $goodsId;
                        $customArray['custom_key']     = $c_key;
                        $this->getDbshopTable('GoodsCustomTable')->addGoodsCustom($customArray);
                    }
                    unset($customArray);
                }
                //商品属性编辑
                if(isset($goodsArray['attribute_type']) and !empty($goodsArray['attribute_type']) and isset($goodsArray['attribute_group_id']) and !empty($goodsArray['attribute_group_id'])) {
                    //先清除该商品下的属性
                    $this->getDbshopTable('GoodsInAttributeTable')->delGoodsInAttribute(array('goods_id'=>$goodsId));
                    //插入属性
                    foreach ($goodsArray['attribute_type'] as $attr_key => $attr_val) {
                        $goodsInAttributeArray = array();
                        $goodsInAttributeArray['goods_id']       = $goodsId;
                        $goodsInAttributeArray['attribute_id']   = $attr_key;
                        if(isset($goodsArray['attribute_value'][$attr_key]) and !empty($goodsArray['attribute_value'][$attr_key])) {
                            $goodsInAttributeArray['attribute_body'] = $this->getAttributeInputValue($attr_val, $goodsArray['attribute_value'][$attr_key]);
                            $this->getDbshopTable('GoodsInAttributeTable')->addGoodsInAttribute($goodsInAttributeArray);
                        }
                    }
                }
            }

            //把该商品纳入商品索引中
            $classId = '';
            if(is_array($goodsInClassArray) and !empty($goodsInClassArray)) {
                foreach($goodsInClassArray as $gKey => $gValue) {
                    if($gValue == 1) {
                        $classId = $gKey;
                        break;
                    }
                }
            }
            if(!empty($classId)) {
                $this->goodsIndexCreateOrUpdate(array(
                    'goods_id'          =>$goodsId,
                    'one_class_id'      => $classId,
                ));
            }

            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理商品'), 'operlog_info'=>$this->getDbshopLang()->translate('更新商品') . '&nbsp;' . $goodsArray['goods_name']));
            
            if($goodsArray['goods_save_type'] == 'save_return_edit') {
                $array['success_msg'] = $this->getDbshopLang()->translate('商品编辑成功！');
            } else {
                return $this->redirect()->toRoute('goods/default/page',array('controller'=>'goods','action'=>'index', 'page'=>$page), array('query'=>$array['query']));
            }
            unset($goodsArray);
        }
        //商品基本信息
        $array['goods_info']   = $this->getDbshopTable()->infoGoods(array('dbshop_goods.goods_id'=>$goodsId,'e.language'=>$this->getDbshopLang()->getLocale()));
        //商品分类
        $array['goods_class']  = $this->getDbshopTable('GoodsClassTable')->classOptions(0,$this->getDbshopTable('GoodsClassTable')->listGoodsClass());
        //商品图片
        $array['goods_images'] = $this->getDbshopTable('GoodsImageTable')->listImage(array('goods_id='.$goodsId));
        //商品中的分类
        $array['in_class']     = $this->getDbshopTable('GoodsInClassTable')->listGoodsInClass(array('goods_id'=>$goodsId));
        //销售规格，颜色和尺寸
        $array['goods_color']  = $this->getDbshopTable('GoodsPriceExtendTable')->infoPriceExtend(array('extend_type'=>'one','goods_id'=>$goodsId));
        $array['goods_size']   = $this->getDbshopTable('GoodsPriceExtendTable')->infoPriceExtend(array('extend_type'=>'two','goods_id'=>$goodsId));
        //颜色
        $colorArray            = $this->getDbshopTable('GoodsPriceExtendColorTable')->listPriceExtendColor(array('goods_id'=>$goodsId));
        $array['color_list']   = $colorArray;
        $array['color_array']  = array();
        if(is_array($colorArray) and !empty($colorArray)) {
            foreach ($colorArray as $color_val) {
                $array['color_array'][$color_val['color_value']] = $color_val['color_info'];
            }            
        }
        //尺寸
        $sizeArray             = $this->getDbshopTable('GoodsPriceExtendSizeTable')->listPriceExtendSize(array('goods_id'=>$goodsId));
        $array['size_array']   = array();
        if(is_array($sizeArray) and !empty($sizeArray)) {
            foreach ($sizeArray as $size_val) {
                $array['size_array'][$size_val['size_value']] = $size_val['size_info'];
            }
        }
        //颜色和尺寸混合列表
        $priceGoodsArray            = $this->getDbshopTable('GoodsPriceExtendGoodsTable')->listPriceExtendGoods(array('goods_id'=>$goodsId));
        $array['price_goods_array'] = array();
        if(is_array($priceGoodsArray) and !empty($priceGoodsArray)) {
            foreach ($priceGoodsArray as $goods_val) {
                $array['price_goods_array'][$goods_val['goods_color']][] = $goods_val;
            }
        }
        //商品品牌
        $array['goods_brand'] = $this->getDbshopTable('GoodsBrandTable')->listGoodsBrand();
        //商品标签
        $tagArray   = $this->getDbshopTable('GoodsTagTable')->listGoodsTag(array("(dbshop_goods_tag.template_tag='" . DBSHOP_TEMPLATE . "' and dbshop_goods_tag.show_type='pc') or (dbshop_goods_tag.template_tag='" . DBSHOP_PHONE_TEMPLATE . "' and dbshop_goods_tag.show_type='phone')".' or (dbshop_goods_tag.template_tag="" and dbshop_goods_tag.tag_type is NULL)', 'e.language'=>$this->getDbshopLang()->getLocale()) , array('dbshop_goods_tag.tag_sort ASC', 'dbshop_goods_tag.tag_id ASC'));
        $array['goods_tag'] = array();
        $array['goods_tag_group'] = array();
        if(is_array($tagArray) and !empty($tagArray)) {
            foreach ($tagArray as $tag_value) {
                $array['goods_tag'][$tag_value['tag_group_id']][] = array('tag_id'=>$tag_value['tag_id'],'tag_name'=>$tag_value['tag_name']);
                $array['goods_tag_group'][$tag_value['tag_group_id']] = (!empty($tag_value['tag_group_mark']) ? '<strong>['.$tag_value['tag_group_mark'].']</strong>' : '').$tag_value['tag_group_name'];
            }
        }
        $array['in_goods_tag'] = $this->getDbshopTable('GoodsTagInGoodsTable')->getInGoodsTag(array('goods_id'=>$goodsId));
        //库存状态
        $array['stock_state_array']  = $this->getDbshopTable('StockStateTable')->listStockState();
        //自定义信息
        $customArray = $this->getDbshopTable('GoodsCustomTable')->listGoodsCustom(array('goods_id'=>$goodsId));
        $array['goods_custom'] = array();
        foreach ($customArray as $value) {
            $array['goods_custom'][$value['custom_key']] = $value;
        }
        //商品属性组
        $array['attribute_group'] = $this->getDbshopTable('GoodsAttributeGroupTable')->listAttributeGroup(array('e.language'=>$this->getDbshopLang()->getLocale()));
        if($array['goods_info']->attribute_group_id != '') {
            $array['attribute_html'] = $this->getAttributeHtml($array['goods_info']->attribute_group_id, $goodsId);
        }
        //关联商品
        $array['related_goods_array'] = $this->getDbshopTable('GoodsRelatedTable')->listRelatedGoods(array('dbshop_goods_related.goods_id'=>$goodsId,'e.language'=>$this->getDbshopLang()->getLocale()));
        //组合商品
        $array['combination_goods_array'] = $this->getDbshopTable('GoodsCombinationTable')->listCombinationGoods(array('dbshop_goods_combination.goods_id'=>$goodsId,'e.language'=>$this->getDbshopLang()->getLocale()));

        //语言标识输出
        $array['dbshop_language'] = $this->getDbshopLang()->getLocale();

        return $array;
    }
    /**
     * 添加虚拟商品页面
     * @return array|\Zend\Http\Response
     */
    public function addVirtualGoodsAction()
    {
        $goodsId =  (int) $this->params('goods_id', 0);
        if(!$goodsId) {//商品id不存在时
            return $this->redirect()->toRoute('goods/default',array('controller'=>'goods','action'=>'index'));
        }
        $array = array();
        //用于返回对应的分页数
        $page = $this->params('page',1);
        $array['page']     = $page;
        $array['query']= $this->request->getQuery()->toArray();

        //商品基本信息
        $array['goods_info']   = $this->getDbshopTable()->infoGoods(array('dbshop_goods.goods_id'=>$goodsId,'e.language'=>$this->getDbshopLang()->getLocale()));

        //虚拟商品
        $array['virtual_goods_list'] = $this->getDbshopTable('VirtualGoodsTable')->listVirtualGoods(array('dbshop_virtual_goods.goods_id'=>$goodsId));

        return $array;
    }
    /**
     * 虚拟商品保存
     */
    public function saveVirtualGoodsAction()
    {
        $array = array('state'=>'false', 'message'=>$this->getDbshopLang()->translate('虚拟商品添加失败！'));
        if ($this->request->isPost()) {
            $virtualGoods = $this->request->getPost()->toArray();
            if($virtualGoods['virtual_goods_end_time']!='' and strtotime($virtualGoods['virtual_goods_end_time']) < time()) $virtualGoods['virtual_goods_state'] = 3;//说明该商品已经过期

            if(isset($virtualGoods['virtual_goods_id']) and !empty($virtualGoods['virtual_goods_id'])) {//编辑
                $virtualGoodsId = $virtualGoods['virtual_goods_id'];
                $this->getDbshopTable('VirtualGoodsTable')->updateVirtualGoods($virtualGoods, array('virtual_goods_id'=>$virtualGoodsId));
                $type = 'edit';
            } else {//新增
                $virtualGoodsId = $this->getDbshopTable('VirtualGoodsTable')->addVirtualGoods($virtualGoods);
                $type = 'add';
            }

            if($virtualGoodsId != 0) {
                $goodsInfo = $this->getDbshopTable()->oneGoodsInfo(array('goods_id'=>$virtualGoods['goods_id']));
                if($goodsInfo->virtual_goods_add_state == 0) {//当商品状态是，虚拟商品没有补货
                    $virtualGoodsNum = $this->getDbshopTable('VirtualGoodsTable')->countVirtualGoods(array('goods_id'=>$virtualGoods['goods_id'], 'virtual_goods_state'=>1));
                    if($virtualGoodsNum > 0) $this->getDbshopTable()->oneUpdateGoods(array('virtual_goods_add_state'=>1), array('goods_id'=>$virtualGoods['goods_id']));
                }

                $virtualGoodsArray = $this->getDbshopTable('VirtualGoodsTable')->infoVirtualGoods(array('virtual_goods_id'=>$virtualGoodsId));
                $stateArray = array(1=>$this->getDbshopLang()->translate('未交易'), 2=>'<strong style="color: green;">'.$this->getDbshopLang()->translate('已交易').'</strong>', 3=>'<strong style="color: red;">'.$this->getDbshopLang()->translate('已过期').'</strong>');
                $typeArray  = array(1=>$this->getDbshopLang()->translate('手动添加'), 2=>$this->getDbshopLang()->translate('自动生成'), 3=>$this->getDbshopLang()->translate('自动重复'));
                $virtualGoodsArray[0]['virtual_goods_end_time'] = $virtualGoodsArray[0]['virtual_goods_end_time'] == 0 ? '' : date("Y-m-d", $virtualGoodsArray[0]['virtual_goods_end_time']);
                $virtualGoodsArray[0]['v_state']                = $virtualGoodsArray[0]['virtual_goods_state'];//用户前台js显示时的判断
                $virtualGoodsArray[0]['virtual_goods_state']    = $stateArray[$virtualGoodsArray[0]['virtual_goods_state']];
                $virtualGoodsArray[0]['virtual_goods_account_type']  = $typeArray[$virtualGoodsArray[0]['virtual_goods_account_type']];
                $virtualGoodsArray[0]['virtual_goods_password_type'] = $typeArray[$virtualGoodsArray[0]['virtual_goods_password_type']];
                $virtualGoodsArray[0]['type']                   = $type;//抛出页面中，js对于类型的判断
                if(empty($virtualGoodsArray[0]['order_sn'])) $virtualGoodsArray[0]['order_sn'] = '';

                $array = array('state'=>'true', 'goods'=>$virtualGoodsArray[0]);
            }
        }
        exit(json_encode($array));
    }
    /**
     *
     */
    public function infoVirtualGoodsAction()
    {
        $array = array('state'=>'false', 'message'=>$this->getDbshopLang()->translate('获取虚拟商品信息失败！'));
        if ($this->request->isPost()) {
            $virtualGoodsId    = (int)$this->request->getPost('virtual_goods_id');
            $virtualGoodsArray = $this->getDbshopTable('VirtualGoodsTable')->infoVirtualGoods(array('virtual_goods_id'=>$virtualGoodsId));
            if(is_array($virtualGoodsArray[0]) and !empty($virtualGoodsArray[0])) {
                $virtualGoodsArray[0]['virtual_goods_end_time'] = $virtualGoodsArray[0]['virtual_goods_end_time'] == 0 ? '' : date("Y-m-d", $virtualGoodsArray[0]['virtual_goods_end_time']);
                $array = array('state'=>'true', 'goods'=>$virtualGoodsArray[0]);
            }
        }
        exit(json_encode($array));
    }
    /**
     * 虚拟商品删除
     */
    public function delVirtualGoodsAction()
    {
        if ($this->request->isPost()) {
            $virtualGoodsId = (int) $this->request->getPost('virtual_goods_id');
            $goodsId        = (int) $this->request->getPost('goods_id');
            if($virtualGoodsId != 0) {
                $delState = $this->getDbshopTable('VirtualGoodsTable')->delVirtualGoods(array('virtual_goods_id'=>$virtualGoodsId));

                //判断是否有补货
                $virtualGoodsNum = $this->getDbshopTable('VirtualGoodsTable')->countVirtualGoods(array('goods_id'=>$goodsId, 'virtual_goods_state'=>1));
                if($virtualGoodsNum == 0) $this->getDbshopTable()->oneUpdateGoods(array('virtual_goods_add_state'=>'0'), array('goods_id'=>$goodsId));

                if($delState) exit('true');
            }
        }
        exit($this->getDbshopLang()->translate('虚拟商品删除失败！'));
    }
    /**
     * 商品图片添加
     */
    public function addimageAction ()
    {
        $verifyToken = md5('unique_salt' . $this->request->getPost('timestamp'));
        if ($this->request->isPost() and $this->request->getPost('token') == $verifyToken) {
            $imageInfo  = $this->getServiceLocator()->get('shop_goods_upload')->goodsMoreImageUpload('Filedata');

            $imageInfo['image']       = $this->getServiceLocator()->get('shop_goods_upload')->goodsMoreImageYunStorage($imageInfo['image']);
            $imageInfo['thumb_image'] = $this->getServiceLocator()->get('shop_goods_upload')->goodsMoreImageYunStorage($imageInfo['thumb_image']);

            $imageArray = array();
            $imageArray['goods_title_image']     = $imageInfo['image'];
            $imageArray['goods_thumbnail_image'] = $imageInfo['thumb_image'];
            $imageArray['goods_watermark_image'] = $imageInfo['image'];
            $imageArray['goods_source_image']    = $imageInfo['image'];
            $imageArray['image_slide']           = 1;
            $imageArray['image_sort']            = 255;
            $imageArray['editor_session_str']    = $this->request->getPost('editSession');
            $imageArray['language']              = $this->getDbshopLang()->getLocale();
            
            $imageId = $this->getDbshopTable('GoodsImageTable')->addImage($imageArray);
            $YunType= (stripos($imageArray['goods_thumbnail_image'],'}/')!==false ? 'yun' : 'local');
            echo json_encode(array('image_id'=>$imageId,'yun_type'=>$YunType, 'image'=>basename($imageInfo['image']), 'image_name'=>$this->getServiceLocator()->get('frontHelper')->shopadminGoodsImage($imageInfo['thumb_image'])));
            // $this->request->getBasePath().$filePath;
        }
        exit();
    }
    /**
     * 获取编辑器上传的图片
     */
    public function getEditorImageAction()
    {
        if($this->request->isPost()) {
            $whereArray = array();

            $goodsId            = (int) $this->request->getPost('goods_id');
            $editorSessionStr   = trim($this->request->getPost('editor_session_str'));
            $imageId            = trim($this->request->getPost('image_id'));

            if($goodsId == 0) {
                $whereArray[] = 'editor_session_str="'.$editorSessionStr.'" and goods_id=0';
            } else {
                $whereArray[] = '(goods_id='.$goodsId.' or editor_session_str="'.$editorSessionStr.'")';
            }

            if(!empty($imageId)) {
                $imageIdStr = substr(str_replace('|', ',', $imageId), 0 , -1);
                $whereArray[] = 'goods_image_id NOT IN ('.$imageIdStr.')';
            }

            if(!empty($whereArray)) {
                $imageCla   = $this->getDbshopTable('GoodsImageTable')->listImage($whereArray);
                $imageArray = $imageCla->toArray();
                if(!empty($imageArray)) {
                    foreach($imageArray as $key => $value) {
                        $value['image_name'] = basename($value['goods_source_image']);
                        $value['checked']    = ($value['image_slide']==1 ? 'checked' : '');
                        $value['yun_type']   = (stripos($value['goods_thumbnail_image'],'}/')!==false ? 'yun' : 'local');
                        $value['goods_thumbnail_image'] = $this->getServiceLocator()->get('frontHelper')->shopadminGoodsImage($value['goods_thumbnail_image']);
                        $imageArray[$key] = $value;
                    }
                    echo json_encode($imageArray);
                    exit;
                }
            }
        }
        echo '';
        exit;
    }
    /**
     * 商品删除
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function delAction ()
    {
        if($this->request->isPost()) {
            $goodsId      = $this->request->getPost('goods_id');
            if(!is_array($goodsId)) $goodsId = array($goodsId);
            //为了操作记录使用
            $goodsArray   = $this->getDbshopTable()->listGoods(array('dbshop_goods.goods_id IN (' . implode(',', $goodsId) . ')'));
            $goodsName    = array();
            if(is_array($goodsArray) and !empty($goodsArray)) {
                foreach ($goodsArray as $goodsValue) {
                    $goodsName[] = $goodsValue['goods_name'];
                }
            }
            
            $goodsIdArray = $this->getDbshopTable()->delGoods($goodsId);
            if(is_array($goodsIdArray) and !empty($goodsIdArray)) {
                $this->getDbshopTable('GoodsExtendTable')->delGoodsExtend($goodsIdArray);
                $this->getDbshopTable('GoodsImageTable')->delGoodsImage($goodsIdArray);
                //二维码删除
                foreach($goodsIdArray as $goodsIdValue) {
                    $goodsQrCodefile = DBSHOP_PATH . '/public/upload/goods/qrcode/'.$goodsIdValue.'.png';
                    if(file_exists($goodsQrCodefile)) @unlink($goodsQrCodefile);
                }
                //评价删除
                $whereArray = array('goods_id IN (' . implode(',', $goodsIdArray) . ')');
                $this->getDbshopTable('GoodsCommentBaseTable')->delGoodsCommentBase($whereArray);
                $this->getDbshopTable('GoodsCommentTable')->delGoodsComment($whereArray);
                //商品咨询删除
                $this->getDbshopTable('GoodsAskTable')->delGoodsAsk($whereArray);
                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理商品'), 'operlog_info'=>$this->getDbshopLang()->translate('删除商品') . '&nbsp;' . @implode(',', $goodsName)));
                
            }
        }
        return $this->redirect()->toRoute('goods/default',array('controller'=>'goods'));
    }
    /**
     * 商品索引，用于前台模糊搜索使用
     * @return array
     */
    public function goodsIndexAction()
    {
        $array = array();

        $configRead = new Ini();
        $goodsConfig= $configRead->fromFile(DBSHOP_PATH . '/data/moduledata/System/goods/goods.ini');
        $array['goods_index'] = isset($goodsConfig['goods_index']) ? $goodsConfig['goods_index'] : '';

        if($this->request->isPost()) {
            $indexArray = $this->request->getPost()->toArray();

            $configWrite= new \Zend\Config\Writer\Ini();
            $goodsConfig['goods_index'] = (!empty($indexArray['goods_index']) ? $indexArray['goods_index'] : '');
            $configWrite->toFile(DBSHOP_PATH . '/data/moduledata/System/goods/goods.ini', $goodsConfig);

            $array['goods_index'] = $goodsConfig['goods_index'];
            $array['success_msg'] = $this->getDbshopLang()->translate('商品索引设置成功！');
        }

        $array['goods_count']       = $this->getDbshopTable()->countGoods(array());
        $array['goods_index_count'] = $this->getDbshopTable('GoodsIndexTable')->countGoodsIndex(array());

        return $array;
    }
    /**
     * 更新索引
     */
    public function updateGoodsIndexAction()
    {
        $goodsArray = $this->getDbshopTable()->listGoods();
        if(is_array($goodsArray) and !empty($goodsArray)) {
            foreach($goodsArray as $goodsValue) {
                $goodsExtendStr = '';
                //商品扩展中的颜色和尺寸
                $colorStr       = $this->getDbshopTable('GoodsPriceExtendColorTable')->goodsExtendColorStr(array('goods_id'=>$goodsValue['goods_id']));
                $goodsExtendStr .= $colorStr;
                $sizeStr        = $this->getDbshopTable('GoodsPriceExtendSizeTable')->goodsExtendSizeStr(array('goods_id'=>$goodsValue['goods_id']));
                $goodsExtendStr .= $sizeStr;
                //商品自定义
                $customStr      = $this->getDbshopTable('GoodsCustomTable')->goodsCustomStr(array('goods_id'=>$goodsValue['goods_id']));
                $goodsExtendStr .= $customStr;
                //商品标签
                $tagNameStr     = $this->getDbshopTable('GoodsTagInGoodsTable')->tagGoodsStr(array('dbshop_goods_tag_in_goods.goods_id'=>$goodsValue['goods_id']));
                $goodsExtendStr .= $tagNameStr;
                //商品属性
                $attributeArray = $this->getDbshopTable('GoodsInAttributeTable')->goodsInAttributeStr(array('dbshop_goods_in_attribute.goods_id'=>$goodsValue['goods_id']));
                if(!empty($attributeArray['attributestr'])) $goodsExtendStr .= $attributeArray['attributestr'];
                //如果商品属性中有 下拉、单选、多选等内容，进行如下操作
                if(!empty($attributeArray['attributevalueid'])) {
                    $valueStr = $this->getDbshopTable('GoodsAttributeValueExtendTable')->attributeValueStr(array('value_id IN ('.$attributeArray['attributevalueid'].')'));
                    $goodsExtendStr .= $valueStr;
                }

                $goodsValue['goods_body'] = $goodsValue['goods_name'] . $goodsExtendStr . $goodsValue['goods_extend_name'] . strip_tags($goodsValue['goods_body']);

                $indexGoodsArray = array();
                $indexGoodsArray['one_class_id']            = $goodsValue['one_class_id'];
                $indexGoodsArray['goods_state']             = $goodsValue['goods_state'];
                $indexGoodsArray['goods_shop_price']        = $goodsValue['goods_shop_price'];
                $indexGoodsArray['goods_name']              = $goodsValue['goods_name'];
                $indexGoodsArray['goods_extend_name']       = $goodsValue['goods_extend_name'];
                $indexGoodsArray['goods_thumbnail_image']   = $goodsValue['goods_thumbnail_image'];
                $indexGoodsArray['goods_click']             = $goodsValue['goods_click'];
                $indexGoodsArray['goods_add_time']          = $goodsValue['goods_add_time'];
                $indexGoodsArray['virtual_sales']           = $goodsValue['virtual_sales'];
                $indexGoodsArray['index_body']              = $goodsValue['goods_body'];

                $indexGoodsId = $this->getDbshopTable('GoodsIndexTable')->goodsIndexId(array('goods_id'=>$goodsValue['goods_id']));
                if($indexGoodsId > 0) {
                    if(!empty($goodsValue['one_class_id'])) $this->getDbshopTable('GoodsIndexTable')->updateGoodsIndex($indexGoodsArray, array('goods_id'=>$indexGoodsId));
                    else $this->getDbshopTable('GoodsIndexTable')->delGoodsIndex(array('goods_id'=>$indexGoodsId));
                } else {
                    $indexGoodsArray['goods_id']    = $goodsValue['goods_id'];
                    if(!empty($goodsValue['one_class_id'])) $this->getDbshopTable('GoodsIndexTable')->addGoodsIndex($indexGoodsArray);
                }
            }
            exit('true');
        } else exit($this->getDbshopLang()->translate('无需要更新的商品索引！'));
    }
    /**
     * 建立或者更新单个商品索引
     * @param array $data
     */
    private function goodsIndexCreateOrUpdate(array $data)
    {
        $goodsInfo = $this->getDbshopTable()->infoGoods(array('dbshop_goods.goods_id'=>$data['goods_id']));
        $goodsValue = array(
            'goods_id'              => $data['goods_id'],
            'goods_name'            => $goodsInfo->goods_name,
            'goods_extend_name'     => $goodsInfo->goods_extend_name,
            'one_class_id'          => $data['one_class_id'],
            'goods_state'           => $goodsInfo->goods_state,
            'goods_body'            => $goodsInfo->goods_body,
            'goods_shop_price'      => $goodsInfo->goods_shop_price,
            'goods_thumbnail_image' => $goodsInfo->goods_thumbnail_image,
            'goods_click'           => $goodsInfo->goods_click,
            'virtual_sales'         => $goodsInfo->virtual_sales,
            'goods_add_time'        => $goodsInfo->goods_add_time
            );

        $goodsExtendStr = '';
        //商品扩展中的颜色和尺寸
        $colorStr       = $this->getDbshopTable('GoodsPriceExtendColorTable')->goodsExtendColorStr(array('goods_id'=>$goodsValue['goods_id']));
        $goodsExtendStr .= $colorStr;
        $sizeStr        = $this->getDbshopTable('GoodsPriceExtendSizeTable')->goodsExtendSizeStr(array('goods_id'=>$goodsValue['goods_id']));
        $goodsExtendStr .= $sizeStr;
        //商品自定义
        $customStr      = $this->getDbshopTable('GoodsCustomTable')->goodsCustomStr(array('goods_id'=>$goodsValue['goods_id']));
        $goodsExtendStr .= $customStr;
        //商品标签
        $tagNameStr     = $this->getDbshopTable('GoodsTagInGoodsTable')->tagGoodsStr(array('dbshop_goods_tag_in_goods.goods_id'=>$goodsValue['goods_id']));
        $goodsExtendStr .= $tagNameStr;
        //商品属性
        $attributeArray = $this->getDbshopTable('GoodsInAttributeTable')->goodsInAttributeStr(array('dbshop_goods_in_attribute.goods_id'=>$goodsValue['goods_id']));
        if(!empty($attributeArray['attributestr'])) $goodsExtendStr .= $attributeArray['attributestr'];
        //如果商品属性中有 下拉、单选、多选等内容，进行如下操作
        if(!empty($attributeArray['attributevalueid'])) {
            $valueStr = $this->getDbshopTable('GoodsAttributeValueExtendTable')->attributeValueStr(array('value_id IN ('.$attributeArray['attributevalueid'].')'));
            $goodsExtendStr .= $valueStr;
        }

        $goodsValue['goods_body'] = $goodsValue['goods_name'] . $goodsExtendStr . $goodsValue['goods_extend_name'] . strip_tags($goodsValue['goods_body']);

        $indexGoodsArray = array();
        $indexGoodsArray['one_class_id']            = $goodsValue['one_class_id'];
        $indexGoodsArray['goods_state']             = $goodsValue['goods_state'];
        $indexGoodsArray['goods_shop_price']        = $goodsValue['goods_shop_price'];
        $indexGoodsArray['goods_name']              = $goodsValue['goods_name'];
        $indexGoodsArray['goods_extend_name']       = $goodsValue['goods_extend_name'];
        $indexGoodsArray['goods_thumbnail_image']   = $goodsValue['goods_thumbnail_image'];
        $indexGoodsArray['goods_click']             = $goodsValue['goods_click'];
        $indexGoodsArray['goods_add_time']          = $goodsValue['goods_add_time'];
        $indexGoodsArray['index_body']              = $goodsValue['goods_body'];

        $indexGoodsId = $this->getDbshopTable('GoodsIndexTable')->goodsIndexId(array('goods_id'=>$goodsValue['goods_id']));
        if($indexGoodsId > 0) {
            if(!empty($goodsValue['one_class_id'])) $this->getDbshopTable('GoodsIndexTable')->updateGoodsIndex($indexGoodsArray, array('goods_id'=>$indexGoodsId));
            else $this->getDbshopTable('GoodsIndexTable')->delGoodsIndex(array('goods_id'=>$indexGoodsId));
        } else {
            $indexGoodsArray['goods_id']    = $goodsValue['goods_id'];
            if(!empty($goodsValue['one_class_id'])) $this->getDbshopTable('GoodsIndexTable')->addGoodsIndex($indexGoodsArray);
        }
    }
    /** 
     * 添加关联商品操作
     */
    public function addRelatedGoodsAction()
    {
        $goodsId        = (int) $this->request->getPost('goods_id');
        $relatedGoodsId = (int) $this->request->getPost('related_goods_id');
        if($relatedGoodsId != 0) {
            $relatedInfo = $this->getDbshopTable('GoodsRelatedTable')->infoRelatedGoods(array('dbshop_goods_related.goods_id'=>$goodsId, 'dbshop_goods_related.related_goods_id'=>$relatedGoodsId, 'e.language'=>$this->getDbshopLang()->getLocale()));
            if(!empty($relatedInfo)) exit(json_encode(array('state'=>'have')));
            $relatedGoodsArray = array();
            $relatedGoodsArray['goods_id']         = $goodsId;
            $relatedGoodsArray['related_goods_id'] = $relatedGoodsId;
            $relatedGoodsArray['related_sort']     = 255;
            $this->getDbshopTable('GoodsRelatedTable')->addRelatedGoods($relatedGoodsArray);
            $relatedInfo = $this->getDbshopTable('GoodsRelatedTable')->infoRelatedGoods(array('dbshop_goods_related.goods_id'=>$goodsId, 'dbshop_goods_related.related_goods_id'=>$relatedGoodsId, 'e.language'=>$this->getDbshopLang()->getLocale()));
            
            exit(json_encode(array(
                    'state'=>'true',
                    'goods_id'=>$goodsId,
                    'related_id'=>$relatedInfo->related_id,
                    'related_goods_id'=>$relatedInfo->related_goods_id,
                    'goods_item'=>$relatedInfo->goods_item,
                    'goods_name'=>$relatedInfo->goods_name,
                    'goods_shop_price'=>$relatedInfo->goods_shop_price,
                    'goods_state'=>$relatedInfo->goods_state == 1 ? $this->getDbshopLang()->translate('上架') : $this->getDbshopLang()->translate('下架')
            )));
        }
        exit(json_encode(array('state'=>'false')));
    }
    /** 
     * 删除关联商品操作
     */
    public function delRelatedGoodsAction()
    {
        $relatedId = (int) $this->request->getPost('related_id');
        if($relatedId != 0) {
            $this->getDbshopTable('GoodsRelatedTable')->delRelatedGoods(array('related_id'=>$relatedId));
            exit('true');
        }
        exit('false');
    }
    /** 
     * 添加组合商品操作
     */
    public function addCombinationGoodsAction()
    {
        $goodsId            = (int) $this->request->getPost('goods_id');
        $combinationGoodsId = (int) $this->request->getPost('combination_goods_id');
        if($combinationGoodsId != 0) {
            $combinationInfo = $this->getDbshopTable('GoodsCombinationTable')->infoCombinationGoods(array('dbshop_goods_combination.goods_id'=>$goodsId, 'dbshop_goods_combination.combination_goods_id'=>$combinationGoodsId, 'e.language'=>$this->getDbshopLang()->getLocale()));
            if(!empty($combinationInfo)) exit(json_encode(array('state'=>'have')));
            $combinationGoodsArray = array();
            $combinationGoodsArray['goods_id']         = $goodsId;
            $combinationGoodsArray['combination_goods_id'] = $combinationGoodsId;
            $combinationGoodsArray['combination_sort']     = 255;
            $this->getDbshopTable('GoodsCombinationTable')->addCombinationGoods($combinationGoodsArray);
            $combinationInfo = $this->getDbshopTable('GoodsCombinationTable')->infoCombinationGoods(array('dbshop_goods_combination.goods_id'=>$goodsId, 'dbshop_goods_combination.combination_goods_id'=>$combinationGoodsId, 'e.language'=>$this->getDbshopLang()->getLocale()));
        
            exit(json_encode(array(
                    'state'=>'true',
                    'goods_id'=>$goodsId,
                    'combination_id'=>$combinationInfo->combination_id,
                    'combination_goods_id'=>$combinationInfo->combination_goods_id,
                    'goods_item'=>$combinationInfo->goods_item,
                    'goods_name'=>$combinationInfo->goods_name,
                    'goods_shop_price'=>$combinationInfo->goods_shop_price,
                    'goods_state'=>$combinationInfo->goods_state == 1 ? $this->getDbshopLang()->translate('上架') : $this->getDbshopLang()->translate('下架')
            )));
        }
        exit(json_encode(array('state'=>'false')));        
    }
    /**
     * 删除组合商品操作
     */
    public function delCombinationGoodsAction()
    {
        $combinationId = (int) $this->request->getPost('combination_id');
        if($combinationId != 0) {
            $this->getDbshopTable('GoodsCombinationTable')->delCombinationGoods(array('combination_id'=>$combinationId));
            exit('true');
        }
        exit('false');
    }
    /**
     * 自动填充的商品检索输出
     */
    public function autocompleteGoodsSearchAction ()
    {
        $keyword = trim($this->request->getQuery('q'));
        
        $goodsList = '';
        if(!empty($keyword)) {
            $goodsArray = $this->getDbshopTable()->listGoods(array('e.goods_name like \'%'.$keyword.'%\''), array('dbshop_goods.goods_id DESC'), 20);
            if(is_array($goodsArray) and !empty($goodsArray)) {
                foreach ($goodsArray as $goodsValue) {
                    $goodsList .= $goodsValue['goods_name'].($goodsValue['goods_state'] == 2 ? '['.$this->getDbshopLang()->translate('已下架').']' : '').'|'.$goodsValue['goods_id']."\n";
                }
            }
        }
        exit($goodsList);
    }
    /**
     * 商品列表的批量编辑
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function editallAction ()
    {
        if($this->request->isPost()) {
            $allEdit = $this->request->getPost('allEdit');
            switch ($allEdit) {
                case 'del':
                    $this->delAction();
                    break;
                case 'editState':
                    $this->allEditState();
                    break;
            }
        }

        //用于返回对应的分页数
        $page = $this->params('page',1);
        $array['page']     = $page;
        $array['query']= $this->request->getQuery()->toArray();

        return $this->redirect()->toRoute('goods/default/page',array('controller'=>'goods', 'page'=>$array['page']), array('query'=>$array['query']));
    }
    private function allEditState()
    {
        if($this->request->isPost()) {
            $goodId = $this->request->getPost('goods_id');
            $state  = $this->request->getPost('editstate');
            $this->getDbshopTable()->oneUpdateGoods(array('goods_state'=>$state), array('goods_id'=>$goodId));
        }
    }
    /**
     * 商品图片编辑
     */
    public function updateimageAction ()
    {
        $goodsImageArray = $this->request->getPost()->toArray(); 

        $array = array();
        if(isset($goodsImageArray['image_sort']) and !empty($goodsImageArray['image_sort'])) $array['image_sort'] = $goodsImageArray['image_sort'];
        if(isset($goodsImageArray['image_slide_value']) and !empty($goodsImageArray['image_slide_value'])) $array['image_slide'] = $goodsImageArray['image_slide_value'];

        if($goodsImageArray['goods_image_id'] != 0 and !empty($array)) {
            $this->getDbshopTable('GoodsImageTable')->updateOneImage($array, array(
                'goods_image_id=' . $goodsImageArray['goods_image_id']
            ));
            echo 'true';
        }
        exit();
    }

    /**
     * ajax删除商品图片操作
     */
    public function delimageAction ()
    {
        $goodsImageId = intval($this->request->getPost('goods_image_id'));
        if ($this->getDbshopTable('GoodsImageTable')->delImage($goodsImageId)) {
            echo 'true';
        } else {
            echo 'false';
        }
        exit();
    }
    /**
     * 清理无用的商品图片，上传到数据库中未被任何商品使用到的图片
     */
    public function clearGoodsImageAction()
    {
        $array = array();

        if($this->request->isPost()) {
            $getArray = $this->request->getPost()->toArray();
            $getArray['goods_image_id'] = isset($getArray['goods_image_id']) ? intval($getArray['goods_image_id']) : 0;

            if($getArray['goods_image_id'] != 0) {
                $imageInfo = $this->getDbshopTable('GoodsImageTable')->infoImage(array('goods_image_id'=>$getArray['goods_image_id']));
                //判断是否为goods_id等于0的图片，如果不是，不予删除
                if(isset($imageInfo->goods_id) and $imageInfo->goods_id == 0) {
                    $this->getDbshopTable('GoodsImageTable')->delImage($getArray['goods_image_id']);
                    exit('true');
                }
            }
        }

        $array['clear_goods_image'] = $this->getDbshopTable('GoodsImageTable')->listImage(array('goods_id=0'))->toArray();

        return $array;
    }
    /** 
     * 获取商品属性输入html
     */
    public function attributeInputAction ()
    {
        $attributeGroupId = intval($this->request->getPost('attribute_group_id'));
        $goodsId          = intval($this->request->getPost('goods_id'));
        if($attributeGroupId == 0) exit();
        echo $this->getAttributeHtml($attributeGroupId, $goodsId);
        exit();
    }
    /** 
     * 对属性解析html
     * @param unknown $attributeGroupId
     * @return string
     */
    private function getAttributeHtml($attributeGroupId, $goodsId='')
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
        
        $html = '';
        if(is_array($attributeArray) and !empty($attributeArray)) {
            foreach ($attributeArray as $a_value) {
                if(!isset($valueArray[$a_value['attribute_id']]) and !in_array($a_value['attribute_type'], array('textarea', 'input'))) continue;
                
                $html .= '<div class="control-group">';
                $html .= '<label for="input01" class="control-label">' . $a_value['attribute_name'] . ':</label>';
                $html .= '<div class="controls"><INPUT type="hidden" name="attribute_type[' .$a_value['attribute_id']. ']" value="' . $a_value['attribute_type'] . '" />';
                
                switch ($a_value['attribute_type']) {
                    case 'select'://下拉菜单
                        if(isset($valueArray[$a_value['attribute_id']]) and is_array($valueArray[$a_value['attribute_id']]) and !empty($valueArray[$a_value['attribute_id']])) {
                            $html .= '<select name="attribute_value[' .$a_value['attribute_id']. ']" class="span2">';
                            $selected = 'selected="selected"';
                            foreach ($valueArray[$a_value['attribute_id']] as $select_key => $select_value) {
                                $html .= '<option value="' .$select_key. '" '.((isset($goodsInAttribute[$a_value['attribute_id']]) and $goodsInAttribute[$a_value['attribute_id']] == $select_key) ? $selected : '').'>' .$select_value. '</option>';
                            }
                            $html .= '</select>';
                        }
                        break;
                    case 'radio'://单选菜单
                        if(isset($valueArray[$a_value['attribute_id']]) and is_array($valueArray[$a_value['attribute_id']]) and !empty($valueArray[$a_value['attribute_id']])) {
                            $checked = 'checked="checked"';
                            foreach ($valueArray[$a_value['attribute_id']] as $select_key => $select_value) {
                                $html .= '<label class="radio inline"><input type="radio" value="' . $select_key . '" '.((isset($goodsInAttribute[$a_value['attribute_id']]) and $goodsInAttribute[$a_value['attribute_id']] == $select_key) ? $checked : '').' name="attribute_value[' .$a_value['attribute_id']. ']">' .$select_value. '</label>';
                            }
                        }
                        break;
                    case 'checkbox'://复选菜单
                        if(isset($valueArray[$a_value['attribute_id']]) and is_array($valueArray[$a_value['attribute_id']]) and !empty($valueArray[$a_value['attribute_id']])) {
                            
                            $checkboxChecked = array();
                            if(isset($goodsInAttribute[$a_value['attribute_id']]) and $goodsInAttribute[$a_value['attribute_id']] != '') {
                                $checkboxChecked = explode(',', $goodsInAttribute[$a_value['attribute_id']]);
                            }
                            
                            foreach ($valueArray[$a_value['attribute_id']] as $select_key => $select_value) {
                                $html .= '<label class="checkbox inline"><input type="checkbox" ' . (@in_array($select_key, $checkboxChecked) ? 'checked="checked"' : '') . ' value="' . $select_key . '" name="attribute_value[' .$a_value['attribute_id']. '][]">' .$select_value. '</label>';
                            }
                        }                        
                        break;
                    case 'input'://输入表单
                        $html .= '<input type="text" class="span6" name="attribute_value[' .$a_value['attribute_id']. ']" value="' .(isset($goodsInAttribute[$a_value['attribute_id']]) ? $goodsInAttribute[$a_value['attribute_id']] : ''). '" >';
                        break;
                    case 'textarea'://文本域表单
                        $html .= '<textarea rows="3" class="span6" name="attribute_value[' .$a_value['attribute_id']. ']">'. (isset($goodsInAttribute[$a_value['attribute_id']]) ? $goodsInAttribute[$a_value['attribute_id']] : '') .'</textarea>';
                        break;     
                }
                $html .= '</div></div>';
            }
        }
        return $html;
    }
    /** 
     * 对于提交商品编辑和添加表单时，对于属性值进行提取
     * @param unknown $attributeType
     * @param unknown $postValue
     * @return string|Ambigous <string, unknown>
     */
    private function getAttributeInputValue($attributeType, $postValue)
    {
        if(empty($postValue)) return '';
        
        $value = '';
        switch ($attributeType) {
            case 'checkbox':
                $value = implode(',', $postValue);
                break;
                default:
                $value = $postValue;    
                break;
        }
        return $value;
    }
    /** 
     * 获取将要插入商品扩展分类表提取分类状态
     * @param array $classIdArray
     * @return multitype:unknown
     */
    private function getGoodsInClassState(array $classIdArray=array())
    {
        $classStateArray = array();
        if(is_array($classIdArray) and !empty($classIdArray)) {
            $classList = $this->getDbshopTable('GoodsClassTable')->selectGoodsClass('class_id IN ('. implode(',', $classIdArray) .')');
            foreach ($classList as $value) {
                $classStateArray[$value['class_id']] = $value['class_state'];
            }
        }
        return $classStateArray;
    }
    /**
     * 数据表调用
     * @param string $tableName            
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'GoodsTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}
