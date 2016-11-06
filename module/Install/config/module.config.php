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
            'Install\Controller\Install' => 'Install\Controller\InstallController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'install' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/install',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Install\Controller',
                        'controller'    => 'Install',
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
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                                'install_step' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                                'route' => '[/:step]',
                                                'constraints' => array(
                                                ),
                                                'defaults' => array(
                                                )
                                        )
                                ),
                        )
                    ),
                ),
            ),
        ),
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
        'template_map' => array(
                 'install/layout'  => __DIR__ . '/../view/install/install/install_layout.phtml',
            ),
        'template_path_stack' => array(
            'Install' => __DIR__ . '/../view',
        ),
    ),
);
