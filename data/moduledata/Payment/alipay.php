<?php
return array(
    'payment_name' => array(
        'title' => '支付方式名称',
        'content' => '支付宝支付',
        'name_id' => 'payment_name',
        'input_type' => 'text',
        'class' => 'span6',
    ),
    'payment_logo' => array(
        'title' => '支付方式LOGO',
        'content' => '/public/img/payment/alipay.gif',
        'name_id' => 'payment_logo',
        'input_type' => 'image',
    ),
    'payment_info' => array(
        'title' => '支付方式简介',
        'content' => '支付宝，便捷的支付方式，大陆最好的支付系统',
        'name_id' => 'payment_info',
        'input_type' => 'textarea',
        'class' => 'span8',
    ),
    'payment_user' => array(
        'title' => '支付宝收款账户',
        'content' => '',
        'name_id' => 'payment_user',
        'input_type' => 'text',
        'class' => 'span6',
    ),
    'payment_type' => array(
        'title' => '支付宝签约接口',
        'content' => array(
            'option_1' => array(
                'name' => '即时到账交易接口',
                'value' => 'create_direct_pay_by_user',
            ),
        ),
        'name_id' => 'payment_type',
        'input_type' => 'select',
        'selected' => 'create_direct_pay_by_user',
    ),
    'alipay_pid' => array(
        'title' => '合作者身份 Pid',
        'content' => '',
        'name_id' => 'alipay_pid',
        'input_type' => 'text',
        'class' => 'span6',
    ),
    'alipay_key' => array(
        'title' => '安全校验码 Key',
        'content' => '',
        'name_id' => 'alipay_key',
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
    'editaction' => 'alipay',
    'orders_state' => '10',
);
