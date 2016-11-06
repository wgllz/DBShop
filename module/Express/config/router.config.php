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
                'express' => array(
                        'type' => 'Literal',
                        'options' => array(
                                'route' => '/' . DBSHOP_ADMIN_DIR . '/express',
                                'defaults' => array(
                                        '__NAMESPACE__' => 'Express\Controller',
                                        'controller' => 'Express',
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
                                                ),
                                                'defaults' => array(
                                                        'action' => 'index'
                                                )
                                        ),
                                        'may_terminate' => true,
                                        'child_routes' => array(
                                                'express-id' => array(
                                                        'type' => 'Segment',
                                                        'options' => array(
                                                                'route' => '[/:express_id]',
                                                                'constraints' => array(
                                                                        'express_id' => '[0-9_-]+'
                                                                )
                                                        )
                                                ),
                                                'express-name-code' => array(
                                                        'type' => 'Segment',
                                                        'options' => array(
                                                                'route' => '/name_code[/:express_code]',
                                                                'constraints' => array()
                                                        )
                                                )
                                        )
                                )
                        )
                )
        )
);
