<?php
return array(
    'payment_name' => array(
        'title' => '支付方式名称',
        'content' => 'PayPal支付',
        'name_id' => 'payment_name',
        'input_type' => 'text',
        'class' => 'span6',
    ),
    'payment_logo' => array(
        'title' => '支付方式LOGO',
        'content' => '/public/img/payment/paypal.jpg',
        'name_id' => 'payment_logo',
        'input_type' => 'image',
    ),
    'payment_info' => array(
        'title' => '支付方式简介',
        'content' => 'PayPal支付国际支付账户',
        'name_id' => 'payment_info',
        'input_type' => 'textarea',
        'class' => 'span8',
    ),
    'payment_type' => array(
        'title' => 'PayPal账户类型',
        'content' => array(
            'option_1' => array(
                'name' => 'sandbox测试账户',
                'value' => 'sandbox',
            ),
            'option_2' => array(
                'name' => 'PayPal真实账户',
                'value' => 'real',
            ),
        ),
        'name_id' => 'payment_type',
        'input_type' => 'select',
        'selected' => 'real',
    ),
    'payment_user' => array(
        'title' => 'PayPal API帐号',
        'content' => '',
        'name_id' => 'paypal_api_user',
        'input_type' => 'text',
        'class' => 'span6',
    ),
    'payment_passwd' => array(
        'title' => 'PayPal API密码',
        'content' => '',
        'name_id' => 'paypal_api_passwd',
        'input_type' => 'text',
        'class' => 'span6',
    ),
    'payment_sign' => array(
        'title' => 'PayPal API签名',
        'content' => '',
        'name_id' => 'paypal_api_sign',
        'input_type' => 'text',
        'class' => 'span6',
    ),
    'payment_show' => array(
        'title' => '显示设置',
        'content' => array(
            'content1' => array(
                'name' => '电脑端显示',
                'value' => 'pc',
            ),
            'content2' => array(
                'name' => '手机端显示',
                'value' => 'phone',
            ),
        ),
        'name_id' => 'payment_show',
        'input_type' => 'checkbox',
        'checked' => array(
            0 => 'pc',
            1 => 'phone',
        ),
    ),
    'payment_currency' => array(
        'title' => '可用货币',
        'content' => array(
            'content1' => array(
                'name' => '美元',
                'value' => 'USD',
            ),
            'content2' => array(
                'name' => '加拿大元',
                'value' => 'CAD',
            ),
            'content3' => array(
                'name' => '欧元',
                'value' => 'EUR',
            ),
            'content4' => array(
                'name' => '英镑',
                'value' => 'GBP',
            ),
            'content5' => array(
                'name' => '日元',
                'value' => 'JPY',
            ),
            'content6' => array(
                'name' => '澳元',
                'value' => 'AUD',
            ),
            'content7' => array(
                'name' => '新西兰元',
                'value' => 'NZD',
            ),
            'content8' => array(
                'name' => '瑞士法郎',
                'value' => 'CHF',
            ),
            'content9' => array(
                'name' => '港币',
                'value' => 'HKD',
            ),
            'content10' => array(
                'name' => '新加坡元',
                'value' => 'SGD',
            ),
            'content11' => array(
                'name' => '福林',
                'value' => 'HUF',
            ),
        ),
        'name_id' => 'payment_currency',
        'input_type' => 'checkbox',
        'checked' => array(
            0 => 'USD',
            1 => 'CAD',
            2 => 'EUR',
            3 => 'NZD',
            4 => 'CHF',
            5 => 'HKD',
        ),
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
        'checked' => '0',
    ),
    'payment_sort' => array(
        'title' => '支付排序',
        'content' => '255',
        'name_id' => 'payment_sort',
        'input_type' => 'text',
        'class' => 'span1',
    ),
    'editaction' => 'paypal',
    'orders_state' => '10',
);
