<?php
return array(
    'payment_name' => array(
        'title' => '支付方式名称',
        'content' => '微信支付',
        'name_id' => 'payment_name',
        'input_type' => 'text',
        'class' => 'span6',
    ),
    'payment_logo' => array(
        'title' => '支付方式LOGO',
        'content' => '/public/img/payment/wxmpay.png',
        'name_id' => 'payment_logo',
        'input_type' => 'image',
    ),
    'payment_info' => array(
        'title' => '支付方式简介',
        'content' => '微信支付',
        'name_id' => 'payment_info',
        'input_type' => 'textarea',
        'class' => 'span8',
    ),
    'payment_type' => array(
        'title' => '微信支付类型',
        'content' => array(
            'option_1' => array(
                'name' => '手机端支付',
                'value' => 'JSAPI',
            ),
        ),
        'name_id' => 'payment_type',
        'input_type' => 'select',
        'selected' => 'JSAPI',
    ),
    'wxpay_appid' => array(
        'title' => 'APPID',
        'content' => '',
        'name_id' => 'wxpay_appid',
        'input_type' => 'text',
        'class' => 'span6',
    ),
    'wxpay_mchid' => array(
        'title' => 'MCHID(商户号)',
        'content' => '',
        'name_id' => 'wxpay_mchid',
        'input_type' => 'text',
        'class' => 'span6',
    ),
    'wxpay_key' => array(
        'title' => 'KEY(支付密钥)',
        'content' => '',
        'name_id' => 'wxpay_key',
        'input_type' => 'text',
        'class' => 'span6',
    ),
    'wxpay_appsecret' => array(
        'title' => 'AppSecret(公众号的)',
        'content' => '',
        'name_id' => 'wxpay_appsecret',
        'input_type' => 'text',
        'class' => 'span6',
    ),
    'payment_show' => array(
        'title' => '显示设置',
        'content' => array(
            'content2' => array(
                'name' => '手机端显示',
                'value' => 'phone',
            ),
        ),
        'name_id' => 'payment_show',
        'input_type' => 'checkbox',
        'checked' => 'phone',
    ),
    'payment_currency' => array(
        'title' => '可用货币',
        'content' => array(
            'content1' => array(
                'name' => '人民币',
                'value' => 'CNY',
            ),
        ),
        'name_id' => 'payment_currency',
        'input_type' => 'checkbox',
        'checked' => 'CNY',
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
    'editaction' => 'wxmpay',
    'orders_state' => '10',
);
