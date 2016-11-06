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
            'Shopfront\Controller\Index'     => 'Shopfront\Controller\IndexController',
            'Shopfront\Controller\Home'      => 'Shopfront\Controller\HomeController',
            'Shopfront\Controller\User'      => 'Shopfront\Controller\UserController',
            'Shopfront\Controller\Goodslist' => 'Shopfront\Controller\GoodslistController',
            'Shopfront\Controller\Goods'     => 'Shopfront\Controller\GoodsController',
            'Shopfront\Controller\Cart'      => 'Shopfront\Controller\CartController',
            'Shopfront\Controller\Order'     => 'Shopfront\Controller\OrderController',
            'Shopfront\Controller\Homeaddress'=> 'Shopfront\Controller\HomeaddressController',
            'Shopfront\Controller\Article'   => 'Shopfront\Controller\ArticleController',
            'Shopfront\Controller\Homefavorites'=> 'Shopfront\Controller\HomefavoritesController',
            'Shopfront\Controller\Homemoney' => 'Shopfront\Controller\HomemoneyController',
            'Shopfront\Controller\Brand'     => 'Shopfront\Controller\BrandController',
            'Shopfront\Controller\Homerefund'=> 'Shopfront\Controller\HomerefundController',
        ),
    ),
    'router' => array(
            'routes' => array(
                    'shopfront'         => include __DIR__ . '/router/index.router.php',
                    'frontuser'         => include __DIR__ . '/router/fuser.router.php',
                    'fronthome'         => include __DIR__ . '/router/home.router.php',
                    'frontgoodslist'    => include __DIR__ . '/router/goodslist.router.php',
                    'frontgoods'        => include __DIR__ . '/router/goods.router.php',
                    'frontcart'         => include __DIR__ . '/router/cart.router.php',
                    'frontorder'        => include __DIR__ . '/router/order.router.php',
                    'frontaddress'      => include __DIR__ . '/router/address.router.php',
                    'frontarticle'      => include __DIR__ . '/router/article.router.php',
                    'frontfavorites'    => include __DIR__ . '/router/favorites.router.php',
                    'frontbrand'        => include __DIR__ . '/router/brand.router.php',
                    'frontmoney'        => include __DIR__ . '/router/money.router.php',
                    'frontrefund'       => include __DIR__ . '/router/refund.router.php',
            ),
    ),
    'translator' => array(
            'translation_file_patterns' => array(
                    array(
                            'type' => 'gettext',
                            'base_dir' => __DIR__ . '/../language/'.DBSHOP_TEMPLATE,
                            'pattern' => '%s.mo'
                    )
            )
    ),
    'view_manager' => array(
        'template_map' => array(
                'site/layout'  => __DIR__ . '/../view/'.DBSHOP_TEMPLATE.'/shopfront/common/index.phtml',
                'site/header'  => __DIR__ . '/../view/'.DBSHOP_TEMPLATE.'/shopfront/common/header.phtml',
                'site/footer'  => __DIR__ . '/../view/'.DBSHOP_TEMPLATE.'/shopfront/common/footer.phtml',
                'site/kefu'    => __DIR__ . '/../view/'.DBSHOP_TEMPLATE.'/shopfront/common/kefu.phtml', 
                'site/dbpage'  => __DIR__ . '/../view/'.DBSHOP_TEMPLATE.'/shopfront/common/pager.phtml',
				'site/ajax-dbpage'  => __DIR__ . '/../view/'.DBSHOP_TEMPLATE.'/shopfront/common/ajax-pages.phtml',
                'site/headermenu' => __DIR__ . '/../view/'.DBSHOP_TEMPLATE.'/shopfront/common/indexmenu.phtml',
        ),
        'template_path_stack' => array(
            'Shopfront' => __DIR__ . '/../view/'.DBSHOP_TEMPLATE,
        ),
    ),
);
