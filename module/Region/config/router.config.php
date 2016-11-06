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
        // 地区
        'region' => array(
            'type' => 'Literal',
            'options' => array(
                'route' => '/' . DBSHOP_ADMIN_DIR . '/region',
                'defaults' => array(
                    '__NAMESPACE__' => 'Region\Controller',
                    'controller' => 'Region',
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
                        'region_id' => array(
                            'type' => 'Segment',
                            'options' => array(
                                'route' => '[/:region_id]'
                            )
                        ),
                        'region_top_id' => array(
                                'type' => 'Segment',
                                'options' => array(
                                        'route' => '/top_id[/:region_top_id]'
                                )
                        ),
                        'region_type' => array(
                                'type' => 'Segment',
                                'options' => array(
                                        'route' => '/select[/:region_type]'
                                )
                        )
                    )
                )
            )
        )
    )
);