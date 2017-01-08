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

return array(
		'module_listener_options' => array(
				'config_glob_paths'         => array('config/autoload/{*}.php'),
				'module_paths'              => array('./module', './module/Extendapp', './vendor'),
		        /*系统配置信息缓存设置*/
		        'config_cache_enabled'      => true,
		        'config_cache_key'          => md5('config'.__FILE__),
		        'module_map_cache_enabled'  => true,
		        'module_map_cache_key'      => md5('module_map'.__FILE__),
		        'cache_dir'                 => "./data/cache/modulecache",
		      )
		);
