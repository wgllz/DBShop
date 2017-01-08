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

use Admin\Service\DbshopOpcache;
use Zend\Config\Writer\PhpArray;
use Zend\View\Model\ViewModel;
use Admin\Controller\BaseController;

class ClassController extends BaseController
{
    /**
     * 商品分类列表
     * 
     */
    public function indexAction ()
    {
        $array = array();
        //商品分类
        $array['goods_class'] = $this->classlistfunAction();
        
        return $array;
    }
    /**
     * 添加商品分类
     */
    public function addAction ()
    {
        $array = array();
        if($this->request->isPost()) {
            //接收POST数据
            $classArray               = $this->request->getPost()->toArray();
            $classArray['class_path'] = $classArray['class_top_id'];
            //分类图片上传
            /*
            $imageIcon = $this->getServiceLocator()->get('shop_goods_upload')->classIcoUpload('class_icon');
            if(!empty($imageIcon['image'])) $classArray['class_icon'] = $imageIcon['image'];
            
            $imageClass = $this->getServiceLocator()->get('shop_goods_upload')->classImageUpload('class_image');
            if(!empty($imageClass['image'])) $classArray['class_image'] = $imageClass['image'];
            */

            //可批量添加分类
            $classTitleArray = explode("\r\n", $classArray['class_name']);
            if(is_array($classTitleArray) and !empty($classTitleArray)) {
                foreach($classTitleArray as $titleValue) {
                    if(!empty($titleValue)) {
                        //分类信息添加
                        $classArray['class_name'] = $titleValue;
                        $classId = $this->getDbshopTable()->addGoodsClass($classArray);
                        //分类标签组保存
                        if(isset($classArray['tag_group_id']) and is_array($classArray['tag_group_id']) and !empty($classArray['tag_group_id'])) {
                            $this->getDbshopTable('GoodsClassShowTable')->addGoodsClassTagGroup(array('class_id'=>$classId, 'show_body'=>serialize($classArray['tag_group_id'])));
                        }
                    }
                }
            }

            //生成的商品分类数组文件
            //$this->createClassFile();
            //对前台生成的商品分类数组文件进行删除
            $this->delClassFile();

            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理分类'), 'operlog_info'=>$this->getDbshopLang()->translate('添加分类') . '&nbsp;' . implode(' ', $classTitleArray)));
             
            //跳转处理
            return $this->redirect()->toRoute('class/default',array('controller'=>'class'));
        }
        //分类列表，用于编辑上级分类
        $array['goods_class']  = $this->classlistfunAction();
        //商品标签组
        $tagArray   = $this->getDbshopTable('GoodsTagTable')->listGoodsTag(array('e.language'=>$this->getDbshopLang()->getLocale()), array('dbshop_goods_tag.tag_group_id ASC', 'dbshop_goods_tag.tag_sort ASC', 'dbshop_goods_tag.tag_id ASC'));
        $array['goods_tag'] = array();
        $array['goods_tag_group'] = array();
        if(is_array($tagArray) and !empty($tagArray)) {
            foreach ($tagArray as $tag_value) {
                if($tag_value['tag_group_id'] == 0) continue;
                $array['goods_tag'][$tag_value['tag_group_id']][] = array('tag_id'=>$tag_value['tag_id'],'tag_name'=>$tag_value['tag_name']);
                $array['goods_tag_group'][$tag_value['tag_group_id']] = (!empty($tag_value['tag_group_mark']) ? '<strong>['.$tag_value['tag_group_mark'].']</strong>' : '').$tag_value['tag_group_name'];
            }
        }
        //添加子分类时，使用接收
        $array['top_class_id'] = (int) $this->params('top_class_id', 0);

        return $array;
    }
    /**
     * 编辑商品分类
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:NULL \Goods\Controller\unknown
     */
    public function editAction ()
    {
        $classId = (int) $this->params('class_id', 0);
        if(!$classId) {
            return $this->redirect()->toRoute('class/default',array('controller'=>'class'));
        }
        
        $array = array();
        if($this->request->isPost()) {
            $classArray = $this->request->getPost()->toArray();
            $classArray['class_path'] = $classArray['class_top_id'];
            
            /*分类图片上传
            $imageIcon = $this->getServiceLocator()->get('shop_goods_upload')->classIcoUpload('class_icon', (isset($classArray['old_class_icon']) ? $classArray['old_class_icon'] : ''));
            $classArray['class_icon'] = $imageIcon['image'];

            $imageClass = $this->getServiceLocator()->get('shop_goods_upload')->classImageUpload('class_image', (isset($classArray['old_class_image']) ? $classArray['old_class_image'] : ''));
            $classArray['class_image'] = $imageClass['image'];*/
            
            //分类信息编辑
            $this->getDbshopTable()->editGoodsClass($classArray,array('class_id'=>$classId));
            //分类中的产品批量编辑
            if(isset($classArray['goods_id']) and is_array($classArray['goods_id']) and !empty($classArray['goods_id']) and $classArray['class_goods_editall'] != '') {
                //删除
                if($classArray['class_goods_editall'] == 'del') $this->getDbshopTable('GoodsInClassTable')->delGoodsInClass(array('goods_id IN ('.implode(',',$classArray['goods_id']).')', 'class_id='.$classArray['class_id']));
                //更新排序
                if($classArray['class_goods_editall'] == 'update') {
                    foreach ($classArray['goods_id'] as $classGoodsId) {
                        $this->getDbshopTable('GoodsInClassTable')->updateGoodsInClass(array('class_goods_sort'=>intval($classArray['class_goods_sort'][$classGoodsId])), array('class_id'=>$classArray['class_id'], 'goods_id'=>$classGoodsId));
                    }
                }
                //添加分类商品推荐
                if($classArray['class_goods_editall'] == 'recommend') {
                    foreach ($classArray['goods_id'] as $classGoodsId) {
                        $this->getDbshopTable('GoodsInClassTable')->updateGoodsInClass(array('class_recommend'=>1), array('class_id'=>$classArray['class_id'], 'goods_id'=>$classGoodsId));
                    }                    
                }
                //取消分类商品推荐
                if($classArray['class_goods_editall'] == 'cancel_recommend') {
                    foreach ($classArray['goods_id'] as $classGoodsId) {
                        $this->getDbshopTable('GoodsInClassTable')->updateGoodsInClass(array('class_recommend'=>0), array('class_id'=>$classArray['class_id'], 'goods_id'=>$classGoodsId));
                    }                
                }                
            }
            //分类标签组保存
            if(isset($classArray['tag_group_id']) and is_array($classArray['tag_group_id']) and !empty($classArray['tag_group_id'])) {
                $this->getDbshopTable('GoodsClassShowTable')->eidtGoodsClassTagGroup(array('class_id'=>$classId, 'show_body'=>serialize($classArray['tag_group_id'])), array('class_id'=>$classId));
            } else $this->getDbshopTable('GoodsClassShowTable')->delGoodsClassTagGroup(array('class_id'=>$classId));

            //商品插入扩展分类状态修改
            $this->getDbshopTable('GoodsInClassTable')->updateGoodsInClass(array('class_state'=>$classArray['class_state']), array('class_id'=>$classArray['class_id']));
            //生成的商品分类数组文件
            //$this->createClassFile();
            //对前台生成的商品分类数组文件进行删除
            $this->delClassFile();

            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理分类'), 'operlog_info'=>$this->getDbshopLang()->translate('更新分类') . '&nbsp;' . $classArray['class_name']));
            
            //跳转
            if($classArray['class_save_type'] != 'save_return_edit') {
                return $this->redirect()->toRoute('class/default',array('controller'=>'class'));
            }
            $array['success_msg'] = $this->getDbshopLang()->translate('商品分类编辑成功！');
        }
        //分类基本信息输出模板
        $array['class_info']  = $this->getDbshopTable()->infoGoodsClass(array('class_id'=>$classId));
        //分类列表，用于编辑上级分类
        $array['goods_class'] = $this->classlistfunAction();
        //商品标签组
        $tagArray   = $this->getDbshopTable('GoodsTagTable')->listGoodsTag(array('e.language'=>$this->getDbshopLang()->getLocale()), array('dbshop_goods_tag.tag_group_id ASC', 'dbshop_goods_tag.tag_sort ASC', 'dbshop_goods_tag.tag_id ASC'));
        $array['goods_tag'] = array();
        $array['goods_tag_group'] = array();
        if(is_array($tagArray) and !empty($tagArray)) {
            foreach ($tagArray as $tag_value) {
                if($tag_value['tag_group_id'] == 0) continue;
                $array['goods_tag'][$tag_value['tag_group_id']][] = array('tag_id'=>$tag_value['tag_id'],'tag_name'=>$tag_value['tag_name']);
                $array['goods_tag_group'][$tag_value['tag_group_id']] = (!empty($tag_value['tag_group_mark']) ? '<strong>['.$tag_value['tag_group_mark'].']</strong>' : '').$tag_value['tag_group_name'];
            }
        }
        $array['class_tag_group'] = $this->getDbshopTable('GoodsClassShowTable')->arrayGoodsClassTagGroup(array('class_id'=>$classId));

        return $array;
    }
    /**
     * ajax获取分类对应的商品
     * @return Ambigous <\Zend\View\Model\ViewModel, \Zend\View\Model\ViewModel>
     */
    public function ajaxgoodsAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        
        $searchArray = array();
        $classId     = (int) $this->params('class_id', 0);
        if($classId != 0) $searchArray['class_id'] = $classId;
        
        //商品分页
        $page = $this->params('page',1);
        $array['goods_list'] = $this->getDbshopTable('GoodsTable')->goodsPageList(array('page'=>$page, 'page_num'=>20), $searchArray,array('goods_in_class'=>true), array('goods_in.class_recommend DESC', 'goods_in.class_goods_sort ASC', 'goods_in.goods_id DESC'));
        
        $array['class_id']    = $classId;
        $array['show_div_id'] = $this->request->getQuery('show_div_id');
        
        //商品属性组
        $array['attribute_group'] = $this->getDbshopTable('GoodsAttributeGroupTable')->listAttributeGroup(array('e.language'=>$this->getDbshopLang()->getLocale()));
        
        return $viewModel->setVariables($array);
    }
    /**
     * 商品分类删除
     */
    public function delAction ()
    {
        $classId = intval($this->request->getPost('class_id'));
        if($classId) {
            //为了记录操作日志
            $classInfo = $this->getDbshopTable()->infoGoodsClass(array('class_id'=>$classId));
            
            //分类信息删除
            if($this->getDbshopTable()->delGoodsClass(array('class_id'=>$classId))) {
                //分类中包含的商品删除
                $this->getDbshopTable('GoodsInClassTable')->delGoodsInClass(array('class_id'=>$classId));
                //分类中包含的商品标签组
                $this->getDbshopTable('GoodsClassShowTable')->delGoodsClassTagGroup(array('class_id'=>$classId));
                //生成的商品分类数组文件
                //$this->createClassFile();
                //对前台生成的商品分类数组文件进行删除
                $this->delClassFile();

                //记录操作日志
                $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('管理分类'), 'operlog_info'=>$this->getDbshopLang()->translate('删除分类') . '&nbsp;' . $classInfo->class_name));
                
                //跳转
                return $this->redirect()->toRoute('class/default',array('controller'=>'class'));
            }
            echo 'false';
            exit;
        }
    }
    /**
     * 分类批量修改
     * @return \Zend\Http\Response
     */
    public function allUpdateAction()
    {
        if($this->request->isPost()) {
            $classArray  = $this->request->getPost()->toArray();
            if(is_array($classArray) and !empty($classArray)) {
                foreach($classArray['class_sort'] as $key => $value) {
                    $this->getDbshopTable()->updateGoodsCalss(array('class_sort'=>$value), array('class_id'=>$key));
                }
                //生成的商品分类数组文件
                //$this->createClassFile();
                //对前台生成的商品分类数组文件进行删除
                $this->delClassFile();

            }
        }
        //跳转处理
        return $this->redirect()->toRoute('class/default',array('controller'=>'class'));
    }
    /**
     * 商品分类图片删除
     */
    public function delimageAction ()
    {
        $classId   = $this->request->getPost('class_id');
        $image     = $this->request->getPost('del_image');
        $classInfo = $this->getDbshopTable()->infoGoodsClass(array('class_id'=>$classId));
        if($classInfo) {
            @unlink(DBSHOP_PATH . $classInfo->$image);
            $this->getDbshopTable()->editGoodsClass(array($image=>'del'),array('class_id'=>$classId));
            
            echo 'true';
        } else {
            echo 'false';
        }
        exit();
    }
    /**
     * 检查分类的可设置性
     */
    public function checkclasstopAction ()
    {
        $classId     = (int)$this->params('class_id');
        $classTopId = intval($this->request->getPost('class_top_id'));
        if($classTopId == $classId or $classId == 0) {//上级分类不能与当前分类相同
            echo 'false';
            exit();
        }
        //当前分类，不能将上级分类设置为当前分类子类
        $sun_class    = $this->getDbshopTable()->getSunClassId($classId);
        if(in_array($classTopId,$sun_class)) echo 'false';
        else echo 'true';
        exit;
    }
    /**
     * 调用商品分类
     * @return unknown
     */
    public function classlistfunAction ()
    {
        $classArray = $this->getDbshopTable()->listGoodsClass();
        $classArray = $this->getDbshopTable()->classOptions(0,$classArray);
        return $classArray;
    }
    /**
     * 删除商品分类文件
     */
    private function delClassFile()
    {
        $arrayGoodsClassFile = DBSHOP_PATH . '/data/moduledata/Shopfront/goodsClass.php';
        if(file_exists($arrayGoodsClassFile)) unlink($arrayGoodsClassFile);
        //缓存处理
        DbshopOpcache::invalidate($arrayGoodsClassFile);
    }
    /**
     * 创建商品分类文件(暂时不使用)
     * @throws \Exception
     */
    private function createClassFile()
    {
        //商品分类
        $array['goods_class']  = $this->getDbshopTable('GoodsClassTable')->classOptions(0,$this->getDbshopTable('GoodsClassTable')->listGoodsClass());
        if(is_array($array['goods_class']) and !empty($array['goods_class'])) {
            foreach ($array['goods_class'] as $key => $val) {
                if($val['class_top_id'] != 0 and substr_count($val['class_path'], ',') == 1) {
                    $array['goods_class'][$val['class_top_id']]['sub_class'][] = $val;
                    unset($array['goods_class'][$key]);
                } elseif ($val['class_top_id'] != 0) unset($array['goods_class'][$key]);
            }
        }
        $classArray = empty($array['goods_class']) ? array() : $array['goods_class'];
        $arrayWrite = new PhpArray();
        $arrayWrite->toFile(DBSHOP_PATH . '/data/moduledata/Shopfront/goodsClass.php', $classArray);
        //缓存处理
        DbshopOpcache::invalidate(DBSHOP_PATH . '/data/moduledata/Shopfront/goodsClass.php');
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'GoodsClassTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}