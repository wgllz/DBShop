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
                'template' => array(
                        'type' => 'Literal',
                        'options' => array(
                                // Change this to something specific to your
                                // module
                                'route' => '/' . DBSHOP_ADMIN_DIR . '/template',
                                'defaults' => array(
                                        // Change this value to reflect the
                                        // namespace in which
                                        // the controllers for your module are
                                        // found
                                        '__NAMESPACE__' => 'Template\Controller',
                                        'controller' => 'Template',
                                        'action' => 'index'
                                )
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                                // This route is a sane default when developing
                                // a module;
                                // as you solidify the routes for your module,
                                // however,
                                // you may want to remove it and replace it with
                                // more
                                // specific routes.
                                'default' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                                'route' => '/[:controller[/:action]]',
                                                'constraints' => array(
                                                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                                                ),
                                                'defaults' => array()
                                        ),
                                        'may_terminate' => true,
                                        'child_routes' => array(
                                                'templateinfo' => array(
                                                        'type'=> 'Segment',
                                                        'options' => array(
                                                                'route'       => '/template-name[/:template_name]',
                                                                'constraints' => array()
                                                        )
                                                )
                                        )
                                )
                        )
                ),
            'phonetemplate' => array(
                'type' => 'Literal',
                'options' => array(
                    // Change this to something specific to your
                    // module
                    'route' => '/' . DBSHOP_ADMIN_DIR . '/phonetemplate',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Template\Controller',
                        'controller' => 'Phonetemplate',
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
                                'action'=>'index'
                            )
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'p-templateinfo' => array(
                                'type'=> 'Segment',
                                'options' => array(
                                    'route'       => '/p-template-name[/:p_template_name]',
                                    'constraints' => array()
                                )
                            )
                        )

                    )
                )
            )
        )
);