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
        // 客户
        'user' => array(
            'type' => 'Literal',
            'options' => array(
                'route' => '/' . DBSHOP_ADMIN_DIR . '/user',
                'defaults' => array(
                    '__NAMESPACE__' => 'User\Controller',
                    'controller' => 'User',
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
                        'user_id' => array(
                            'type' => 'Segment',
                            'options' => array(
                                'route' => '[/:user_id]'
                            ),
                            'may_terminate' => true,
                            'child_routes' => array(
                                'other-page' => array(
                                    'type' => 'Segment',
                                    'options' => array(
                                        'route' => '/other[/:page]'
                                        )
                                    )
                                )
                        ),
                        'page' => array(
                            'type' => 'Segment',
                            'options' => array(
                                'route' => '/page[/:page]',
                                'constraints' => array(
                                    'page' => '[0-9_-]+'
                                ),
                                'defaults' => array(
                                    'page' => 1
                                )
                            )
                        ),
                        'mail-log-page' => array(
                            'type' => 'Segment',
                            'options' => array(
                                'route' => '/mail[/:user_id][/:page]',
                                'constraints' => array(
                                    'page' => '[0-9_-]+'
                                ),
                                'defaults' => array(
                                    'page' => 1
                                )
                            )
                        )
                    )
                )
            )
        ),
        // 客户组
        'usergroup' => array(
            'type' => 'Literal',
            'options' => array(
                'route' => '/' . DBSHOP_ADMIN_DIR . '/user/group',
                'defaults' => array(
                    '__NAMESPACE__' => 'User\Controller',
                    'controller' => 'Usergroup',
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
                        'user_group_id' => array(
                            'type' => 'Segment',
                            'options' => array(
                                'route' => '[/:user_group_id]'
                            )
                        )
                    )
                )
            )
        ),
        // 会员余额
        'usermoney' => array(
            'type' => 'Literal',
            'options' => array(
                'route' => '/' . DBSHOP_ADMIN_DIR . '/user/money',
                'defaults' => array(
                    '__NAMESPACE__' => 'User\Controller',
                    'controller' => 'Usermoney',
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
                    'child_routes'  => array(
                        'money_log_page' => array(
                            'type' => 'Segment',
                            'options' => array(
                                'route' => '/m-log-page[/:page]'
                            )
                        )
                    )
                )
            )
        ),
        //客户整合接口
        'userintegration' => array(
                'type' => 'Literal',
                'options' => array(
                        'route' => '/' . DBSHOP_ADMIN_DIR . '/user/integration',
                        'defaults' => array(
                                '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'Userintegration',
                                'action' => 'index'
                        )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                        'default' => array(
                                'type' => 'Segment',
                                'options' => array(
                                        'route' => '[/:action][/:integrationtype]',
                                        'constraints' => array(
                                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                                        )
                                )
                        )
                )
        ),
        //积分
        'integral' => array(
                'type' => 'Literal',
                'options' => array(
                        'route' => '/' . DBSHOP_ADMIN_DIR . '/user/integral',
                        'defaults' => array(
                                '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'Integral',
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
                                'child_routes'  => array(
                                        'integral_rule_id'     => array(
                                                'type'    => 'Segment',
                                                'options' => array(
                                                        'route' => '[/:integral_rule_id]'
                                                )
                                        ),
                                        'integral_type_id'     => array(
                                            'type'    => 'Segment',
                                            'options' => array(
                                                'route' => '/typeId[/:integral_type_id]'
                                            )
                                        ),
                                        'integrallog_page' => array(
                                                'type' => 'Segment',
                                                'options' => array(
                                                        'route' => '/ingegrallogpage[/:page]'
                                                )
                                        )
                                )                                
                        )
                )
        )        
    )
);