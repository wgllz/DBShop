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
    'controllers' => array(
        'invokables' => array(
            'Stock\Controller\Stock' => 'Stock\Controller\StockController',
            'Stock\Controller\State' => 'Stock\Controller\StateController',
        ),
    ),
    'router' => include __DIR__ . '/router.config.php',
    'translator' => array(
            'translation_file_patterns' => array(
                    array(
                            'type' => 'gettext',
                            'base_dir' => __DIR__ . '/../language',
                            'pattern' => '%s.mo'
                    )
            )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'Stock' => __DIR__ . '/../view',
        ),
    ),
);
