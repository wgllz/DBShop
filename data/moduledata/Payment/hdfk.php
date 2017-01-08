<?php
return array(
    'payment_name' => array(
        'title' => '支付方式名称',
        'content' => '货到付款',
        'name_id' => 'payment_name',
        'input_type' => 'text',
        'class' => 'span6',
    ),
    'payment_logo' => array(
        'title' => '支付方式LOGO',
        'content' => '/public/img/payment/hdfk.gif',
        'name_id' => 'payment_logo',
        'input_type' => 'image',
    ),
    'payment_info' => array(
        'title' => '支付方式简介',
        'content' => '货到付款',
        'name_id' => 'payment_info',
        'input_type' => 'textarea',
        'class' => 'span8',
    ),
    'payment_show' => array(
        'title' => '显示设置',
        'content' => array(
            'content1' => array(
                'name' => '全平台显示',
                'value' => 'all',
            ),
        ),
        'name_id' => 'payment_show',
        'input_type' => 'checkbox',
        'checked' => 'all',
    ),
    'payment_fee' => array(
        'title' => '支付手续费',
        'content' => '',
        'name_id' => 'payment_fee',
        'input_type' => 'text',
        'class' => 'span2',
    ),
    'payment_state' => array(
        'title' => '支付状态',
        'content' => array(
            'radio_1' => array(
                'name' => '开启',
                'value' => '1',
            ),
            'radio_2' => array(
                'name' => '关闭',
                'value' => '0',
            ),
        ),
        'name_id' => 'payment_state',
        'input_type' => 'radio',
        'checked' => '1',
    ),
    'payment_sort' => array(
        'title' => '支付排序',
        'content' => '255',
        'name_id' => 'payment_sort',
        'input_type' => 'text',
        'class' => 'span1',
    ),
    'editaction' => 'hdfk',
    'orders_state' => '30',
);
