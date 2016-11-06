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
        'routes' => array(
            'system' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/' . DBSHOP_ADMIN_DIR . '/system',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'System\Controller',
                        'controller'    => 'System',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // This route is a sane default when developing a module;
                    // as you solidify the routes for your module, however,
                    // you may want to remove it and replace it with more
                    // specific routes.
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '[/:action]',
                            'constraints' => array(
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                'action' => 'index'
                            ),
                        ),
                    ),
                ),
            ),
            'optimization' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/' . DBSHOP_ADMIN_DIR . '/optimization',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'System\Controller',
                        'controller'    => 'Optimization',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // This route is a sane default when developing a module;
                    // as you solidify the routes for your module, however,
                    // you may want to remove it and replace it with more
                    // specific routes.
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '[/:action]',
                            'constraints' => array(
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                'action' => 'index'
                            ),
                        ),
                    ),
                ),
            ),
            // 在线客服
            'online' => array(
                    'type' => 'Literal',
                    'options' => array(
                            'route' => '/' . DBSHOP_ADMIN_DIR . '/online',
                            'defaults' => array(
                                    '__NAMESPACE__' => 'System\Controller',
                                    'controller' => 'Online',
                                    'action' => 'index'
                            )
                    ),
                    'may_terminate' => true,
                    'child_routes' => array(
                            'default' => array(
                                    'type' => 'Segment',
                                    'options' => array(
                                            'route' => '[/:action]',
                                            'constraints' => array(
                                                    'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                                            )
                                    ),
                                    'may_terminate' => true,
                                    'child_routes' => array(
                                            'online_id' => array(
                                            'type' => 'Segment',
                                            'options' => array(
                                            'route' => '[/:online_id]'
                                                    )
                                            ),
                                        'online_group_id' => array(
                                                'type' => 'Segment',
                                                'options' => array(
                                                        'route' => '/online_group[/:online_group_id]'
                                                )
                                        )
                                    )
                            )
                    )
            )   
        )
    );