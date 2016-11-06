INSERT INTO dbshop_admin_group (admin_group_id, admin_group_purview) VALUES (1, 'a:1:{s:10:"purviewAll";s:1:"1";}');
INSERT INTO dbshop_admin_group_extend (admin_group_id, admin_group_name, `language`) VALUES (1, '管理员', 'zh_CN');

INSERT INTO dbshop_user_group (group_id) VALUES (1);
INSERT INTO dbshop_user_group_extend (group_id, group_name, `language`) VALUES (1, '普通会员', 'zh_CN');

INSERT INTO dbshop_currency (currency_id, currency_name, currency_code, currency_symbol, currency_decimal, currency_unit, currency_rate, currency_type, currency_state) VALUES
(1, 'CNY', 'CNY', '￥', 2, '元', '1.00000000', 1, 1);

INSERT INTO dbshop_express (express_id, express_name, express_info, express_url, express_sort, express_set, express_price, express_state) VALUES
(7, '顺丰速运', '国内服务质量最好，速度最快的速运公司。', '', 255, 'T', '0', 1);

INSERT INTO dbshop_stock_state (stock_state_id, state_sort, stock_type_state, state_type) VALUES
(1, 23, 1, 1),
(2, 255, 2, 1);
INSERT INTO dbshop_stock_state_extend (stock_state_id, stock_state_name, `language`) VALUES
(1, '有货', 'zh_CN'),
(2, '缺货', 'zh_CN');

INSERT INTO dbshop_goods_tag (tag_id, tag_type, tag_group_id, template_tag) VALUES
(1, 'index_new', 0, 'default'),
(2, 'index_spec', 0, 'default'),
(3, 'index_hot', 0, 'default'),
(4, 'index_recom', 0, 'default');
INSERT INTO dbshop_goods_tag_extend (tag_id, tag_name, `language`) VALUES
(1, '最新商品', 'zh_CN'),
(2, '特价商品', 'zh_CN'),
(3, '热门商品', 'zh_CN'),
(4, '推荐商品', 'zh_CN');

INSERT INTO dbshop_ad (`ad_id`, `ad_class`, `ad_name`, `ad_place`, `ad_type`, `ad_width`, `ad_height`, `ad_start_time`, `ad_end_time`, `ad_url`, `ad_body`, `ad_state`, `template_ad`) VALUES
(31, 'index', '推荐商品下方', 'common_down', 'image', 985, 90, NULL, NULL, NULL, '/public/upload/ad/31/1.png', 1, 'default'),
(32, 'index', '最新商品下面', 'new_down', 'image', 985, 90, NULL, NULL, NULL, '/public/upload/ad/32/1.png', 1, 'default'),
(33, 'index', '首页幻灯片', 'class_right', 'slide', 840, 280, NULL, NULL, NULL, NULL, 1, 'default'),
(34, 'goodsclass', '左侧菜单广告', 'goodslist_leftmenu_down', 'image', 276, 100, NULL, NULL, NULL, '/public/upload/ad/34/3.png', 1, 'default'),
(35, 'goodsclass', '商品列表横幅', 'goodslist_banner', 'image', 985, 90, NULL, NULL, NULL, '/public/upload/ad/35/1.png', 1, 'default'),
(37, 'goods', '品质保证下面广告', 'goods_info_right', 'image', 360, NULL, NULL, NULL, NULL, '/public/upload/ad/37/4.png', 1, 'default');
INSERT INTO dbshop_ad_slide (`ad_id`, `ad_slide_info`, `ad_slide_image`, `ad_slide_sort`, `ad_slide_url`) VALUES
(33, '广告图片5', '/public/upload/ad/33/5.png', 5, NULL),
(33, '广告图片4', '/public/upload/ad/33/4.png', 4, NULL),
(33, '广告图片3', '/public/upload/ad/33/3.png', 3, NULL),
(33, '广告图片2', '/public/upload/ad/33/2.png', 2, NULL),
(33, '广告图片1', '/public/upload/ad/33/1.png', 1, NULL);

INSERT INTO `dbshop_user_integral_type` (`integral_type_id`, `default_integral_num`, `integral_type_mark`, `integral_currency_con`) VALUES
(1, 0, 'integral_type_1', 1),
(2, 0, 'integral_type_2', 0);
INSERT INTO `dbshop_user_integral_type_extend` (`integral_type_id`, `integral_type_name`, `language`) VALUES
(1, '消费积分', 'zh_CN'),
(2, '等级积分', 'zh_CN');