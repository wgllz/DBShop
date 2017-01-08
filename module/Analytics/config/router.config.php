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

return array(
        'routes' => array(
            'analytics' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/' . DBSHOP_ADMIN_DIR . '/analytics',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Analytics\Controller',
                        'controller'    => 'Analytics',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '[/:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                'controller'    => 'Analytics',
                                'action'        => 'index'
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
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
                        )
                    ),
                ),
            ),
        )  
);