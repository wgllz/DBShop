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
    /** 
     * System系统管理
     * index        系统设置
     * uploadset    附件设置
     * userset      客户设置
     * sendMessageSet消息提醒设置
     * Online在线客服管理
     * index        客服查看
     * add          客服添加
     * edit         客服编辑
     * del          客服删除
     * group        客服组查看
     * groupadd     客服组添加
     * groupedit    客服组编辑
     * groupdel     客服组删除
     * Optimization性能优化
     * index优化设置
     */
    'System\Controller'    => array(
        'System'=>array('index', 'uploadset', 'userset', 'sendMessageSet'),
        'Online'=>array('index', 'add', 'edit', 'del', 'group', 'groupadd', 'groupedit', 'groupdel'),
        'Optimization'=>array('index')
    ),
    /**
     * Admin管理员管理
     * administrator管理员查看
     * adminadd     管理员添加
     * adminedit    管理员编辑
     * admindel     管理员删除
     * admingroup   管理组查看
     * groupadd     管理组添加
     * editgroup    管理组编辑
     * groupdel     管理组删除
     */
    'Admin\Controller'     => array(
        'Admin'=>array('administrator', 'adminadd', 'adminedit', 'admindel', 'admingroup', 'groupadd', 'editgroup', 'groupdel')
    ),
    /**
     * Currency货币设置
     * index        货币查看
     * add          货币添加
     * edit         货币编辑
     * del          货币删除
     */
    'Currency\Controller'  => array(
        'Currency'=>array('index', 'add', 'edit', 'del')
    ),
    /**
     * Payment支付设置
     * index        支付查看
     * payment      支付编辑
     */
    'Payment\Controller'   => array(
        'Payment'=>array('index', 'payment')
    ),
    /** 
     * Express配送设置
     * index        配送查看
     * add          配送添加
     * edit         配送编辑
     * del          配送删除
     */
    'Express\Controller'   => array(
        'Express'=>array('index', 'add', 'edit', 'del', 'expressapi', 'apiedit')
    ),
    /** 
     * Region地区管理
     * index        地区查看
     * add          地区添加
     * edit         地区编辑
     * del          地区删除
     */
    'Region\Controller'    => array(
        'Region'=>array('index', 'add', 'edit', 'del')
    ),
    /** 
     * State状态管理
     * index        库存状态查看
     * add          库存状态添加
     * edit         库存状态编辑
     * del          库存状态删除
     */
    'Stock\Controller'     => array(
        'State'=>array('index', 'add', 'edit', 'del')
    ),
    /** 
     * Template模板设置
     * index        模板设置
     */
    'Template\Controller'  => array(
        'Template'=>array('index')
    ),
    /**
    * Package系统更新管理
    * index        更新列表查看
    * onlineupdate 更新操作处理
    */
    'Package\Controller'  => array(
        'Package'=>array('index', 'onlineupdate')
     ),
    /** 
     * Navigation导航设置
     * index        导航查看
     * add          导航添加
     * edit         导航编辑
     * del          导航删除
     */
    'Navigation\Controller'=> array(
        'Navigation'=>array('index', 'add', 'edit', 'del')
    ),
    /**
     * Links友情链接设置
     * index        友情链接查看
     * add          友情链接添加
     * edit         友情链接编辑
     * del          友情链接删除
     */
    'Links\Controller'     => array(
        'Links'=>array('index', 'add', 'edit', 'del')
    ),
    /**
     * Email邮件发送
     * index        邮件发送查看
     * emailsend    邮件发送处理
     */
    'Email\Controller'     => array(
        'Email'=>array('index', 'emailsend')
    ),
    /**
     * Ad广告管理
     * index        广告管理查看
     * setad        广告查看
     * add          广告添加
     * edit         广告编辑
     * delad        广告删除
     * mobileIndex  手机广告管理查看
     * mobileSetad  手机广告查看
     * mobileAdd    手机广告添加
     * mobileEdit   手机广告编辑
     * MobileDelad  手机广告删除
     */
    'Ad\Controller'        => array(
        'Ad'=>array('index', 'setad', 'add', 'edit', 'delad', 'mobileIndex', 'mobileSetad', 'mobileAdd', 'mobileEdit', 'MobileDelad')
    ),
    /**
     * Dbsql数据库备份还原
     * index        数据库备份查看
     * delbackup    数据库备份删除
     * backup       数据库备份操作
     * importbackup 数据库导入操作
     */
    'Dbsql\Controller'     => array(
        'Dbsql'=>array('index', 'delbackup', 'backup', 'importbackup')
    ),
    /**
     * Operlog操作日志
     * index        操作日志查看
     * del          操作日志删除
     */
    'Operlog\Controller'   => array(
        'Operlog'=>array('index', 'del')
    ),
    /**
     * Cms文章管理
     * index        文章查看
     * add          文章添加
     * edit         文章编辑
     * del          文章删除
     * singleArticle        单页文章列表
     * addSingleArticle     单页文章添加
     * editSingleArticle    单页文章编辑
     * singleDel            单页文章删除
     * Class文章分类管理
     * index        文章分类查看
     * add          文章分类添加
     * edit         文章分类编辑
     * del          文章分类删除
     */
    'Cms\Controller'       => array(
        'Cms'   =>array('index', 'add', 'edit', 'del', 'singleArticle', 'addSingleArticle', 'editSingleArticle', 'singleDel'),
        'Class' =>array('index', 'add', 'edit', 'del')
    ),
    /**
     * User客户管理
     * index        客户查看
     * add          客户添加
     * edit         客户编辑
     * del          客户删除
     * editall      客户批量处理
     * Usergroup客户组管理
     * index        客户组查看
     * add          客户组添加
     * edit         客户组编辑
     * del          客户组删除
     * Integral积分规则
     * integralRule     查看积分规则
     * addIntegralRule  添加积分规则
     * editIntegralRule 编辑积分规则
     * delIntegralRule  删除积分规则
     */
    'User\Controller'      => array(
        'User'      => array('index', 'add', 'edit', 'del', 'editall'),
        'Usergroup' => array('index', 'add', 'edit', 'del'),
        'Integral'  => array('integralRule', 'addIntegralRule', 'editIntegralRule', 'delIntegralRule'),
        'Usermoney' => array('index', 'addUserMoney', 'paycheck', 'paycheckDel', 'withdraw', 'withdrawUdate', 'withdrawDel')
    ),
    /**
     * Goods商品管理
     * index        商品查看
     * add          商品添加
     * edit         商品编辑
     * editall      商品批量处理
     * Class商品分类管理
     * index        分类查看
     * add          分类添加
     * edit         分类编辑
     * del          分类删除
     * Brand商品品牌管理
     * index        品牌查看
     * add          品牌添加
     * edit         品牌编辑
     * del          品牌删除
     * Attribute商品属性管理
     * index        属性查看
     * add          属性添加
     * edit         属性编辑
     * del          属性删除
     * attributeValue       属性值查看
     * addAttributeValue    属性值添加
     * editAttributeValue   属性值编辑
     * delAttributeValue    属性值删除
     * attributeGroup       属性组查看
     * addAttributeGroup    属性组添加
     * editAttributeGroup   属性组编辑
     * delAttributeGroup    属性组删除
     * Comment商品评价管理
     * index        商品评价查看
     * edit         商品评价编辑
     * del          商品评价删除
     * Tag商品标签管理
     * index        标签查看
     * add          标签添加
     * edit         标签编辑
     * del          标签删除
     * tagGroup     标签组查看
     * addTagGroup  标签组添加
     * editTagGroup 标签组编辑
     * delTagGroup  标签组删除
     * Ask商品咨询管理
     * index        咨询查看
     * del          咨询删除
     * replycontent     回复咨询
     * changeShowState  改变显示状态
     * Promotions商品优惠规则
     * index        规则查看
     * add          规则添加
     * edit         规则编辑
     * del          规则删除
     */
    'Goods\Controller'     => array(
        'Goods'     => array('index', 'add', 'edit', 'del', 'editall'),
        'Class'     => array('index', 'add', 'edit', 'del'),
        'Brand'     => array('index', 'add', 'edit', 'del'),
        'Attribute' => array('index', 'add', 'edit', 'del', 'attributeValue', 'addAttributeValue', 'editAttributeValue', 'delAttributeValue', 'attributeGroup', 'addAttributeGroup', 'editAttributeGroup', 'delAttributeGroup'),
        'Comment'   => array('index', 'edit', 'del'),
        'Tag'       => array('index', 'add', 'edit', 'del', 'tagGroup', 'addTagGroup', 'editTagGroup', 'delTagGroup'),
    	'Ask'		=> array('index', 'del', 'replycontent', 'changeShowState'),
    	'Promotions'=> array('index', 'add', 'edit', 'del'),
    ),
    /**
     * Orders订单管理
     * index        订单查看
     * edit         订单编辑
     * orderprint   订单打印
     * payoper      订单支付
     * shipoper     订单配送
     * finishoper   订单完成
     */
    'Orders\Controller'    => array(
        'Orders'    =>array('index', 'edit', 'orderprint', 'payoper', 'shipoper', 'finishoper',
            'refund', 'operRefund', 'showRefund',
            'addExpressNumber', 'importExpressNumber', 'expressNumberList', 'exportShip', 'moreshipoper')
    )
);