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

namespace System\Controller;

use Admin\Controller\BaseController;

class OptimizationController extends BaseController
{
    /**
     * 优化设置
     */
    public function indexAction()
    {
        $array = array();

        $array['dbshop_admin_compress'] = (defined('DBSHOP_ADMIN_COMPRESS') ? DBSHOP_ADMIN_COMPRESS : '');
        $array['dbshop_front_compress'] = (defined('DBSHOP_FRONT_COMPRESS') ? DBSHOP_FRONT_COMPRESS : '');

        $array['front_cache_state']     = (defined('FRONT_CACHE_STATE')     ? FRONT_CACHE_STATE     : '');
        $array['front_cache_time']      = (defined('FRONT_CACHE_TIME')      ? FRONT_CACHE_TIME      : '');
        $array['front_cache_time_type'] = (defined('FRONT_CACHE_TIME_TYPE') ? FRONT_CACHE_TIME_TYPE : '');

        $array['front_cdn_state']       = (defined('FRONT_CDN_STATE')       ? FRONT_CDN_STATE       : 'false');
        $array['front_cdn_http_type']   = (defined('FRONT_CDN_HTTP_TYPE')   ? FRONT_CDN_HTTP_TYPE   : 'http://');
        $array['front_cdn_domain']      = (defined('FRONT_CDN_DOMAIN')      ? FRONT_CDN_DOMAIN      : '');

        if($this->request->isPost()) {
            $optimizationArray = $this->request->getPost()->toArray();
            $setArray = array();
            //Gzip优化
            $setArray['DBSHOP_ADMIN_COMPRESS'] = (isset($optimizationArray['dbshop_admin_compress']) ? $optimizationArray['dbshop_admin_compress'] : 'false');
            $setArray['DBSHOP_FRONT_COMPRESS'] = (isset($optimizationArray['dbshop_front_compress']) ? $optimizationArray['dbshop_front_compress'] : 'false');
            //前台缓存
            $setArray['FRONT_CACHE_STATE']     = (isset($optimizationArray['front_cache_state'])     ? $optimizationArray['front_cache_state']     : '');
            $setArray['FRONT_CACHE_TIME']      = (isset($optimizationArray['front_cache_time'])      ? $optimizationArray['front_cache_time']      : 0);
            $setArray['FRONT_CACHE_TIME_TYPE'] = (isset($optimizationArray['front_cache_time_type']) ? $optimizationArray['front_cache_time_type'] : 0);
            $setArray['FRONT_CACHE_EXPIRE_TIME'] = $setArray['FRONT_CACHE_TIME'] * $setArray['FRONT_CACHE_TIME_TYPE'];
            //cdn图片加速（加速本地商品图片）
            $setArray['FRONT_CDN_STATE']       = (isset($optimizationArray['front_cdn_state'])     ? $optimizationArray['front_cdn_state']     : 'false');
            $setArray['FRONT_CDN_HTTP_TYPE']   = (isset($optimizationArray['front_cdn_http_type']) ? $optimizationArray['front_cdn_http_type'] : 'http://');
            $setArray['FRONT_CDN_DOMAIN']      = (isset($optimizationArray['front_cdn_domain'])    ? $optimizationArray['front_cdn_domain']    : '');

            $this->getServiceLocator()->get('adminHelper')->setDbshopSetshopFile($setArray);
            //清空ZF2缓存设置
            $this->getServiceLocator()->get('adminHelper')->clearZfConfigCache();
            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('性能优化'), 'operlog_info'=>$this->getDbshopLang()->translate('性能优化设置')));

            $array['dbshop_admin_compress'] = $setArray['DBSHOP_ADMIN_COMPRESS'];
            $array['dbshop_front_compress'] = $setArray['DBSHOP_FRONT_COMPRESS'];

            $array['front_cache_state']     = $setArray['FRONT_CACHE_STATE'];
            $array['front_cache_time']      = $setArray['FRONT_CACHE_TIME'];
            $array['front_cache_time_type'] = $setArray['FRONT_CACHE_TIME_TYPE'];

            $array['front_cdn_state']       = $setArray['FRONT_CDN_STATE'];
            $array['front_cdn_http_type']   = $setArray['FRONT_CDN_HTTP_TYPE'];
            $array['front_cdn_domain']      = $setArray['FRONT_CDN_DOMAIN'];

            $array['success_msg'] = $this->getDbshopLang()->translate('性能优化设置保存成功！');

        }

        return $array;
    }
    /**
     * 前台缓存清理
     */
    public function clearFrontCacheAction()
    {
        //查看缓存是否开启，如果开启则进行缓存清除
        if(defined('FRONT_CACHE_STATE') and FRONT_CACHE_STATE == 'true') {
            $this->getServiceLocator()->get('frontCache')->flush();
            exit('true');
        }
        exit('false');
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
}