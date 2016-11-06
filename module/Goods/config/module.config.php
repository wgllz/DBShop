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
            'Goods\Controller\Goods'      => 'Goods\Controller\GoodsController',
            'Goods\Controller\Brand'      => 'Goods\Controller\BrandController',
            'Goods\Controller\Class'      => 'Goods\Controller\ClassController',
            'Goods\Controller\Comment'    => 'Goods\Controller\CommentController',
            'Goods\Controller\Ask'        => 'Goods\Controller\AskController',
            'Goods\Controller\Tag'        => 'Goods\Controller\TagController',
            'Goods\Controller\Attribute'  => 'Goods\Controller\AttributeController',
            'Goods\Controller\Promotions' => 'Goods\Controller\PromotionsController',
        ),
    ),
    //路由设置
    'router' => include __DIR__ . '/router.config.php',
    'translator' => array(
    		'translation_file_patterns' => array(
    				array(
    						'type'     => 'gettext',
    						'base_dir' => __DIR__ . '/../language',
    						'pattern'  => '%s.mo',
    				),
    		),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'Goods' => __DIR__ . '/../view'
        ),
    ),
);
