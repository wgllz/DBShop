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
            'Mobile\Controller\Index' => 'Mobile\Controller\IndexController',
            'Mobile\Controller\Class' => 'Mobile\Controller\ClassController',
            'Mobile\Controller\Cart'  => 'Mobile\Controller\CartController',
            'Mobile\Controller\Goods' => 'Mobile\Controller\GoodsController',
            'Mobile\Controller\User'  => 'Mobile\Controller\UserController',
            'Mobile\Controller\Home'  => 'Mobile\Controller\HomeController',
            'Mobile\Controller\Order' => 'Mobile\Controller\OrderController',
            'Mobile\Controller\Address' => 'Mobile\Controller\AddressController',
            'Mobile\Controller\Favorites' => 'Mobile\Controller\FavoritesController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'mobile'  => include __DIR__ . '/router/index.router.php',
            'm_class' => include __DIR__ . '/router/class.router.php',
            'm_goods' => include __DIR__ . '/router/goods.router.php',
            'm_cart'  => include __DIR__ . '/router/cart.router.php',
            'm_user'  => include __DIR__ . '/router/user.router.php',
            'm_home'  => include __DIR__ . '/router/home.router.php',
            'm_order' => include __DIR__ . '/router/order.router.php',
            'm_address' => include __DIR__ . '/router/address.router.php',
            'm_favorites' => include __DIR__ . '/router/favorites.router.php',
        ),
    ),
    'translator' => array(
        'translation_file_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language/'.(defined('DBSHOP_PHONE_TEMPLATE') ? DBSHOP_PHONE_TEMPLATE : 'default'),
                'pattern' => '%s.mo'
            )
        )
    ),
    'view_manager' => array(
        'template_map' => array(
            'mobile/layout'  => __DIR__ . '/../view/'.(defined('DBSHOP_PHONE_TEMPLATE') ? DBSHOP_PHONE_TEMPLATE : 'default').'/mobile/common/index.phtml',
            'mobile/header'  => __DIR__ . '/../view/'.(defined('DBSHOP_PHONE_TEMPLATE') ? DBSHOP_PHONE_TEMPLATE : 'default').'/mobile/common/header.phtml',
            'mobile/footer'  => __DIR__ . '/../view/'.(defined('DBSHOP_PHONE_TEMPLATE') ? DBSHOP_PHONE_TEMPLATE : 'default').'/mobile/common/footer.phtml',
            'mobile/ajaxpage'=> __DIR__ . '/../view/'.(defined('DBSHOP_PHONE_TEMPLATE') ? DBSHOP_PHONE_TEMPLATE : 'default').'/mobile/common/ajaxpage.phtml',
            'mobile/moreajax'=> __DIR__ . '/../view/'.(defined('DBSHOP_PHONE_TEMPLATE') ? DBSHOP_PHONE_TEMPLATE : 'default').'/mobile/common/moreajax.phtml',
            'mobile/page'    => __DIR__ . '/../view/'.(defined('DBSHOP_PHONE_TEMPLATE') ? DBSHOP_PHONE_TEMPLATE : 'default').'/mobile/common/page.phtml',
        ),
        'template_path_stack' => array(
            'Mobile' => __DIR__ . '/../view/'.(defined('DBSHOP_PHONE_TEMPLATE') ? DBSHOP_PHONE_TEMPLATE : 'default'),
        ),
    ),
);
