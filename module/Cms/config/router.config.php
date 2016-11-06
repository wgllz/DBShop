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
            'cms' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/' . DBSHOP_ADMIN_DIR . '/cms',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Cms\Controller',
                        'controller'    => 'Cms',
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
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'article_class_id' => array(
                                    'type' => 'Segment',
                                    'options' => array(
                                            'route' => '[/:article_class_id]',
                                            'constraints' => array(
                                            ),
                                            'defaults' => array(
                                            )
                                    )
                            ),
                            'article_id' => array(
                                    'type' => 'Segment',
                                    'options' => array(
                                            'route' => '/article[/:article_id][/:page]',
                                            'constraints' => array(
                                                    'article_id' => '[0-9_-]+'
                                            ),
                                            'defaults' => array(
                                                    'article_id' => 1
                                            )
                                    )
                            ),
                            'article_page' => array(
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