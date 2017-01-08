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
    'type'    => 'Literal',
    'options' => array(
        'route'    => '/wxpay',
        'defaults' => array(
            '__NAMESPACE__' => 'Mobile\Controller',
            'controller'    => 'Wx',
            'action'        => 'index',
        ),
    ),
    'may_terminate' => true,
    'child_routes' => array(
        'default' => array(
            'type'    => 'Segment',
            'options' => array(
                'route'    => '[/:action]_',
                'constraints' => array(
                    'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                ),
                'defaults' => array(
                    'action'=>'index'
                ),
            ),
            'may_terminate' => true,
            'child_routes' => array(
                'wx_order_id' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '[:order_id].html',
                        'constraints' => array(
                        )
                    )
                )


            )
        ),
    ),
);