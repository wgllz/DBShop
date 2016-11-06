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
                // 商品品牌
                'brand'     => array(
                        'type'          => 'Literal',
                        'options'       => array(
                                'route'    => '/' . DBSHOP_ADMIN_DIR . '/goods/brand',
                                'defaults' => array(
                                        '__NAMESPACE__' => 'Goods\Controller',
                                        'controller'    => 'Brand',
                                        'action'        => 'index'
                                )
                        ),
                        // 子路由，品牌id和page
                        'may_terminate' => true,
                        'child_routes'  => array(
                                'default' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                                'route'       => '[/:action][/:brand_id][/:page]',
                                                'constraints' => array(
                                                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                                                )
                                        )
                                )
                        )
                ),
                // 商品属性
                'attribute' => array(
                        'type'          => 'Literal',
                        'options'       => array(
                                'route'    => '/' . DBSHOP_ADMIN_DIR . '/goods/attribute',
                                'defaults' => array(
                                        '__NAMESPACE__' => 'Goods\Controller',
                                        'controller'    => 'Attribute',
                                        'action'        => 'index'
                                )
                        ),
                        // 子路由，品牌id和page
                        'may_terminate' => true,
                        'child_routes'  => array(
                                'default' => array(
                                        'type'          => 'Segment',
                                        'options'       => array(
                                                'route'       => '[/:action]',
                                                'constraints' => array(
                                                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                                                )
                                        ),
                                        'may_terminate' => true,
                                        'child_routes'  => array(
                                                'attribute-id'       => array(
                                                        'type'          => 'Segment',
                                                        'options'       => array(
                                                                'route'       => '[/:attribute_id]',
                                                                'constraints' => array()
                                                        ),
                                                        'may_terminate' => true,
                                                        'child_routes'  => array(
                                                                'attribute-value-id' => array(
                                                                    'type'  =>'Segment',
                                                                    'options'       => array(
                                                                            'route'       => '/value_id[/:value_id]',
                                                                            'constraints' => array()
                                                                    )
                                                                )
                                                        )
                                                ),
                                                'attribute-group-id' => array(
                                                        'type'    => 'Segment',
                                                        'options' => array(
                                                                'route'       => '/attr_group_id[/:attribute_group_id]',
                                                                'constraints' => array()
                                                        )
                                                )
                                        )
                                )
                        )
                ),
                // 商品标签
                'tag'       => array(
                        'type'          => 'Literal',
                        'options'       => array(
                                'route'    => '/' . DBSHOP_ADMIN_DIR . '/goods/tag',
                                'defaults' => array(
                                        '__NAMESPACE__' => 'Goods\Controller',
                                        'controller'    => 'Tag',
                                        'action'        => 'index'
                                )
                        ),
                        // 子路由，action和标签id
                        'may_terminate' => true,
                        'child_routes'  => array(
                                'default'         => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                                'route'       => '[/:action][/:tag_id][/:page]',
                                                'constraints' => array(
                                                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                                                )
                                        )
                                ),
                                'goods_tag_group' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                                'route'       => '/taggroup[/:action][/:tag_group_id]',
                                                'constraints' => array(
                                                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                                                )
                                        )
                                )
                        )
                ),
                // 商品评价
                'comment'   => array(
                        'type'          => 'Literal',
                        'options'       => array(
                                'route'    => '/' . DBSHOP_ADMIN_DIR . '/goods/comment',
                                'defaults' => array(
                                        '__NAMESPACE__' => 'Goods\Controller',
                                        'controller'    => 'Comment',
                                        'action'        => 'index'
                                )
                        ),
                        // 子路由，品牌id和page
                        'may_terminate' => true,
                        'child_routes'  => array(
                                'default' => array(
                                        'type'          => 'Segment',
                                        'options'       => array(
                                                'route'       => '[/:action]',
                                                'constraints' => array(
                                                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                                                )
                                        ),
                                        'may_terminate' => true,
                                        'child_routes'  => array(
                                                'comment-page' => array(
                                                        'type'    => 'Segment',
                                                        'options' => array(
                                                                'route'       => '/page[/:page]',
                                                                'constraints' => array(
                                                                        'page' => '[0-9_-]+'
                                                                ),
                                                                'defaults'    => array(
                                                                        'page' => 1
                                                                )
                                                        )
                                                ),
                                                'comment-id'   => array(
                                                        'type'    => 'Segment',
                                                        'options' => array(
                                                                'route'       => '/comment_id[/:comment_id][/:goods_id]',
                                                                'constraints' => array()
                                                        )
                                                ),
                                                'goods-id'     => array(
                                                        'type'    => 'Segment',
                                                        'options' => array(
                                                                'route'       => '[/:goods_id][/:page]',
                                                                'constraints' => array(),
                                                                'defaults'    => array(
                                                                        'page' => 1
                                                                )
                                                        )
                                                ),
                                            'user-name'     => array(
                                                    'type'    => 'Segment',
                                                    'options' => array(
                                                            'route'       => '[/:user_name]/com_page[/:page]',
                                                            'constraints' => array(),
                                                            'defaults'    => array(
                                                                    'page' => 1
                                                            )
                                                    )
                                            )
                                        )
                                )
                        )
                ),
                //商品咨询
                'ask'       => array(
                        'type'          => 'Literal',
                        'options'       => array(
                                'route'    => '/' . DBSHOP_ADMIN_DIR . '/goods/ask',
                                'defaults' => array(
                                        '__NAMESPACE__' => 'Goods\Controller',
                                        'controller'    => 'Ask',
                                        'action'        => 'index'
                                )
                        ),
                        'may_terminate' => true,
                        'child_routes'  => array(
                                'default' => array(
                                        'type'          => 'segment',
                                        'options'       => array(
                                                'route'       => '[/:action]',
                                        ),
                                        'may_terminate' => true,
                                        'child_routes'  => array(
                                                'ask-page' => array(
                                                        'type'  => 'Segment',
                                                        'options' => array(
                                                        'route'       => '/askpage[/:page]',
                                                        'constraints' => array(
                                                        'page' => '[0-9_-]+'
                                                                ),
                                                                'defaults' => array(
                                                                'page' => 1
                                                                )
                                                        )
                                                ),
                                                'ask-id' => array(
                                                		'type'  => 'Segment',
                                                		'options' => array(
                                                				'route'       => '/askid[/:askid]',
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
                // 商品分类
                'class'     => array(
                        'type'          => 'Literal',
                        'options'       => array(
                                'route'    => '/' . DBSHOP_ADMIN_DIR . '/goods/class',
                                'defaults' => array(
                                        '__NAMESPACE__' => 'Goods\Controller',
                                        'controller'    => 'Class',
                                        'action'        => 'index'
                                )
                        ),
                        // 子路由，action
                        'may_terminate' => true,
                        'child_routes'  => array(
                                'default' => array(
                                        'type'          => 'segment',
                                        'options'       => array(
                                                'route'       => '[/:action]',
                                                'constraints' => array(
                                                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                                                )
                                        ),
                                        // 子路由，action下，分类id|子类添加|分类内商品分页
                                        'may_terminate' => true,
                                        'child_routes'  => array(
                                                'class_id'     => array(
                                                        'type'    => 'Segment',
                                                        'options' => array(
                                                                'route' => '[/:class_id]'
                                                        )
                                                ),
                                                'top_class_id' => array(
                                                        'type'    => 'Segment',
                                                        'options' => array(
                                                                'route' => '/c/sub_class[/:top_class_id]'
                                                        )
                                                ),
                                                'page'         => array(
                                                        'type'    => 'Segment',
                                                        'options' => array(
                                                                'route'       => '[/:class_id][/:page]',
                                                                'constraints' => array(
                                                                        'page' => '[0-9_-]+'
                                                                ),
                                                                'defaults'    => array(
                                                                        'page' => 1
                                                                )
                                                        )
                                                )
                                        )
                                )
                        )
                ),
                // 商品
                'goods'     => array(
                        'type'          => 'Literal',
                        'options'       => array(
                                'route'    => '/' . DBSHOP_ADMIN_DIR . '/goods/goods',
                                'defaults' => array(
                                        '__NAMESPACE__' => 'Goods\Controller',
                                        'controller'    => 'Goods',
                                        'action'        => 'index'
                                )
                        ),
                        // 子路由
                        'may_terminate' => true,
                        'child_routes'  => array(
                                'default' => array(
                                        'type'          => 'Segment',
                                        'options'       => array(
                                                'route'       => '[/:action]',
                                                'constraints' => array(
                                                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                                                ),
                                                'defaults'    => array(
                                                        'action' => 'index'
                                                )
                                        ),
                                        // 子路由，action下商品id|商品分页|分类列表直接添加商品链接
                                        'may_terminate' => true,
                                        'child_routes'  => array(
                                                'goods_id'       => array(
                                                        'type'     => 'Segment',
                                                        'options'  => array(
                                                                'route'       => '[/:goods_id][/:page]',
                                                                'constraints' => array(
                                                                        'goods_id' => '[0-9_-]+'
                                                                )
                                                        ),
                                                        'defaults' => array(
                                                                'controller' => 'Goods',
                                                                'action'     => 'index'
                                                        )
                                                ),
                                                'page'           => array(
                                                        'type'    => 'Segment',
                                                        'options' => array(
                                                                'route'       => '/page[/:page]',
                                                                'constraints' => array(
                                                                        'page' => '[0-9_-]+'
                                                                ),
                                                                'defaults'    => array(
                                                                        'page'   => 1
                                                                )
                                                        )
                                                ),
                                                'goods_class_id' => array(
                                                        'type'     => 'Segment',
                                                        'options'  => array(
                                                                'route' => '/class_id[/:goods_class_id]'
                                                        ),
                                                        'defaults' => array(
                                                                'action' => 'index'
                                                        )
                                                )
                                        )
                                )
                        )
                ),
                // 促销规则
                'promotions'     => array(
                        'type'          => 'Literal',
                        'options'       => array(
                                'route'    => '/' . DBSHOP_ADMIN_DIR . '/goods/promotions',
                                'defaults' => array(
                                        '__NAMESPACE__' => 'Goods\Controller',
                                        'controller'    => 'Promotions',
                                        'action'        => 'index'
                                )
                        ),
                        // 子路由，action
                        'may_terminate' => true,
                        'child_routes'  => array(
                                'default' => array(
                                        'type'          => 'segment',
                                        'options'       => array(
                                                'route'       => '[/:action]',
                                                'constraints' => array(
                                                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                                                )
                                        ),
                                        'may_terminate' => true,
                                        'child_routes'  => array(
                                                'promotions_id'     => array(
                                                        'type'    => 'Segment',
                                                        'options' => array(
                                                                'route' => '[/:promotions_id]'
                                                        )
                                                )
                                        )
                                )
                        )
                )              
        )
);
