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
            'Admin\Controller\Admin' => 'Admin\Controller\AdminController',
            'Admin\Controller\Home' => 'Admin\Controller\HomeController'
        )
    ),
    'router' => array(
        'routes' => array(
            'admin' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/' . DBSHOP_ADMIN_DIR,
                    'defaults' => array(
                        '__NAMESPACE__' => 'Admin\Controller',
                        'controller' => 'Admin',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '[/[:action]]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'action' => 'index'
                            )
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'admin_group_id' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '[/:admin_group_id]'
                                )
                            ),
                            'admin_id' => array(
                                    'type' => 'Segment',
                                    'options' => array(
                                            'route' => '/admin_id[/:admin_id][/:check_type]'
                                    )
                            )
                        )
                    )
                )
            ),
            
            'adminHome' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/' . DBSHOP_ADMIN_DIR . '/home',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Admin\Controller',
                        'controller' => 'home',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '[/[:action]]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'action' => 'index'
                            )
                        )
                    )
                )
            )
        )
    ),
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
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/admin.phtml',
            'layout/header' => __DIR__ . '/../view/layout/admin_header_layout.phtml',
            'layout/footer' => __DIR__ . '/../view/layout/admin_footer_layout.phtml',
            'error/404'     => __DIR__ . '/../view/error/404.phtml',
            'error/index'   => __DIR__ . '/../view/error/index.phtml',
            'common/pager'      => __DIR__ . '/../view/common/pager.phtml',
            'common/ajax-pages' => __DIR__ . '/../view/common/dbshop-ajax-pages.phtml',
                ),
        'template_path_stack' => array(
            'Admin' => __DIR__ . '/../view'
        )
    )
);
