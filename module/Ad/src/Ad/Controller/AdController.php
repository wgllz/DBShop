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

namespace Ad\Controller;

use Admin\Controller\BaseController;

class AdController extends BaseController
{
    /** 
     * 广告管理首页
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction ()
    {
        $array = array();
        
        //获取模板中对于广告位置和类型的设置
        $array['ad_class']  = $this->readerAdIni();
        if(is_array($array['ad_class']) and !empty($array['ad_class'])) {//获取广告类别下面添加的广告数量，在类别不是非常多的情况下，循环执行sql效率问题可以忽略不计
            foreach ($array['ad_class']['class'] as $value) {
                $array['ad_class']['adcount'][$value] = $this->getDbshopTable()->classAdCount(array('ad_class'=>$value, 'template_ad'=>DBSHOP_TEMPLATE, 'show_type'=>'pc'));
            }
        }
        return $array;
    }
    /** 
     * 广告设置页面
     * @return multitype:
     */
    public function setadAction ()
    {
        $array  = array();
        $adType = $this->params('ad_type');
        
        $array['ad_type'] = $adType;
        
        //获取模板中对于广告位置和类型的设置
        $adIni = $this->readerAdIni();
        if(!isset($adIni['classname'][$adType])) return $this->redirect()->toRoute('ad/default');
        $array['set_ad_class_name']  = $adIni['classname'][$adType];
        //广告位置
        $array['place'] = (isset($adIni[$adType]['place']) ? $adIni[$adType]['place'] : array());
        
        //广告列表
        $array['ad_list'] = $this->getDbshopTable()->listAd(array('ad_class'=>$adType, 'template_ad'=>DBSHOP_TEMPLATE, 'show_type'=>'pc'));
        
        return $array;
    }
    /**
     * 添加广告
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:NULL unknown
     */
    public function addAction ()
    {
        $array  = array();
        $adType = $this->params('ad_type');
        
        $array['ad_type'] = $adType;
        
        //获取模板中对于广告位置和类型的设置
        $adIni = $this->readerAdIni();
        if(!isset($adIni['classname'][$adType])) return $this->redirect()->toRoute('ad/default');
        $array['set_ad_class_name']  = $adIni['classname'][$adType];

        //广告位置获取
        $array['ad_place'] = (isset($adIni[$adType]['place']) ? $adIni[$adType]['place'] : array());
        
        //添加广告
        if($this->request->isPost()) {
            $adArray = $this->request->getPost()->toArray();
            $adArray['ad_class']    = $adType;
            $adArray['template_ad'] = DBSHOP_TEMPLATE;
            if(!in_array($adArray['ad_type'],array('image','slide'))) {
                $adArray['ad_body']   = $adArray['ad_'.$adArray['ad_type']];
            } else {
                $adArray['ad_width']  = $adIni[$adType]['size'][$adArray['ad_place'] . '_image_width'];
                $adArray['ad_height'] = $adIni[$adType]['size'][$adArray['ad_place'] . '_image_height'];
            }
            //广告基础内容插入
            $adId    = $this->getDbshopTable()->addAd($adArray);
            //图片广告处理
            if($adArray['ad_type'] == 'image') {
                $adImage = $this->getServiceLocator()->get('shop_other_upload')->adImageUpload('ad_image', '', $adIni[$adType]['size'][$adArray['ad_place'] . '_image_width'], $adIni[$adType]['size'][$adArray['ad_place'] . '_image_height'], $adId);
                if($adImage['image'] != '')$this->getDbshopTable()->updateAd(array('ad_body'=>$adImage['image'], 'ad_start_time'=>$adArray['ad_start_time'], 'ad_end_time'=>$adArray['ad_end_time']), array('ad_id'=>$adId));
            }
            
            //幻灯片广告处理
            if($adArray['ad_type'] == 'slide') {
                for($i=1; $i<=5; $i++) {
                    if($_FILES['ad_slide_image_' . $i]['name'] != '') {
                        $slideArray = array();
                        $slideImage = array();
                        $slideImage = $this->getServiceLocator()->get('shop_other_upload')->adImageUpload('ad_slide_image_' . $i, '', $adIni[$adType]['size'][$adArray['ad_place'] . '_image_width'], $adIni[$adType]['size'][$adArray['ad_place'] . '_image_height'], $adId);
                        
                        $slideArray['ad_id']          = $adId;
                        $slideArray['ad_slide_info']  = $adArray['ad_slide_text_' . $i];
                        $slideArray['ad_slide_image'] = $slideImage['image'];
                        $slideArray['ad_slide_sort']  = $adArray['ad_slide_sort_' . $i];
                        $slideArray['ad_slide_url']   = $adArray['ad_slide_url_' . $i];
                        $this->getDbshopTable('AdSlideTable')->addAdSlide($slideArray);
                    }
                }
                unset($slideArray, $slideImage);
            }
            //更新广告文件调用信息，用于前台调用
            $this->createAndUpdateAdFile($adType, $adId);
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('广告管理'), 'operlog_info'=>$this->getDbshopLang()->translate('添加电脑端广告') . '&nbsp;' . $adArray['ad_name']));
            
            unset($adArray);
            return $this->redirect()->toRoute('ad/default/ad-type', array('action'=>'setad','ad_type'=>$adType));
        }
        
        return $array;
    }
    public function editAction ()
    {
        $array  = array();
        
        $adId   = $this->params('ad_id', 0);
        $adInfo = $this->getDbshopTable()->infoAd(array('ad_id'=>$adId));
        
        $adType = $this->params('ad_type');
        $array['ad_type'] = $adType;
        
        //获取模板中对于广告位置和类型的设置
        $adIni = $this->readerAdIni();
        if(!isset($adIni['classname'][$adType])) return $this->redirect()->toRoute('ad/default');
        $array['set_ad_class_name']  = $adIni['classname'][$adType];
        //广告位置获取
        $array['ad_place'] = (isset($adIni[$adType]['place']) ? $adIni[$adType]['place'] : array());
        
        //编辑更新广告信息
        if($this->request->isPost()) {
            $adArray = $this->request->getPost()->toArray();
            $adArray['ad_class']    = $adType;
            $adArray['template_ad'] = DBSHOP_TEMPLATE;
            if(!in_array($adArray['ad_type'],array('image','slide'))) {
                $adArray['ad_body']   = $adArray['ad_'.$adArray['ad_type']];
            } else {
                $adArray['ad_width']  = $adIni[$adType]['size'][$adArray['ad_place'] . '_image_width'];
                $adArray['ad_height'] = $adIni[$adType]['size'][$adArray['ad_place'] . '_image_height'];
            }
            //图片广告处理
            if($adArray['ad_type'] == 'image') {
                $adImage = $this->getServiceLocator()->get('shop_other_upload')->adImageUpload('ad_image', (isset($adArray['old_ad_image']) ? $adArray['old_ad_image'] : ''), $adIni[$adType]['size'][$adArray['ad_place'] . '_image_width'], $adIni[$adType]['size'][$adArray['ad_place'] . '_image_height'] , $adId);
                $adArray['ad_body'] = $adImage['image'];
            }
            //广告基础内容更新
            $this->getDbshopTable()->updateAd($adArray, array('ad_id'=>$adId));
            //幻灯片广告处理
            if($adArray['ad_type'] == 'slide') {
                $this->getDbshopTable('AdSlideTable')->delSlideData(array('ad_id'=>$adId));
                for($i=1; $i<=5; $i++) {
                    if($_FILES['ad_slide_image_' . $i]['name'] != '' or (isset($adArray['old_ad_slide_image_' . $i]) and $adArray['old_ad_slide_image_' . $i] != '')) {
                        $slideArray = array();
                        $slideImage = array();
                        $slideImage = $this->getServiceLocator()->get('shop_other_upload')->adImageUpload('ad_slide_image_' . $i, (isset($adArray['old_ad_slide_image_' . $i]) ? $adArray['old_ad_slide_image_' . $i] : ''), $adIni[$adType]['size'][$adArray['ad_place'] . '_image_width'], $adIni[$adType]['size'][$adArray['ad_place'] . '_image_height'], $adId);
            
                        $slideArray['ad_id']          = $adId;
                        $slideArray['ad_slide_info']  = $adArray['ad_slide_text_' . $i];
                        $slideArray['ad_slide_image'] = $slideImage['image'];
                        $slideArray['ad_slide_sort']  = $adArray['ad_slide_sort_' . $i];
                        $slideArray['ad_slide_url']   = $adArray['ad_slide_url_' . $i];
                        $this->getDbshopTable('AdSlideTable')->addAdSlide($slideArray);
                    }
                }
                unset($slideArray, $slideImage);
            }
            //更新广告文件调用信息，用于前台调用
            $this->createAndUpdateAdFile($adType, $adId);
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('广告管理'), 'operlog_info'=>$this->getDbshopLang()->translate('更新电脑端广告') . '&nbsp;' . $adArray['ad_name']));
            
            unset($adArray);
            return $this->redirect()->toRoute('ad/default/ad-type', array('action'=>'setad','ad_type'=>$adType));
        }
        
        if(!isset($adInfo->ad_id)) return $this->redirect()->toRoute('ad/default/ad-type', array('action'=>'setad','ad_type'=>$adType));
        $array['ad_info'] = $adInfo;

        //当为幻灯片类型时，获取幻灯片数据
        $array['slide_array'] = $this->getDbshopTable('AdSlideTable')->listAdSlide(array('ad_id'=>$adId));
        
        return $array;
    }
    /** 
     * 广告删除
     */
    public function deladAction ()
    {
        $delState= 'false';
        $adType  = $this->request->getPost('ad_type');  //广告类型
        $adId    = $this->request->getPost('ad_id');    //广告id
        
        $adInfo  = $this->getDbshopTable()->infoAd(array('ad_id'=>$adId));
        if(!empty($adInfo)) {
            //删除前台数据文件
            $this->delAdFile($adInfo->ad_class, $adId);
            
            if($adInfo->ad_type == 'image' or $adInfo->ad_body != '') {
                @unlink(DBSHOP_PATH . $adInfo->ad_body);
                @rmdir(DBSHOP_PATH . '/public/upload/ad/' . $adId);
            }
            if($adInfo->ad_type == 'slide') $this->getDbshopTable('AdSlideTable')->delAdSlide(array('ad_id'=>$adId));
            $this->getDbshopTable()->delAd(array('ad_id'=>$adId));
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('广告管理'), 'operlog_info'=>$this->getDbshopLang()->translate('删除电脑端广告') . '&nbsp;' . $adInfo->ad_name));
            
            $delState = 'true';
        }
        exit($delState);
    }

    /**
     * 手机端广告首页
     * @return array
     */
    public function mobileIndexAction ()
    {
        $array = array();

        //获取模板中对于广告位置和类型的设置
        $array['ad_class']  = $this->readerAdIni('phone');
        if(is_array($array['ad_class']) and !empty($array['ad_class'])) {//获取广告类别下面添加的广告数量，在类别不是非常多的情况下，循环执行sql效率问题可以忽略不计
            foreach ($array['ad_class']['class'] as $value) {
                $array['ad_class']['adcount'][$value] = $this->getDbshopTable()->classAdCount(array('ad_class'=>$value, 'template_ad'=>$this->getDbshopPhoneTempate(), 'show_type'=>'phone'));
            }
        }

        return $array;
    }
    /**
     * 广告设置页面
     * @return multitype:
     */
    public function mobileSetadAction ()
    {
        $array  = array();
        $adType = $this->params('ad_type');

        $array['ad_type'] = $adType;

        //获取模板中对于广告位置和类型的设置
        $adIni = $this->readerAdIni('phone');
        if(!isset($adIni['classname'][$adType])) return $this->redirect()->toRoute('ad/default', array('action'=>'mobileIndex'));
        $array['set_ad_class_name']  = $adIni['classname'][$adType];
        //广告位置
        $array['place'] = (isset($adIni[$adType]['place']) ? $adIni[$adType]['place'] : array());

        //广告列表
        $array['ad_list'] = $this->getDbshopTable()->listAd(array('ad_class'=>$adType, 'template_ad'=>$this->getDbshopPhoneTempate(), 'show_type'=>'phone'));

        return $array;
    }
    /**
     * 添加广告
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:NULL unknown
     */
    public function mobileAddAction ()
    {
        $array  = array();
        $adType = $this->params('ad_type');

        $array['ad_type'] = $adType;

        //获取模板中对于广告位置和类型的设置
        $adIni = $this->readerAdIni('phone');
        if(!isset($adIni['classname'][$adType])) return $this->redirect()->toRoute('ad/default', array('action'=>'mobileIndex'));
        $array['set_ad_class_name']  = $adIni['classname'][$adType];

        //广告位置获取
        $array['ad_place'] = (isset($adIni[$adType]['place']) ? $adIni[$adType]['place'] : array());

        //添加广告
        if($this->request->isPost()) {
            $adArray = $this->request->getPost()->toArray();
            $adArray['show_type']   = 'phone';
            $adArray['ad_class']    = $adType;
            $adArray['template_ad'] = $this->getDbshopPhoneTempate();
            if(!in_array($adArray['ad_type'],array('image','slide'))) {
                $adArray['ad_body']   = $adArray['ad_'.$adArray['ad_type']];
            } else {
                $adArray['ad_width']  = $adIni[$adType]['size'][$adArray['ad_place'] . '_image_width'];
                $adArray['ad_height'] = $adIni[$adType]['size'][$adArray['ad_place'] . '_image_height'];
            }
            //广告基础内容插入
            $adId    = $this->getDbshopTable()->addAd($adArray);
            //图片广告处理
            if($adArray['ad_type'] == 'image') {
                $adImage = $this->getServiceLocator()->get('shop_other_upload')->adImageUpload('ad_image', '', $adIni[$adType]['size'][$adArray['ad_place'] . '_image_width'], $adIni[$adType]['size'][$adArray['ad_place'] . '_image_height'], $adId);
                if($adImage['image'] != '')$this->getDbshopTable()->updateAd(array('ad_body'=>$adImage['image'], 'ad_start_time'=>$adArray['ad_start_time'], 'ad_end_time'=>$adArray['ad_end_time'], 'show_type'=>'phone'), array('ad_id'=>$adId));
            }

            //幻灯片广告处理
            if($adArray['ad_type'] == 'slide') {
                for($i=1; $i<=5; $i++) {
                    if($_FILES['ad_slide_image_' . $i]['name'] != '') {
                        $slideArray = array();
                        $slideImage = array();
                        $slideImage = $this->getServiceLocator()->get('shop_other_upload')->adImageUpload('ad_slide_image_' . $i, '', $adIni[$adType]['size'][$adArray['ad_place'] . '_image_width'], $adIni[$adType]['size'][$adArray['ad_place'] . '_image_height'], $adId);

                        $slideArray['ad_id']          = $adId;
                        $slideArray['ad_slide_info']  = $adArray['ad_slide_text_' . $i];
                        $slideArray['ad_slide_image'] = $slideImage['image'];
                        $slideArray['ad_slide_sort']  = $adArray['ad_slide_sort_' . $i];
                        $slideArray['ad_slide_url']   = $adArray['ad_slide_url_' . $i];
                        $this->getDbshopTable('AdSlideTable')->addAdSlide($slideArray);
                    }
                }
                unset($slideArray, $slideImage);
            }
            //更新广告文件调用信息，用于前台调用
            $this->createAndUpdateAdFile($adType, $adId, 'phone');
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('广告管理'), 'operlog_info'=>$this->getDbshopLang()->translate('添加手机端广告') . '&nbsp;' . $adArray['ad_name']));

            unset($adArray);
            return $this->redirect()->toRoute('ad/default/ad-type', array('action'=>'mobileSetad','ad_type'=>$adType));
        }

        return $array;
    }
    /**
     * 编辑手机端广告
     * @return array|\Zend\Http\Response
     */
    public function mobileEditAction ()
    {
        $array  = array();

        $adId   = $this->params('ad_id', 0);
        $adInfo = $this->getDbshopTable()->infoAd(array('ad_id'=>$adId));

        $adType = $this->params('ad_type');
        $array['ad_type'] = $adType;

        //获取模板中对于广告位置和类型的设置
        $adIni = $this->readerAdIni('phone');
        if(!isset($adIni['classname'][$adType])) return $this->redirect()->toRoute('ad/default', array('action'=>'mobileIndex'));
        $array['set_ad_class_name']  = $adIni['classname'][$adType];
        //广告位置获取
        $array['ad_place'] = (isset($adIni[$adType]['place']) ? $adIni[$adType]['place'] : array());

        //编辑更新广告信息
        if($this->request->isPost()) {
            $adArray = $this->request->getPost()->toArray();
            $adArray['show_type']   = 'phone';
            $adArray['ad_class']    = $adType;
            $adArray['template_ad'] = $this->getDbshopPhoneTempate();
            if(!in_array($adArray['ad_type'],array('image','slide'))) {
                $adArray['ad_body']   = $adArray['ad_'.$adArray['ad_type']];
            } else {
                $adArray['ad_width']  = $adIni[$adType]['size'][$adArray['ad_place'] . '_image_width'];
                $adArray['ad_height'] = $adIni[$adType]['size'][$adArray['ad_place'] . '_image_height'];
            }
            //图片广告处理
            if($adArray['ad_type'] == 'image') {
                $adImage = $this->getServiceLocator()->get('shop_other_upload')->adImageUpload('ad_image', (isset($adArray['old_ad_image']) ? $adArray['old_ad_image'] : ''), $adIni[$adType]['size'][$adArray['ad_place'] . '_image_width'], $adIni[$adType]['size'][$adArray['ad_place'] . '_image_height'] , $adId);
                $adArray['ad_body'] = $adImage['image'];
            }
            //广告基础内容更新
            $this->getDbshopTable()->updateAd($adArray, array('ad_id'=>$adId));
            //幻灯片广告处理
            if($adArray['ad_type'] == 'slide') {
                $this->getDbshopTable('AdSlideTable')->delSlideData(array('ad_id'=>$adId));
                for($i=1; $i<=5; $i++) {
                    if($_FILES['ad_slide_image_' . $i]['name'] != '' or (isset($adArray['old_ad_slide_image_' . $i]) and $adArray['old_ad_slide_image_' . $i] != '')) {
                        $slideArray = array();
                        $slideImage = array();
                        $slideImage = $this->getServiceLocator()->get('shop_other_upload')->adImageUpload('ad_slide_image_' . $i, (isset($adArray['old_ad_slide_image_' . $i]) ? $adArray['old_ad_slide_image_' . $i] : ''), $adIni[$adType]['size'][$adArray['ad_place'] . '_image_width'], $adIni[$adType]['size'][$adArray['ad_place'] . '_image_height'], $adId);

                        $slideArray['ad_id']          = $adId;
                        $slideArray['ad_slide_info']  = $adArray['ad_slide_text_' . $i];
                        $slideArray['ad_slide_image'] = $slideImage['image'];
                        $slideArray['ad_slide_sort']  = $adArray['ad_slide_sort_' . $i];
                        $slideArray['ad_slide_url']   = $adArray['ad_slide_url_' . $i];
                        $this->getDbshopTable('AdSlideTable')->addAdSlide($slideArray);
                    }
                }
                unset($slideArray, $slideImage);
            }
            //更新广告文件调用信息，用于前台调用
            $this->createAndUpdateAdFile($adType, $adId, 'phone');
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('广告管理'), 'operlog_info'=>$this->getDbshopLang()->translate('更新手机端广告') . '&nbsp;' . $adArray['ad_name']));

            unset($adArray);
            return $this->redirect()->toRoute('ad/default/ad-type', array('action'=>'mobileSetad','ad_type'=>$adType));
        }

        if(!isset($adInfo->ad_id)) return $this->redirect()->toRoute('ad/default/ad-type', array('action'=>'mobileSetad','ad_type'=>$adType));
        $array['ad_info'] = $adInfo;

        //当为幻灯片类型时，获取幻灯片数据
        $array['slide_array'] = $this->getDbshopTable('AdSlideTable')->listAdSlide(array('ad_id'=>$adId));

        return $array;
    }
    /**
     * 广告删除
     */
    public function MobileDeladAction ()
    {
        $delState= 'false';
        $adType  = $this->request->getPost('ad_type');  //广告类型
        $adId    = $this->request->getPost('ad_id');    //广告id

        $adInfo  = $this->getDbshopTable()->infoAd(array('ad_id'=>$adId));
        if(!empty($adInfo)) {
            //删除前台数据文件
            $this->delAdFile($adInfo->ad_class, $adId, 'phone');

            if($adInfo->ad_type == 'image' or $adInfo->ad_body != '') {
                @unlink(DBSHOP_PATH . $adInfo->ad_body);
                @rmdir(DBSHOP_PATH . '/public/upload/ad/' . $adId);
            }
            if($adInfo->ad_type == 'slide') $this->getDbshopTable('AdSlideTable')->delAdSlide(array('ad_id'=>$adId));
            $this->getDbshopTable()->delAd(array('ad_id'=>$adId));
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('广告管理'), 'operlog_info'=>$this->getDbshopLang()->translate('删除手机端广告') . '&nbsp;' . $adInfo->ad_name));

            $delState = 'true';
        }
        exit($delState);
    }
    /**
     *
     */
    public function delSlideImageAction()
    {
        $delState   = 'false';
        $adId       = (int) $this->request->getPost('ad_id');    //广告id
        $slideImage = $this->request->getPost('image_path');

        if($adId > 0 and !empty($slideImage)) {
            $where = array('ad_id'=>$adId, 'ad_slide_image'=>$slideImage);
            $slideInfo = $this->getDbshopTable('AdSlideTable')->infoAdSlide($where);
            if(isset($slideInfo->ad_id) and $slideInfo->ad_id > 0) {
                if($slideInfo->ad_slide_image != '') @unlink(DBSHOP_PATH . $slideInfo->ad_slide_image);
                $this->getDbshopTable('AdSlideTable')->delSlideData($where);
                $delState = md5($slideImage);

                $adInfo  = $this->getDbshopTable()->infoAd(array('ad_id'=>$adId));
                if($adInfo->ad_type == 'slide') {
                    //更新广告文件调用信息，用于前台调用
                    $this->createAndUpdateAdFile($adInfo->ad_class, $adId, $adInfo->show_type);
                }
            }
        }
        exit($delState);
    }
    /** 
     * 在添加广告时，从这里获取图片广告或者幻灯片广告的最佳尺寸
     */
    public function getImageSizeAction ()
    {
        $adType  = $this->request->getPost('ad_type');  //广告类型
        $adPlace = $this->request->getPost('ad_place'); //广告位置
        $type    = $this->request->getPost('type');     //广告类别
        $showTypeV = $this->request->getPost('show_type');
        $showType= !empty($showTypeV) ? $showTypeV : 'pc';
        $content = $this->getDbshopLang()->translate('推荐尺寸') . '&nbsp;&nbsp;';
        
        $adIni   = $this->readerAdIni($showType);
        $content = (isset($adIni[$type]['size'][$adPlace.'_image']) and !empty($adIni[$type]['size'][$adPlace.'_image']) and @in_array($adType, array('image','slide'))) ? '<strong>' . $content . $adIni[$type]['size'][$adPlace.'_image'] . '</strong>' : '';
        
        exit($content);
    }
    /**
     * 获取模板对于广告设置的信息
     * @return Ambigous <multitype:>
     */
    private function readerAdIni($type='pc')
    {
        $adIni       = array();
        $adIniReader = new \Zend\Config\Reader\Ini();

        if($type == 'pc') $temPath = '/module/Shopfront/view/' . DBSHOP_TEMPLATE . '/shopfront/template.ini';
        else $temPath = '/module/Mobile/view/'. $this->getDbshopPhoneTempate() .'/mobile/template.ini';

        $adIni       = $adIniReader->fromFile(DBSHOP_PATH .$temPath);
        
        return $adIni['ad'];
    }
    /**
     * 删除前台广告调用数据文件
     * @param $adClass
     * @param $adId
     * @param string $showType
     * @throws \Exception
     */
    private function delAdFile($adClass, $adId, $showType='pc')
    {
        $adDateFileReader = new \Zend\Config\Reader\Ini();
        $adDateFileWrite  = new \Zend\Config\Writer\Ini();

        if($showType == 'pc') {
            $adFile           = DBSHOP_PATH . '/data/moduledata/Ad/' . DBSHOP_TEMPLATE . '/' . $adClass . '.ini';
            $contentFilePath  = DBSHOP_PATH . '/data/moduledata/Ad/' . DBSHOP_TEMPLATE . '/';
        } else {
            $adFile           = DBSHOP_PATH . '/data/moduledata/Ad/mobile/' . $this->getDbshopPhoneTempate() . '/' . $adClass . '.ini';
            $contentFilePath  = DBSHOP_PATH . '/data/moduledata/Ad/mobile/' . $this->getDbshopPhoneTempate() . '/';
        }
        
        $adData = (file_exists($adFile) ? $adDateFileReader->fromFile($adFile) : array());
        $adInfo = $this->getDbshopTable()->infoAd(array('ad_id'=>$adId));
        
        if($adInfo->ad_id != '') {
            unset($adData[$adInfo->ad_place][$adInfo->ad_id]);
            $adDateFileWrite->toFile($adFile, $adData);
            @unlink($contentFilePath . '/' . $adClass . '_' . $adInfo->ad_place . '_' . $adInfo->ad_id . '.php');
        }
    }
    /**
     * 生成前台调用广告文件
     * @param $adClass
     * @param $adId
     * @param string $type
     * @throws \Exception
     */
    private function createAndUpdateAdFile($adClass, $adId, $type='pc')
    {
        $adDateFileReader = new \Zend\Config\Reader\Ini();
        $adDateFileWrite  = new \Zend\Config\Writer\Ini();
        if($type == 'pc') {
            $adFile           = DBSHOP_PATH . '/data/moduledata/Ad/' . DBSHOP_TEMPLATE . '/' . $adClass . '.ini';
            $contentFilePath  = DBSHOP_PATH . '/data/moduledata/Ad/' . DBSHOP_TEMPLATE . '/';
        } else {
            $adFile           = DBSHOP_PATH . '/data/moduledata/Ad/mobile/' . $this->getDbshopPhoneTempate() . '/' . $adClass . '.ini';
            $contentFilePath  = DBSHOP_PATH . '/data/moduledata/Ad/mobile/' . $this->getDbshopPhoneTempate() . '/';
        }

        if(!is_dir($contentFilePath)) {
            mkdir(rtrim($contentFilePath), 0755, true);
        }
        
        $adData = array();
        $adInfo = $this->getDbshopTable()->infoAd(array('ad_id'=>$adId));
        if($adInfo->ad_id != '') {
            //判断是否存在配置文件，如果存在读取配置文件，然后覆盖配置文件对应的广告信息
            if(file_exists($adFile)) $adData = $adDateFileReader->fromFile($adFile);
            $adData[$adInfo->ad_place][$adInfo->ad_id] = array(
                        'ad_name' => $adInfo->ad_name,
                        'file'    => $adInfo->ad_class . '_' . $adInfo->ad_place . '_' . $adInfo->ad_id . '.php',
                        'state'   => $adInfo->ad_state,
                        'url'     => $adInfo->ad_url,
                        'start_time' => $adInfo->ad_start_time,
                        'end_time'   => $adInfo->ad_end_time,
            );
            $adDateFileWrite->toFile($adFile, $adData);
            //写入广告内容
            file_put_contents($contentFilePath . '/' . $adClass . '_' . $adInfo->ad_place . '_' . $adInfo->ad_id . '.php', $this->createAdContent($adInfo, $type));
        }
    }
    /**
     * 生成广告内容文件
     * @param $adInfo
     * @param string $showType
     * @return mixed|string
     */
    private function createAdContent($adInfo, $showType='pc')
    {
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');

        if($showType == 'pc') {
            $content  = file_get_contents(DBSHOP_PATH . '/module/Shopfront/view/' . DBSHOP_TEMPLATE . '/shopfront/ad/' . $adInfo->ad_type . '.phtml');
            $template = file_get_contents(DBSHOP_PATH . '/module/Shopfront/view/' . DBSHOP_TEMPLATE . '/shopfront/ad/' . $adInfo->ad_class . '_' . $adInfo->ad_place . '.phtml');
        } else {
            $content  = file_get_contents(DBSHOP_PATH . '/module/Mobile/view/' . $this->getDbshopPhoneTempate() . '/mobile/ad/' . $adInfo->ad_type . '.phtml');
            $template = file_get_contents(DBSHOP_PATH . '/module/Mobile/view/' . $this->getDbshopPhoneTempate() . '/mobile/ad/' . $adInfo->ad_class . '_' . $adInfo->ad_place . '.phtml');
        }

        switch ($adInfo->ad_type) {
            case 'text':
            case 'code':
                $content = str_replace(array('{ad_url}', '{ad_body}'), array($adInfo->ad_url, $adInfo->ad_body), $content);
                break;
            case 'image':
                $content = str_replace(array('{ad_url}', '{ad_body}'), array($adInfo->ad_url, $renderer->basePath($adInfo->ad_body)), $content);
                break;
            case 'slide':
                $adTemplate    = explode('<dbshop>', $content);
                $adTemplate[0] = str_replace('{randid}', time(), $adTemplate[0]);
                $adTemplate[2] = str_replace('{randid}', time(), $adTemplate[2]);
                $slideArray    = $this->getDbshopTable('AdSlideTable')->listAdSlide(array('ad_id'=>$adInfo->ad_id));
                $slideContent  = '';
                if(is_array($slideArray) and !empty($slideArray)) {
                    foreach ($slideArray as $key => $value) {
                        $slideContent .= str_replace(array('{ad_url}', '{image}', '{ad_body}'), array($value['ad_slide_url'], $renderer->basePath($value['ad_slide_image']), $value['ad_slide_info']), ($key == 0 ? str_replace('{active}', 'active ', $adTemplate[1]) : str_replace('{active}', '', $adTemplate[1])));
                    }
                }
                $content = $adTemplate[0] . $slideContent . $adTemplate[2];
                break;
        }
        $content = str_replace('{content}', $content, $template);
        
        return $content;
    }
    /**
     * 获取系统设置的手机端显示模板，如果没有则显示默认的
     * @return string
     */
    private function getDbshopPhoneTempate()
    {
        if(defined('DBSHOP_PHONE_TEMPLATE')) return DBSHOP_PHONE_TEMPLATE;
        else return 'default';
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'AdTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
    }
}
