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

use User\FormValidate\FormUserValidate;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Captcha;
use Zend\Json\Json;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    private $dbTables = array();
    private $translator;
    
    /** 
     * 首页
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        //检查程序是否安装
        if(!file_exists(DBSHOP_PATH . '/data/install.lock')) {
           return $this->redirect()->toRoute('install/default');
        }
        //统计使用(这里不要轻易删除，因为在模板中有对应的操作，有些是非统计使用)
        $this->layout()->dbTongJiPage = 'index';
        
        $array = array();

        //判断是否为手机端访问
        if($this->getServiceLocator()->get('frontHelper')->isMobile()) return $this->redirect()->toRoute('mobile/default');

        //商品分类
        $arrayFile = DBSHOP_PATH . '/data/moduledata/Shopfront/goodsClass.php';
        //对商品分类数组进行写入文件处理
        if(file_exists($arrayFile)) {
            $array['goods_class'] = include $arrayFile;
        } else {
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
            $arrayWrite = new \Zend\Config\Writer\PhpArray();
            $arrayWrite->toFile(DBSHOP_PATH . '/data/moduledata/Shopfront/goodsClass.php', $array['goods_class']);
        }
        //友情链接
        $array['flink_list']   = $this->getDbshopTable('LinksTable')->listLinks(array('links_state=1',"links_logo!=''"));
        $this->layout()->index_flink_list = $array['flink_list'];//当友情链接在footer页面使用时，此设置起作用
                       
        //品牌
        $array['goods_brand'] = $this->getDbshopTable('GoodsBrandTable')->listGoodsBrand(array(),12);
        
        //客服代码
        $this->layout()->kefu_html = $this->getServiceLocator()->get('frontHelper')->getOnlineService('index');

        return $array;
    }
    /**
     * ajax获取商品分类列表
     * @return ViewModel
     */
    public function goodsClassAction() {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);

        $array = array();
        $arrayFile = DBSHOP_PATH . '/data/moduledata/Shopfront/goodsClass.php';
        //对商品分类数组进行写入文件处理
        if(file_exists($arrayFile)) {
            $array['goods_class'] = include $arrayFile;
        } else {
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
            $arrayWrite = new \Zend\Config\Writer\PhpArray();
            $arrayWrite->toFile(DBSHOP_PATH . '/data/moduledata/Shopfront/goodsClass.php', $array['goods_class']);
        }

        return $viewModel->setVariables($array);
    }

    public function ajaxheaderAction() {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);

        $array = array();
        return $viewModel->setVariables($array);
    }
    /**
     * 手机验证码
     */
    public function phoneCaptchaAction()
    {
        if($this->getServiceLocator()->get('frontHelper')->websiteCaptchaState('phone_user_register_captcha') == 'true') {
            $phoneCaptchaSession = new Container('phone_captcha');
            // 验证码验证，通过ajax验证
            $postCaptcha = $this->request->getPost('phone_captcha');
            $checkState  = $this->params('captcha_check');
            if ($checkState == 1) {
                echo $postCaptcha == $phoneCaptchaSession->captcha ? 'true' : 'false';
                exit();
            }

            $userPhone = $this->request->getPost('user_phone');
            if(empty($userPhone) or !is_numeric($userPhone)) exit($this->getDbshopLang()->translate('不是正确的手机号码!'));
            $phoneState = preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $userPhone) ? 'true' : 'false';
            if($phoneState == 'false') exit($this->getDbshopLang()->translate('不是正确的手机号码!'));

            //防止刷手机短信
            $captchaValidate = new FormUserValidate($this->getDbshopLang());
            $captchaValidate->checkUserForm($this->request->getPost(), 'phoneCaptcha');

            $number = '1234567890';
            for($i=0; $i<10; $i++) {
                $num[$i] = $number[$i];
            }
            mt_srand((double) microtime() * 10000);
            shuffle($num);

            $phoneCaptcha = substr(implode('',$num), 2, 6);
            $phoneCaptchaSession = new Container('phone_captcha');
            $phoneCaptchaSession->captcha = $phoneCaptcha;

            try {
                $this->getServiceLocator()->get('shop_send_sms')->toSendSms(
                    array(
                        'captcha'           => $phoneCaptcha,
                        'patcheashopname'   => $this->getServiceLocator()->get('frontHelper')->websiteInfo('shop_name')
                    ),
                    $userPhone,
                    'alidayu_phone_captcha_template_id'
                );
            } catch(\Exception $e) {
                echo $this->getDbshopLang()->translate('验证码获取失败！');
            }
        } else {
            echo $this->getDbshopLang()->translate('系统未开启手机验证功能!');
        }
        exit();
    }
    /**
     * 验证码
     */
    public function captchaAction ()
    {
        $captchaSession = new Container('captcha');
        
        // 验证码验证，通过ajax验证
        $postCaptcha = $this->request->getPost('captcha_code');
        $checkState  = $this->params('captcha_check');
        if ($checkState == 1) {
            echo strtolower($postCaptcha) == strtolower($captchaSession->word) ? 'true' : 'false';
            exit();
        }
        
        $captcha = new Captcha\Image(array(
            'font' => DBSHOP_PATH . '/module/Upload/src/Upload/Plugin/Watermark/verdana.ttf',
            'session' => $captchaSession,
            'fontsize' => 15,
            'imgdir' => 'public/upload/captcha/',
            'width' => 90,
            'height' => 40,
            'gcFreq' => 1,
            'dotNoiseLevel' => 5,
            'lineNoiseLevel' => 1,
            'wordlen' => 5,
            'expiration' => 0
        ));
        //生成验证码
        $captcha->generate();
        //变为json格式后输出
        echo Json::encode(array(
            'captcha_file' => $captcha->getImgDir().$captcha->getId().$captcha->getSuffix(),
            //'captcha_code' => $captcha->getWord()
        ));

        /*header("Pragma:no-cache\r\n");
        header("Cache-Control:no-cache\r\n");
        header("Expires:0\r\n");
        header("content-type:image/png\r\n");
        readfile($captcha->getImgDir().$captcha->getId().$captcha->getSuffix());*/
        exit();
    }
    /**
     * 货币切换
     */
    public function chanageCurrencyAction()
    {
        $this->getServiceLocator()->get('frontHelper')->setFrontDefaultCurrency($this->params('code'));
        //判断是否缓存开启，这样的处理方式问题在于，如果缓存比较大的时候，会慢
        if(defined('FRONT_CACHE_STATE') and FRONT_CACHE_STATE == 'true') {
            $this->getServiceLocator()->get('frontCache')->flush();
        }
        @header("Location: " . $this->getRequest()->getServer('HTTP_REFERER'));
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
