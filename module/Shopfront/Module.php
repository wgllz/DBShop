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

namespace Shopfront;

use Shopfront\Helper\Helper as frontHelper;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
		    // if we're in a namespace deeper than one level we need to fix the \ in the path
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/' , __NAMESPACE__),
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    public function getServiceConfig ()
    {
        return array(
            'factories'=>array(
                'frontHelper'    => function () {return new \Shopfront\Helper\Helper(); },
                'frontCache' => function() { return \Zend\Cache\StorageFactory::factory(
                        array(
                            'adapter' => array(
                                'name' => 'filesystem',
                                'options' => array(
                                    'dirLevel' => 0,
                                    'cacheDir' => 'data/cache/frontcache',
                                    'dirPermission' => 0755,
                                    'filePermission' => 0666,
                                    'ttl' => (defined('FRONT_CACHE_EXPIRE_TIME') ? FRONT_CACHE_EXPIRE_TIME : 600),
                                    'namespaceSeparator' => '-dbshop-'
                                ),
                            ),
                        )
                    );}
            )
        );
    }
    public function onBootstrap($e)
    {
        $app = $e->getParam('application');

        $app->getEventManager()->attach('dispatch', array($this, 'setLayout'));

        //页面压缩性能设置
        if(defined('DBSHOP_ADMIN_COMPRESS') and defined('DBSHOP_FRONT_COMPRESS') and (DBSHOP_ADMIN_COMPRESS == 'true' or DBSHOP_FRONT_COMPRESS == 'true')) {
            $app->getEventManager()->attach("finish", array($this, "compressOutput"), 100);
        }

        //缓存开启
        if(defined('FRONT_CACHE_STATE') and FRONT_CACHE_STATE == 'true') {
            $app->getEventManager()->attach('dispatch', array($this, 'dispatchCachePage'), 1001);
            $app->getEventManager()->attach('finish', array($this, 'cachePage'), 1001);
        }

        //当开启https时，将强制进行https访问
        /*
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($app->getEventManager());
        $app->getEventManager()->attach('route', array($this, 'doHttpsRedirect'));
        */
    }
    /**
     * 设置前台layout
     * @param $e
     */
    public function setLayout($e)
    {
        //设置系统信息
        $app = $e->getApplication();
        $serviceManager = $app->getServiceManager();

        $matches    = $e->getRouteMatch();
        $controller = $matches->getParam('controller');

        if($matches->getParam('action') != 'goodsClass') {//这个判断是为了前台在ajax调用商品分类时，报错，进行测试处理，看是否还会出错误
            $serviceManager->get('viewhelpermanager')->setFactory('frontwebsite', function  ($sm) use($e)
            {
                return new frontHelper();
            });
        }

        if (false === strpos($controller, __NAMESPACE__)) {
            // not a controller from this module
            return;
        }
        //检查是否关闭
        if($matches->getParam('action') != 'goodsClass') {
            if($serviceManager->get('frontHelper')->websiteInfo('shop_close') == 'close') {
                @header("Content-type: text/html; charset=utf-8");
                exit($serviceManager->get('frontHelper')->websiteInfo('shop_close_info'));
            }
        }
        //作为访问时间间隔控制，暂时未用
        /*if(!defined('FRONT_CACHE_STATE') or FRONT_CACHE_STATE != 'true') {
            $this->shopPageSession($serviceManager);
        }*/

        // Set the layout template
        $viewModel = $e->getViewModel();
        if($e->getRequest()->isXmlHttpRequest()) {
            $viewModel->setTerminal(true);
        } else {
            $viewModel->setTemplate('site/layout');
        }
    }
    /**
     * https强制转换
     * @param MvcEvent $e
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function doHttpsRedirect(MvcEvent $e){
        $sm = $e->getApplication()->getServiceManager();
        $uri = $e->getRequest()->getUri();
        $scheme = $uri->getScheme();
        if ($scheme != 'https'){
            $uri->setScheme('https');
            $response=$e->getResponse();
            $response->getHeaders()->addHeaderLine('Location', $uri);
            $response->setStatusCode(302);
            $response->sendHeaders();
            return $response;
        }
    }
    /**
     * 检查缓存是否存在，如果存在直接使用缓存
     * @param $e
     * @return mixed
     */
    public function dispatchCachePage($e)
    {
        $app = $e->getApplication();
        $serviceManager = $app->getServiceManager();

        $matches    = $e->getRouteMatch();
        if(isset($matches)) {
            $controller = $matches->getParam('controller');
            $action     = $matches->getParam('action');
        }

        if (!isset($controller) || false === strpos($controller, __NAMESPACE__) || $serviceManager->get('frontHelper')->isMobile() || $serviceManager->get('frontHelper')->websiteInfo('shop_close') == 'close') {
            return;
        }

        //作为访问时间间隔控制，暂时未用
        //$this->shopPageSession($serviceManager);

        $uri = $e->getRequest()->getUri();
        $queryStr = $this->cacheQueryStr($controller, $action);
        $key = md5($uri->getPath() . $queryStr);

        $cacheAdapter = $serviceManager->get('frontCache');
        if($cacheAdapter->hasItem($key)) {
            $content  = $cacheAdapter->getItem($key);
            $response = $e->getResponse();
            $response->setContent($content);

            return $response;
        }
    }
    /**
     * 生成缓存
     * @param $e
     */
    public function cachePage($e)
    {
        $app = $e->getApplication();
        $serviceManager = $app->getServiceManager();

        $matches    = $e->getRouteMatch();
        if(isset($matches)) {
            $controller = $matches->getParam('controller');
            $action     = $matches->getParam('action');
        }

        if (!isset($controller) || false === strpos($controller, __NAMESPACE__) || $serviceManager->get('frontHelper')->isMobile() || $serviceManager->get('frontHelper')->websiteInfo('shop_close') == 'close') {
            return;
        }

        $cachedControllerAndActionArray = array(
            'Shopfront\Controller\Index' => array(//首页
                'index',
                'goodsClass',
            ),
            'Shopfront\Controller\Goodslist' => array(//商品列表
                'index',
                'goodsSearch'
            ),
            'Shopfront\Controller\Goods' => array(//商品
                'index',
            ),
            'Shopfront\Controller\Article' => array(//文章
                'index',
                'content',
                'single'
            ),
            'Shopfront\Controller\Brand' => array(//商品品牌
                'index',
                'brandGoods'
            ),
        );

        if(isset($cachedControllerAndActionArray[$controller]) and in_array($action, $cachedControllerAndActionArray[$controller])) {

            $uri = $e->getRequest()->getUri();
            $queryStr = $this->cacheQueryStr($controller, $action);
            $key = md5($uri->getPath() . $queryStr);

            $cacheAdapter = $serviceManager->get('frontCache');
            if(!$cacheAdapter->hasItem($key)) {
                $response = $e->getResponse();
                $content  = trim($response->getContent());

                if (!empty($content)) $cacheAdapter->setItem($key, $content);
                else return;
            }
        }
    }
    private function cacheQueryStr($controller, $action)
    {
        $queryStr = '';
        switch($controller) {
            case 'Shopfront\Controller\Index':
                break;
            case 'Shopfront\Controller\Brand':
                if($action == 'brandGoods') {
                    $queryStr .= (isset($_GET['time_sort']) ? 'time_sort'.trim($_GET['time_sort']) : '');
                    $queryStr .= (isset($_GET['click_sort']) ? 'click_sort'.trim($_GET['click_sort']) : '');
                    $queryStr .= (isset($_GET['price_sort']) ? 'price_sort'.trim($_GET['price_sort']) : '');
                    $queryStr .= (isset($_GET['sort_c']) ? 'sort_c'.trim($_GET['sort_c']) : '');
                }
                break;
            case 'Shopfront\Controller\Article':
                break;
            case 'Shopfront\Controller\Goods':
                break;
            case 'Shopfront\Controller\Goodslist':
                $queryStr .= (isset($_GET['time_sort']) ? 'time_sort'.trim($_GET['time_sort']) : '');
                $queryStr .= (isset($_GET['click_sort']) ? 'click_sort'.trim($_GET['click_sort']) : '');
                $queryStr .= (isset($_GET['price_sort']) ? 'price_sort'.trim($_GET['price_sort']) : '');
                $queryStr .= (isset($_GET['sort_c']) ? 'sort_c'.trim($_GET['sort_c']) : '');

                if($action == 'index') {
                    $queryStr .= (isset($_GET['tag_c']) ? 'tag_c'.trim($_GET['tag_c']) : '');
                    $queryStr .= (isset($_GET['tag_id']) ? 'tag_id'.trim($_GET['tag_id']) : '');
                }
                if($action == 'goodsSearch') {
                    $queryStr .= (isset($_GET['keywords']) ? 'keywords'.trim($_GET['keywords']) : '');
                }
                break;

        }
        return $queryStr;
    }
    private function shopPageSession($serviceManager)
    {
        //控制浏览频率
        $pageSession = new Container('page_session');
        if(!isset($pageSession->showTime) or $pageSession->showTime == '') {
            $pageSession->showTime = time();
        } else {
            $time = (time()-$pageSession->showTime);
            if($time < 2) {
                $pageSession->showTime = time() + 5;
                exit($serviceManager->get('translator')->translate('浏览过于频繁，请等待30秒钟后手动刷新该页面！'));
            }
            else $pageSession->showTime = time();
        }
    }
    /**
     * 设置页面压缩Gzip
     * @param $e
     */
    public function compressOutput($e)
    {
        $matches    = $e->getRouteMatch();
        if(!$matches) return;
        $controller = $matches->getParam('controller');

        //前台压缩处理开启
        if(DBSHOP_ADMIN_COMPRESS == 'false' and DBSHOP_FRONT_COMPRESS == 'true') {
            if (false === strpos($controller, __NAMESPACE__)) return;
        }
        //后台压缩处理开启
        if(DBSHOP_ADMIN_COMPRESS == 'true' and DBSHOP_FRONT_COMPRESS == 'false') {
            if (false !== strpos($controller, __NAMESPACE__)) return;
        }

        $response = $e->getResponse();
        $content = $response->getBody();
        if(@strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false)
        {
            header('Content-Encoding: gzip');
            $content = gzencode($content, 9);
        }
        $response->setContent($content);
    }
}
