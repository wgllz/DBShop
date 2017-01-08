<?php
/**
 * DBShop 电子商务系统
 *
 * ==========================================================================
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2016 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 * ==========================================================================
 *
 * @author    静静的风
 *
 */

namespace Plugin;

class Module
{

    public function getAutoloaderConfig ()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php'
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    // if we're in a namespace deeper than one level we need to fix the \ in the path
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/', __NAMESPACE__)
                )
            )
        );
    }

    public function getConfig ()
    {
        $configArray = include __DIR__ . '/config/module.config.php';
        //$apiConfig   = include __DIR__ . '/Extendapp/Dbapi/config/module.config.php';

        /*$configArray['controllers']['invokables']   = array_merge($configArray['controllers']['invokables'], $apiConfig['controllers']['invokables']);
        $configArray['router']['routes']            = array_merge($configArray['router']['routes'], $apiConfig['router']['routes']);
        $configArray['translator']['translation_file_patterns'] = array_merge($configArray['translator']['translation_file_patterns'], $apiConfig['translator']['translation_file_patterns']);
        $configArray['view_manager']['template_path_stack'] = array_merge($configArray['view_manager']['template_path_stack'], $apiConfig['view_manager']['template_path_stack']);
        */
        //$configArray = array_merge($configArray, $apiConfig);
        //print_r($configArray);exit;
        //$configArray = self::fileMergeArray(__DIR__ . '/config/module_config/*.php');
        return $configArray;
    }

    public function onBootstrap ($e)
    {

    }

    public function getServiceConfig ()
    {
        $tableArray = include __DIR__ . '/config/table.config.php';
        //$apiTable   = include __DIR__ . '/Extendapp/Dbapi/config/table.config.php';

        //$tableArray = array_merge($tableArray, $apiTable);
        //$tableArray = self::fileMergeArray(__DIR__ . '/config/module_table/*.php');
        return array(
            // 模块中的数据表
            'factories' => $tableArray
        );
    }
    /**
     * 将文件夹内的文件，合并为一个数组
     * @param $pathFile
     * @return array
     */
    public static function fileMergeArray($pathFile)
    {
        $cArray = array();
        foreach (glob($pathFile) as $file) {
            $array = include $file;
            if(is_array($array) and !empty($array)) $cArray = array_merge($array, $cArray);
        }
        return $cArray;
    }

}
