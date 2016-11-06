DROP TABLE IF EXISTS dbshop_ad;
CREATE TABLE dbshop_ad (
  ad_id int(11) NOT NULL AUTO_INCREMENT,
  ad_class char(20) NOT NULL,
  ad_name char(255) NOT NULL,
  ad_place char(50) NOT NULL,
  ad_type char(20) NOT NULL,
  ad_width int(11) DEFAULT NULL,
  ad_height int(11) DEFAULT NULL,
  ad_start_time char(10) DEFAULT NULL,
  ad_end_time char(10) DEFAULT NULL,
  ad_url varchar(500) DEFAULT NULL,
  ad_body varchar(5000) DEFAULT NULL,
  ad_state tinyint(1) NOT NULL DEFAULT '2',
  template_ad char(30) NOT NULL DEFAULT 'default',
  show_type char(10) NOT NULL DEFAULT 'pc',
  PRIMARY KEY (ad_id),
  KEY ad_place (ad_place,ad_type,ad_state),
  KEY ad_class (ad_class),
  KEY template_ad (template_ad),
  KEY show_type (show_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_admin;
CREATE TABLE dbshop_admin (
  admin_id int(11) NOT NULL AUTO_INCREMENT,
  admin_group_id int(11) NOT NULL,
  admin_name char(100) NOT NULL,
  admin_passwd char(32) NOT NULL,
  admin_email char(100) NOT NULL,
  admin_add_time char(10) NOT NULL,
  admin_old_login_time char(10) DEFAULT NULL,
  admin_new_login_time char(10) DEFAULT NULL,
  PRIMARY KEY (admin_id),
  KEY admin_name (admin_name,admin_email),
  KEY admin_group_id (admin_group_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_admin_group;
CREATE TABLE dbshop_admin_group (
  admin_group_id int(11) NOT NULL AUTO_INCREMENT,
  admin_group_purview text,
  PRIMARY KEY (admin_group_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_admin_group_extend;
CREATE TABLE dbshop_admin_group_extend (
  admin_group_id int(11) NOT NULL,
  admin_group_name char(200) NOT NULL,
  `language` char(10) NOT NULL,
  KEY admin_group_id (admin_group_id,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_ad_slide;
CREATE TABLE dbshop_ad_slide (
  ad_id int(11) NOT NULL,
  ad_slide_info varchar(1000) DEFAULT NULL,
  ad_slide_image char(200) DEFAULT NULL,
  ad_slide_sort int(3) DEFAULT '255',
  ad_slide_url varchar(500) DEFAULT NULL,
  KEY ad_id (ad_id,ad_slide_sort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_article;
CREATE TABLE dbshop_article (
  article_id int(11) NOT NULL AUTO_INCREMENT,
  article_class_id int(11) NOT NULL,
  article_writer char(200) DEFAULT NULL,
  article_url char(200) DEFAULT NULL,
  article_add_time char(10) NOT NULL,
  article_state tinyint(1) NOT NULL DEFAULT '0',
  article_sort int(11) NOT NULL DEFAULT '255',
  PRIMARY KEY (article_id),
  KEY article_class_id (article_class_id,article_add_time),
  KEY article_state (article_state),
  KEY article_sort (article_sort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_article_class;
CREATE TABLE dbshop_article_class (
  article_class_id int(11) NOT NULL AUTO_INCREMENT,
  article_class_top_id int(11) NOT NULL DEFAULT '0',
  article_class_path char(255) NOT NULL DEFAULT '0',
  article_class_sort int(11) NOT NULL,
  article_class_state tinyint(1) NOT NULL DEFAULT '0',
  index_news tinyint(1) NOT NULL DEFAULT '2',
  PRIMARY KEY (article_class_id),
  KEY article_class_top_id (article_class_top_id,article_class_sort,article_class_state),
  KEY article_class_path (article_class_path),
  KEY index_news (index_news)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_article_class_extend;
CREATE TABLE dbshop_article_class_extend (
  article_class_id int(11) NOT NULL,
  article_class_name char(200) NOT NULL,
  article_class_info varchar(1000) DEFAULT NULL,
  article_class_extend_name char(100) DEFAULT NULL,
  article_class_keywords char(255) DEFAULT NULL,
  article_class_description varchar(1000) DEFAULT NULL,
  `language` char(10) NOT NULL,
  KEY article_class_id (article_class_id,article_class_name,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_article_extend;
CREATE TABLE dbshop_article_extend (
  article_id int(11) NOT NULL,
  article_title char(200) NOT NULL,
  article_body text,
  article_title_extend char(100) DEFAULT NULL,
  article_keywords char(255) DEFAULT NULL,
  article_description varchar(1000) DEFAULT NULL,
  `language` char(10) NOT NULL,
  KEY article_id (article_id,article_title,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_single_article;
CREATE TABLE `dbshop_single_article` (
  `single_article_id` int(11) NOT NULL AUTO_INCREMENT,
  `single_article_writer` char(200) DEFAULT NULL,
  `single_article_add_time` char(10) NOT NULL,
  `article_tag` char(20) DEFAULT '',
  `template_tag` char(30) NOT NULL,
  PRIMARY KEY (`single_article_id`),
  KEY `template_tag` (`template_tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_single_article_extend;
CREATE TABLE  `dbshop_single_article_extend` (
`single_article_id` INT NOT NULL,
`single_article_title` CHAR(200) NOT NULL,
`single_article_body` TEXT NULL,
`single_article_title_extend` CHAR(100) NULL,
`single_article_keywords` CHAR(255) NULL,
`single_article_description` VARCHAR(1000) NULL,
`language` CHAR(10) NOT NULL,
INDEX (`single_article_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_currency;
CREATE TABLE dbshop_currency (
  currency_id int(11) NOT NULL AUTO_INCREMENT,
  currency_name char(20) NOT NULL,
  currency_code char(5) NOT NULL,
  currency_symbol char(5) DEFAULT NULL,
  currency_decimal tinyint(1) NOT NULL DEFAULT '2',
  currency_unit char(20) DEFAULT NULL,
  currency_rate char(10) NOT NULL,
  currency_type tinyint(1) NOT NULL DEFAULT '0',
  currency_state tinyint(1) NOT NULL,
  PRIMARY KEY (currency_id),
  KEY currency_name (currency_name,currency_code,currency_decimal,currency_type,currency_state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_express;
CREATE TABLE dbshop_express (
  express_id int(11) NOT NULL AUTO_INCREMENT,
  express_name char(200) NOT NULL,
  express_name_code char(30) DEFAULT NULL,
  express_info varchar(1000) DEFAULT NULL,
  express_url char(100) DEFAULT NULL,
  express_sort int(11) NOT NULL,
  express_set char(1) NOT NULL,
  express_price char(100) DEFAULT NULL,
  express_state tinyint(1) NOT NULL DEFAULT '1',
  cash_on_delivery tinyint(1) NOT NULL DEFAULT '2',
  PRIMARY KEY (express_id),
  KEY express_sort (express_sort),
  KEY express_set (express_set),
  KEY express_state (express_state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_express_individuation;
CREATE TABLE dbshop_express_individuation (
  indiv_id int(11) NOT NULL AUTO_INCREMENT,
  express_id int(11) DEFAULT '0',
  express_price char(100) NOT NULL DEFAULT '0',
  express_area varchar(5000) NOT NULL,
  PRIMARY KEY (indiv_id),
  KEY express_id (express_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_express_number;
CREATE TABLE dbshop_express_number (
  `express_number_id` int(11) NOT NULL AUTO_INCREMENT,
  `express_number` char(100) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `order_sn` char(50) DEFAULT NULL,
  `express_number_state` tinyint(1) NOT NULL DEFAULT '0',
  `express_number_use_time` char(10) DEFAULT NULL,
  `express_id` int(11) NOT NULL,
  PRIMARY KEY (`express_number_id`),
  KEY `express_number` (`express_number`,`order_id`,`express_number_state`,`express_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_getpackageuser;
CREATE TABLE dbshop_getpackageuser (
  user_id int(11) NOT NULL AUTO_INCREMENT,
  user_name char(100) NOT NULL,
  user_passwd char(32) NOT NULL,
  web_site char(200) NOT NULL,
  user_state tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods;
CREATE TABLE dbshop_goods (
  `goods_id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_item` char(50) NOT NULL,
  `brand_id` int(11) DEFAULT '0',
  `attribute_group_id` int(11) NOT NULL DEFAULT '0',
  `goods_state` tinyint(1) NOT NULL,
  `goods_weight` char(20) NOT NULL DEFAULT '0',
  `goods_type` tinyint(1) NOT NULL DEFAULT '1',
  `virtual_goods_add_state` tinyint(1) NOT NULL DEFAULT '0',
  `goods_start_time` char(10) DEFAULT NULL,
  `goods_end_time` char(10) DEFAULT NULL,
  `goods_price` char(20) DEFAULT '0',
  `goods_shop_price` char(20) NOT NULL DEFAULT '0',
  `goods_preferential_price` char(20) DEFAULT '0',
  `goods_preferential_start_time` char(10) DEFAULT NULL,
  `goods_preferential_end_time` char(10) DEFAULT NULL,
  `goods_integral_num` int(11) NOT NULL DEFAULT '0',
  `goods_stock` int(11) NOT NULL DEFAULT '0',
  `goods_out_of_stock_set` int(11) NOT NULL DEFAULT '0',
  `goods_cart_buy_max_num` int(11) NOT NULL DEFAULT '0',
  `goods_cart_buy_min_num` int(11) NOT NULL DEFAULT '0',
  `goods_stock_state` int(11) NOT NULL,
  `goods_stock_state_open` tinyint(1) NOT NULL DEFAULT '0',
  `goods_out_stock_state` int(11) NOT NULL,
  `goods_add_time` char(10) NOT NULL,
  `goods_sort` int(11) NOT NULL,
  `goods_tag_str` varchar(1000) DEFAULT NULL,
  `goods_click` int(11) NOT NULL DEFAULT '0',
  `goods_class_have_true` tinyint(1) NOT NULL DEFAULT '1',
  `virtual_sales` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`goods_id`),
  KEY `goods_item` (`goods_item`,`goods_shop_price`,`goods_stock`,`goods_stock_state`,`goods_add_time`,`goods_sort`),
  KEY `brand_id` (`brand_id`),
  KEY `attribute_group_id` (`attribute_group_id`),
  KEY `goods_out_stock_state` (`goods_out_stock_state`),
  KEY `goods_stock_state_set` (`goods_stock_state_open`),
  KEY `goods_click` (`goods_click`),
  KEY `goods_class_have_true` (`goods_class_have_true`),
  KEY `goods_type` (`goods_type`),
  KEY `virtual_goods_add_state` (`virtual_goods_add_state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_related;
CREATE TABLE `dbshop_goods_related` (
  `related_id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL,
  `related_goods_id` int(11) NOT NULL,
  `related_sort` int(3) NOT NULL DEFAULT '255',
  PRIMARY KEY (`related_id`),
  KEY `goods_id` (`goods_id`,`related_goods_id`,`related_sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_combination;
CREATE TABLE dbshop_goods_combination (
  `combination_id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL,
  `combination_goods_id` int(11) NOT NULL,
  `combination_sort` int(3) NOT NULL,
  PRIMARY KEY (`combination_id`),
  KEY `goods_id` (`goods_id`,`combination_goods_id`,`combination_sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_attribute;
CREATE TABLE dbshop_goods_attribute (
  attribute_id int(11) NOT NULL AUTO_INCREMENT,
  attribute_type char(10) NOT NULL,
  attribute_group_id int(11) NOT NULL,
  attribute_sort int(11) NOT NULL,
  PRIMARY KEY (attribute_id),
  KEY attribute_type (attribute_type,attribute_group_id,attribute_sort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_attribute_extend;
CREATE TABLE dbshop_goods_attribute_extend (
  attribute_id int(11) NOT NULL,
  attribute_name char(100) NOT NULL,
  `language` char(10) NOT NULL,
  KEY attribute_id (attribute_id,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_attribute_group;
CREATE TABLE dbshop_goods_attribute_group (
  attribute_group_id int(11) NOT NULL AUTO_INCREMENT,
  attribute_group_sort int(11) NOT NULL,
  PRIMARY KEY (attribute_group_id),
  KEY attribute_group_sort (attribute_group_sort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_attribute_group_extend;
CREATE TABLE dbshop_goods_attribute_group_extend (
  attribute_group_id int(11) NOT NULL,
  attribute_group_name char(100) NOT NULL,
  `language` char(10) NOT NULL,
  KEY attribute_group_id (attribute_group_id,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_attribute_value;
CREATE TABLE dbshop_goods_attribute_value (
  value_id int(11) NOT NULL AUTO_INCREMENT,
  attribute_id int(11) NOT NULL,
  attribute_group_id int(11) NOT NULL,
  value_sort int(3) NOT NULL DEFAULT '255',
  PRIMARY KEY (value_id),
  KEY attribute_id (attribute_id,value_sort),
  KEY attribute_group_id (attribute_group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_attribute_value_extend;
CREATE TABLE dbshop_goods_attribute_value_extend (
  value_id int(11) NOT NULL,
  attribute_id int(11) NOT NULL,
  value_name char(100) NOT NULL,
  `language` char(10) NOT NULL,
  KEY value_id (value_id,attribute_id,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_brand;
CREATE TABLE dbshop_goods_brand (
  brand_id int(11) NOT NULL AUTO_INCREMENT,
  brand_sort int(3) NOT NULL DEFAULT '255',
  PRIMARY KEY (brand_id),
  KEY brand_sort (brand_sort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_brand_extend;
CREATE TABLE dbshop_goods_brand_extend (
  brand_id int(11) NOT NULL,
  brand_name char(150) NOT NULL,
  brand_logo char(100) DEFAULT NULL,
  brand_info varchar(1500) DEFAULT NULL,
  brand_title_extend char(100) DEFAULT NULL,
  brand_keywords char(255) DEFAULT NULL,
  brand_description varchar(1000) DEFAULT NULL,
  `language` char(10) NOT NULL,
  KEY `language` (`language`),
  KEY brand_id (brand_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_class;
CREATE TABLE dbshop_goods_class (
  class_id int(11) NOT NULL AUTO_INCREMENT,
  class_top_id int(11) NOT NULL DEFAULT '0',
  class_name char(150) NOT NULL,
  class_path char(255) DEFAULT '0',
  class_state tinyint(1) NOT NULL,
  class_info varchar(4000) DEFAULT NULL,
  class_sort int(11) NOT NULL DEFAULT '255',
  class_title_extend char(100) DEFAULT NULL,
  class_keywords char(255) DEFAULT NULL,
  class_description varchar(1000) DEFAULT NULL,
  class_icon char(200) DEFAULT NULL,
  class_image char(100) DEFAULT NULL,
  navigation_show tinyint(1) DEFAULT NULL,
  PRIMARY KEY (class_id),
  KEY class_top_id (class_top_id,class_name),
  KEY class_state (class_state),
  KEY class_sort (class_sort),
  KEY navigation_show (navigation_show)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_class_show;
CREATE TABLE dbshop_goods_class_show (
  class_id int(11) NOT NULL,
  show_body varchar(5000) NOT NULL,
  KEY class_id (class_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_comment;
CREATE TABLE dbshop_goods_comment (
  comment_id int(11) NOT NULL AUTO_INCREMENT,
  comment_writer char(100) NOT NULL,
  comment_body varchar(2000) NOT NULL,
  goods_evaluation tinyint(1) NOT NULL DEFAULT '5',
  comment_time char(10) NOT NULL,
  comment_show_state tinyint(1) NOT NULL DEFAULT '1',
  reply_body varchar(2000) DEFAULT NULL,
  reply_time char(10) DEFAULT NULL,
  reply_writer char(100) DEFAULT NULL,
  goods_id int(11) NOT NULL,
  order_goods_id int(11) NOT NULL,
  PRIMARY KEY (comment_id),
  KEY goods_evaluation (goods_evaluation,comment_time,comment_show_state,goods_id),
  KEY reply_time (reply_time,reply_writer),
  KEY order_goods_id (order_goods_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_comment_base;
CREATE TABLE dbshop_goods_comment_base (
  goods_id int(11) NOT NULL,
  comment_last_writer char(100) NOT NULL,
  comment_last_time char(10) NOT NULL,
  comment_count int(11) NOT NULL,
  PRIMARY KEY (goods_id),
  KEY comment_last_writer (comment_last_writer,comment_last_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_ask;
CREATE TABLE dbshop_goods_ask (
  ask_id int(11) NOT NULL AUTO_INCREMENT,
  ask_writer char(100) NOT NULL,
  ask_content varchar(2000) NOT NULL,
  ask_time char(10) NOT NULL,
  ask_show_state tinyint(1) NOT NULL DEFAULT '1',
  reply_ask_content varchar(2000) DEFAULT NULL,
  reply_ask_time char(10) DEFAULT NULL,
  reply_ask_writer char(100) DEFAULT NULL,
  goods_id int(11) NOT NULL,
  PRIMARY KEY (ask_id),
  KEY ask_show_state (ask_show_state,goods_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_ask_base;
CREATE TABLE  dbshop_goods_ask_base (
goods_id INT NOT NULL,
ask_last_writer CHAR( 100 ) NOT NULL,
ask_last_time CHAR( 10 ) NOT NULL,
ask_count INT NOT NULL,
INDEX (goods_id)
) ENGINE = INNODB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_custom;
CREATE TABLE dbshop_goods_custom (
  custom_title char(50) NOT NULL,
  custom_content char(255) DEFAULT NULL,
  custom_key int(2) NOT NULL,
  goods_id int(11) NOT NULL,
  KEY goods_id (goods_id),
  KEY custom_key (custom_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_extend;
CREATE TABLE dbshop_goods_extend (
  goods_id int(11) NOT NULL,
  goods_name char(255) NOT NULL,
  goods_info varchar(2000) DEFAULT NULL,
  goods_body text,
  goods_image_id int(11) DEFAULT NULL,
  goods_extend_name char(255) DEFAULT NULL,
  goods_keywords char(255) DEFAULT NULL,
  goods_description varchar(1000) DEFAULT NULL,
  `language` char(20) NOT NULL,
  KEY goods_id (goods_id,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_image;
CREATE TABLE dbshop_goods_image (
  goods_image_id int(11) NOT NULL AUTO_INCREMENT,
  goods_id int(11) DEFAULT '0',
  goods_title_image varchar(150) DEFAULT NULL,
  goods_thumbnail_image varchar(150) DEFAULT NULL,
  goods_watermark_image varchar(150) DEFAULT NULL,
  goods_source_image varchar(150) DEFAULT NULL,
  image_sort int(3) DEFAULT '255',
  image_slide tinyint(1) NOT NULL DEFAULT '1',
  editor_session_str char(32) DEFAULT NULL ,
  `language` char(20) NOT NULL,
  PRIMARY KEY (goods_image_id),
  KEY goods_id (goods_id,`language`),
  KEY image_sort (image_sort),
  KEY image_slide (image_slide),
  KEY editor_session_str (editor_session_str)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_in_attribute;
CREATE TABLE dbshop_goods_in_attribute (
  goods_id int(11) NOT NULL,
  attribute_id int(11) NOT NULL,
  attribute_body varchar(3000) DEFAULT NULL,
  KEY goods_id (goods_id,attribute_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_in_class;
CREATE TABLE dbshop_goods_in_class (
  goods_id int(11) NOT NULL,
  class_id int(11) NOT NULL,
  class_state tinyint(1) NOT NULL DEFAULT '1',
  class_goods_sort int(11) DEFAULT '255',
  class_recommend tinyint(1) NOT NULL DEFAULT '0',
  KEY goods_id (goods_id,class_id),
  KEY goods_sort (class_goods_sort),
  KEY class_state (class_state),
  KEY class_recommend (class_recommend)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_price_extend;
CREATE TABLE dbshop_goods_price_extend (
  extend_id int(11) NOT NULL AUTO_INCREMENT,
  extend_name char(120) DEFAULT NULL,
  goods_id int(11) NOT NULL,
  extend_type char(10) NOT NULL,
  extend_show_type tinyint(1) NOT NULL DEFAULT '1',
  `language` char(10) NOT NULL,
  PRIMARY KEY (extend_id),
  KEY goods_id (goods_id,`language`),
  KEY extend_type (extend_type),
  KEY extend_show_type (extend_show_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_price_extend_color;
CREATE TABLE dbshop_goods_price_extend_color (
  color_value char(20) NOT NULL,
  color_info char(100) DEFAULT NULL,
  color_image char(100) DEFAULT NULL,
  extend_id int(11) NOT NULL,
  goods_id int(11) NOT NULL,
  KEY color_value (color_value,extend_id,goods_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_price_extend_goods;
CREATE TABLE dbshop_goods_price_extend_goods (
  goods_color char(20) NOT NULL,
  goods_size char(20) NOT NULL,
  goods_extend_price char(20) NOT NULL,
  goods_extend_stock int(11) NOT NULL,
  goods_extend_item char(50) NOT NULL,
  goods_id int(11) NOT NULL,
  KEY goods_color (goods_color,goods_size,goods_extend_price,goods_extend_stock,goods_extend_item,goods_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_price_extend_size;
CREATE TABLE dbshop_goods_price_extend_size (
  size_value char(20) NOT NULL,
  size_info char(100) DEFAULT NULL,
  extend_id int(11) NOT NULL,
  goods_id int(11) NOT NULL,
  KEY size_value (size_value,extend_id,goods_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_tag;
CREATE TABLE dbshop_goods_tag (
  tag_id int(11) NOT NULL AUTO_INCREMENT,
  tag_type char(20) DEFAULT NULL,
  tag_group_id int(11) NOT NULL DEFAULT '0',
  tag_sort int(11) NOT NULL DEFAULT '255',
  template_tag char(30) DEFAULT NULL ,
  PRIMARY KEY (tag_id),
  KEY tag_type (tag_type),
  KEY tag_group_id (tag_group_id),
  KEY template_tag (template_tag),
  KEY tag_sort (tag_sort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_tag_extend;
CREATE TABLE dbshop_goods_tag_extend (
  tag_id int(11) NOT NULL,
  tag_name char(200) NOT NULL,
  `language` char(10) NOT NULL,
  KEY tag_id (tag_id,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_tag_group;
CREATE TABLE dbshop_goods_tag_group (
  tag_group_id int(11) NOT NULL AUTO_INCREMENT,
  tag_group_sort int(3) NOT NULL,
  PRIMARY KEY (tag_group_id),
  KEY tag_group_sort (tag_group_sort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_tag_group_extend;
CREATE TABLE dbshop_goods_tag_group_extend (
  tag_group_id int(11) NOT NULL,
  tag_group_name char(100) NOT NULL,
  `tag_group_mark` char(50) DEFAULT NULL,
  `language` char(10) NOT NULL,
  KEY tag_group_id (tag_group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_goods_tag_in_goods;
CREATE TABLE dbshop_goods_tag_in_goods (
  tag_id int(11) NOT NULL,
  goods_id int(11) NOT NULL,
  tag_goods_sort int(11) NOT NULL DEFAULT '255',
  KEY tag_id (tag_id,goods_id),
  KEY tag_goods_sort (tag_goods_sort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_links;
CREATE TABLE dbshop_links (
  links_id int(11) NOT NULL AUTO_INCREMENT,
  links_webname char(100) NOT NULL,
  links_url char(255) DEFAULT NULL,
  links_logo char(100) DEFAULT NULL,
  links_sort int(11) NOT NULL DEFAULT '255',
  links_state tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (links_id),
  KEY links_sort (links_sort,links_state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_navigation;
CREATE TABLE dbshop_navigation (
  navigation_id int(11) NOT NULL AUTO_INCREMENT,
  navigation_url varchar(500) DEFAULT NULL,
  navigation_type tinyint(1) NOT NULL,
  goods_class_id int(11) NOT NULL DEFAULT '0',
  navigation_new_window tinyint(1) DEFAULT '0',
  navigation_sort int(11) NOT NULL DEFAULT '255',
  PRIMARY KEY (navigation_id),
  KEY navigation_type (navigation_type,navigation_new_window,navigation_sort),
  KEY goods_class_id (goods_class_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_navigation_extend;
CREATE TABLE dbshop_navigation_extend (
  navigation_id int(11) NOT NULL,
  navigation_title char(200) NOT NULL,
  `language` char(10) NOT NULL,
  KEY navigation_id (navigation_id,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_online;
CREATE TABLE dbshop_online (
  online_id int(11) NOT NULL AUTO_INCREMENT,
  online_name char(100) NOT NULL,
  online_account char(100) NOT NULL,
  online_type char(10) NOT NULL,
  online_web_code varchar(500) DEFAULT NULL,
  online_state tinyint(1) NOT NULL DEFAULT '0',
  online_group_id int(11) NOT NULL,
  online_sort int(3) NOT NULL DEFAULT '255',
  PRIMARY KEY (online_id),
  KEY online_name (online_name,online_state,online_group_id),
  KEY online_account (online_account),
  KEY online_sort (online_sort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_online_group;
CREATE TABLE dbshop_online_group (
  online_group_id int(11) NOT NULL AUTO_INCREMENT,
  online_group_name char(100) NOT NULL,
  online_group_sort int(11) NOT NULL,
  online_group_state tinyint(1) NOT NULL DEFAULT '0',
  index_show char(4) DEFAULT NULL,
  class_show char(4) DEFAULT NULL,
  goods_show char(4) DEFAULT NULL,
  PRIMARY KEY (online_group_id),
  KEY online_group_sort (online_group_sort,online_group_state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_operlog;
CREATE TABLE dbshop_operlog (
  log_id int(11) NOT NULL AUTO_INCREMENT,
  log_oper_user char(50) NOT NULL,
  log_oper_usergroup char(50) NOT NULL,
  log_time char(10) NOT NULL,
  log_ip char(20) NOT NULL,
  log_content varchar(2000) DEFAULT NULL,
  PRIMARY KEY (log_id),
  KEY log_oper_user (log_oper_user,log_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_order;
CREATE TABLE dbshop_order (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_sn` char(50) NOT NULL,
  `order_out_sn` char(50) DEFAULT NULL,
  `goods_serialize` varchar(5000) DEFAULT NULL,
  `goods_amount` char(10) NOT NULL,
  `order_amount` char(10) NOT NULL,
  `pay_fee` char(10) NOT NULL DEFAULT '0',
  `express_fee` char(10) NOT NULL DEFAULT '0',
  `user_pre_fee` char(10) NOT NULL DEFAULT '0',
  `user_pre_info` char(100) DEFAULT NULL,
  `buy_pre_fee` char(10) NOT NULL DEFAULT '0',
  `integral_buy_num` int(11) NOT NULL DEFAULT '0',
  `integral_buy_price` char(10) NOT NULL DEFAULT '0',
  `goods_weight_amount` char(10) NOT NULL DEFAULT '0',
  `order_state` int(4) NOT NULL,
  `ot_order_state` int(4) DEFAULT NULL,
  `pay_code` char(20) NOT NULL,
  `pay_name` char(20) NOT NULL,
  `pay_time` char(10) DEFAULT NULL,
  `pay_certification` varchar(500) DEFAULT NULL,
  `express_id` int(11) NOT NULL DEFAULT '0',
  `express_name` char(100) DEFAULT NULL,
  `express_time` char(10) DEFAULT NULL,
  `buyer_id` int(11) NOT NULL,
  `buyer_name` char(100) NOT NULL,
  `buyer_email` char(50) NOT NULL,
  `currency` char(10) NOT NULL,
  `currency_symbol` char(15) DEFAULT NULL,
  `currency_unit` char(15) DEFAULT NULL,
  `order_time` char(10) NOT NULL,
  `finish_time` char(10) DEFAULT NULL,
  `order_message` varchar(500) DEFAULT NULL,
  `integral_num` int(11) NOT NULL DEFAULT '0',
  `integral_rule_info` char(100) DEFAULT NULL,
  `integral_type_2_num` int(11) NOT NULL DEFAULT '0',
  `integral_type_2_num_rule_info` char(100) DEFAULT NULL,
  `invoice_content` varchar(500) DEFAULT NULL,
  `refund_state` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`order_id`),
  KEY `order_sn` (`order_sn`,`order_out_sn`,`goods_amount`,`order_amount`,`pay_fee`,`express_fee`,`order_state`,`express_id`),
  KEY `order_time` (`order_time`),
  KEY `buyer_id` (`buyer_id`,`buyer_name`,`buyer_email`),
  KEY `pay_time` (`pay_time`),
  KEY `ot_order_state` (`ot_order_state`),
  KEY `refund_state` (`refund_state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_order_delivery_address;
CREATE TABLE dbshop_order_delivery_address (
  order_id int(11) NOT NULL AUTO_INCREMENT,
  delivery_name char(100) NOT NULL,
  region_id int(11) NOT NULL,
  region_info char(100) NOT NULL,
  region_address char(150) NOT NULL,
  zip_code char(6) DEFAULT NULL,
  tel_phone char(20) DEFAULT NULL,
  mod_phone char(20) DEFAULT NULL,
  express_name char(50) NOT NULL,
  express_fee char(10) DEFAULT '0',
  express_time_info char(255) DEFAULT NULL,
  express_number char(50) DEFAULT NULL,
  express_id int(11) NOT NULL,
  PRIMARY KEY (order_id),
  KEY region_id (region_id,express_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_order_goods;
CREATE TABLE dbshop_order_goods (
  `order_goods_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `goods_item` char(100) DEFAULT NULL,
  `goods_name` char(255) NOT NULL,
  `goods_extend_info` char(255) DEFAULT NULL,
  `goods_color` char(20) DEFAULT NULL,
  `goods_size` char(20) DEFAULT NULL,
  `goods_type` tinyint(1) NOT NULL DEFAULT '1',
  `goods_shop_price` char(10) NOT NULL,
  `buy_num` int(11) NOT NULL,
  `goods_image` char(100) DEFAULT NULL,
  `goods_amount` char(10) NOT NULL,
  `goods_count_weight` int(11) NOT NULL DEFAULT '0',
  `buyer_id` int(11) NOT NULL,
  `comment_state` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`order_goods_id`),
  KEY `order_id` (`order_id`,`goods_id`,`goods_shop_price`),
  KEY `goods_count_weight` (`goods_count_weight`),
  KEY `buyer_id` (`buyer_id`),
  KEY `class_id` (`class_id`),
  KEY `comment_state` (`comment_state`),
  KEY `goods_type` (`goods_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_order_log;
CREATE TABLE dbshop_order_log (
  order_log_id int(11) NOT NULL AUTO_INCREMENT,
  order_id int(11) NOT NULL,
  order_state int(11) NOT NULL,
  state_info char(255) DEFAULT NULL,
  log_time char(10) NOT NULL,
  log_user char(50) NOT NULL,
  PRIMARY KEY (order_log_id),
  KEY order_id (order_id,order_state,log_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_packageservice;
CREATE TABLE dbshop_packageservice (
  package_id int(11) NOT NULL AUTO_INCREMENT,
  package_name char(100) NOT NULL,
  package_info varchar(1000) NOT NULL,
  package_version char(10) NOT NULL,
  package_version_state char(25) DEFAULT NULL,
  package_module char(30) NOT NULL,
  PRIMARY KEY (package_id),
  KEY package_name (package_name,package_version,package_module),
  KEY package_version_state (package_version_state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_region;
CREATE TABLE dbshop_region (
  region_id int(11) NOT NULL AUTO_INCREMENT,
  region_top_id int(11) NOT NULL DEFAULT '0',
  region_sort int(3) NOT NULL DEFAULT '255',
  region_path char(100) DEFAULT NULL,
  PRIMARY KEY (region_id),
  KEY region_top_id (region_top_id,region_sort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_region_extend;
CREATE TABLE dbshop_region_extend (
  region_id int(11) NOT NULL,
  region_name char(255) NOT NULL,
  `language` char(20) NOT NULL,
  KEY region_id (region_id,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_stock_state;
CREATE TABLE dbshop_stock_state (
  stock_state_id int(11) NOT NULL AUTO_INCREMENT,
  state_sort int(11) DEFAULT '255',
  stock_type_state tinyint(1) NOT NULL DEFAULT '1',
  state_type tinyint(1) DEFAULT '0',
  PRIMARY KEY (stock_state_id),
  KEY state_sort (state_sort,state_type),
  KEY stock_type_state (stock_type_state)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_stock_state_extend;
CREATE TABLE dbshop_stock_state_extend (
  stock_state_id int(11) NOT NULL,
  stock_state_name char(100) NOT NULL,
  `language` char(10) NOT NULL,
  KEY stock_state_id (stock_state_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_user;
CREATE TABLE dbshop_user (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `user_name` char(100) NOT NULL,
  `user_avatar` char(100) DEFAULT NULL,
  `user_password` char(32) NOT NULL,
  `user_email` char(50) NOT NULL,
  `user_sex` tinyint(1) NOT NULL DEFAULT '3',
  `user_birthday` char(10) DEFAULT NULL,
  `user_phone` char(30) DEFAULT NULL,
  `user_time` char(10) NOT NULL,
  `user_state` tinyint(1) NOT NULL DEFAULT '1',
  `user_integral_num` int(11) NOT NULL DEFAULT '0',
  `integral_type_2_num` int(11) NOT NULL DEFAULT '0',
  `user_audit_code` char(100) DEFAULT NULL,
  `user_forgot_passwd_code` char(100) DEFAULT NULL,
  `user_money` float(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_name_2` (`user_name`),
  UNIQUE KEY `user_email` (`user_email`),
  KEY `user_name` (`user_name`,`user_email`,`user_sex`),
  KEY `group_id` (`group_id`),
  KEY `user_birthday` (`user_birthday`),
  KEY `user_phone` (`user_phone`),
  KEY `integral_type_2_num` (`integral_type_2_num`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_user_address;
CREATE TABLE dbshop_user_address (
  address_id int(11) NOT NULL AUTO_INCREMENT,
  true_name char(100) NOT NULL,
  region_id int(11) NOT NULL,
  region_value char(255) DEFAULT NULL,
  address char(255) NOT NULL,
  zip_code char(10) DEFAULT NULL,
  mod_phone char(50) NOT NULL,
  tel_area_code char(10) DEFAULT NULL,
  tel_phone char(20) DEFAULT NULL,
  tel_ext char(10) DEFAULT NULL,
  addr_default tinyint(1) NOT NULL DEFAULT '0',
  user_id int(11) NOT NULL,
  PRIMARY KEY (address_id),
  KEY true_name (true_name,region_id,mod_phone,addr_default),
  KEY user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_user_favorites;
CREATE TABLE dbshop_user_favorites (
  favorites_id int(11) NOT NULL AUTO_INCREMENT,
  goods_id int(11) NOT NULL,
  class_id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  `time` char(10) NOT NULL,
  PRIMARY KEY (favorites_id),
  KEY goods_id (goods_id,user_id),
  KEY class_id (class_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_user_group;
CREATE TABLE dbshop_user_group (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `integral_num_start` int(11) NOT NULL DEFAULT '0',
  `integral_num_end` int(11) NOT NULL DEFAULT '0',
  `integral_num_state` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`),
  KEY `integral_num_start` (`integral_num_start`,`integral_num_end`,`integral_num_state`)
  PRIMARY KEY (group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_user_group_extend;
CREATE TABLE dbshop_user_group_extend (
  group_id int(11) NOT NULL,
  group_name char(200) NOT NULL,
  `language` char(10) NOT NULL,
  KEY group_id (group_id,group_name,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_user_mail_log;
CREATE TABLE dbshop_user_mail_log (
  mail_log_id int(11) NOT NULL AUTO_INCREMENT,
  mail_subject varchar(200) NOT NULL,
  mail_body varchar(6000) NOT NULL,
  send_time char(10) NOT NULL,
  user_id int(11) NOT NULL,
  send_state tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (mail_log_id),
  KEY user_id (user_id),
  KEY send_state (send_state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_promotions_rule;
CREATE TABLE dbshop_promotions_rule (
  `promotions_id` int(11) NOT NULL AUTO_INCREMENT,
  `promotions_name` char(150) NOT NULL,
  `promotions_info` varchar(1000) DEFAULT NULL,
  `promotions_start_time` char(10) DEFAULT NULL,
  `promotions_end_time` char(10) DEFAULT NULL,
  `rule_state` tinyint(1) NOT NULL DEFAULT '1',
  `shopping_amount` int(11) DEFAULT '0',
  `shopping_discount` int(11) DEFAULT '0',
  `discount_type` tinyint(1) NOT NULL,
  `promotions_user_type` char(20) NOT NULL,
  `promotions_user_group` text,
  `promotions_goods_type` char(20) NOT NULL,
  `promotions_goods_content` text,
  PRIMARY KEY (`promotions_id`),
  KEY `shopping_amount` (`shopping_amount`,`shopping_discount`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_integral_rule;
CREATE TABLE dbshop_integral_rule (
  `integral_rule_id` int(11) NOT NULL AUTO_INCREMENT,
  `integral_rule_name` char(150) NOT NULL,
  `integral_rule_info` varchar(1000) DEFAULT NULL,
  `integral_rule_start_time` char(10) DEFAULT NULL,
  `integral_rule_end_time` char(10) DEFAULT NULL,
  `integral_rule_state` tinyint(1) NOT NULL DEFAULT '1',
  `shopping_amount` int(11) NOT NULL DEFAULT '0',
  `integral_num` int(11) NOT NULL DEFAULT '0',
  `integral_rule_user_type` char(20) NOT NULL,
  `integral_rule_user_group` text,
  `integral_rule_goods_type` char(20) NOT NULL,
  `integral_rule_goods_content` text,
  `integral_type_id` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`integral_rule_id`),
  KEY `integral_rule_state` (`integral_rule_state`),
  KEY `integral_type_id` (`integral_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_integral_log;
CREATE TABLE dbshop_integral_log (
  `integral_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_name` char(100) NOT NULL,
  `integral_log_info` char(255) DEFAULT NULL,
  `integral_num_log` float NOT NULL,
  `integral_type_id` int(11) NOT NULL DEFAULT '1',
  `integral_log_time` char(10) NOT NULL,
  PRIMARY KEY (`integral_log_id`),
  KEY `user_id` (`user_id`,`user_name`,`integral_log_time`),
  KEY `integral_type_id` (`integral_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_other_login;
CREATE TABLE dbshop_other_login (
  `ol_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `open_id` char(100) NOT NULL,
  `ol_add_time` char(10) NOT NULL,
  `login_type` char(20) NOT NULL,
  PRIMARY KEY (`ol_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_order_amount_log;
CREATE TABLE dbshop_order_amount_log (
  `order_amount_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_edit_amount` char(10) NOT NULL,
  `order_original_amount` char(10) NOT NULL,
  `order_edit_amount_type` char(5) NOT NULL,
  `order_edit_number` int(11) NOT NULL,
  `order_edit_amount_time` char(10) DEFAULT NULL,
  `order_edit_amount_user` char(100) NOT NULL,
  `order_edit_amount_info` varchar(1000) DEFAULT NULL,
  `order_id` int(11) NOT NULL,
  PRIMARY KEY (`order_amount_log_id`),
  KEY `order_edit_amount` (`order_edit_amount`,`order_edit_amount_type`,`order_edit_number`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_user_money_log;
CREATE TABLE dbshop_user_money_log (
  `money_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `money_change_num` float(10,2) NOT NULL,
  `money_changed_amount` float(10,2) NOT NULL,
  `money_change_time` int(10) DEFAULT NULL,
  `money_pay_state` tinyint(4) NOT NULL,
  `money_pay_info` varchar(500) NOT NULL,
  `payment_code` char(10) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` char(50) NOT NULL,
  `admin_id` int(11) NOT NULL DEFAULT '0',
  `admin_name` char(50) DEFAULT NULL,
  `money_pay_type` tinyint(1) NOT NULL,
  PRIMARY KEY (`money_log_id`),
  KEY `money_change_num` (`money_change_num`,`money_change_time`,`money_pay_state`,`payment_code`,`user_id`,`admin_id`),
  KEY `user_name` (`user_name`),
  KEY `money_pay_type` (`money_pay_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_withdraw_log;
CREATE TABLE dbshop_withdraw_log (
  `withdraw_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_name` char(50) NOT NULL,
  `money_change_num` float(10,2) NOT NULL,
  `currency_code` char(10) NOT NULL,
  `bank_name` char(100) NOT NULL,
  `bank_account` char(50) NOT NULL,
  `bank_card_number` char(100) NOT NULL,
  `withdraw_info` char(255) DEFAULT NULL,
  `withdraw_time` int(10) NOT NULL,
  `withdraw_state` tinyint(1) NOT NULL DEFAULT '0',
  `withdraw_finish_time` int(10) DEFAULT NULL,
  `admin_id` int(11) NOT NULL DEFAULT '0',
  `admin_name` char(50) DEFAULT NULL,
  PRIMARY KEY (`withdraw_id`),
  KEY `user_id` (`user_id`,`user_name`,`bank_name`,`bank_account`,`bank_card_number`,`withdraw_time`,`withdraw_state`,`admin_id`,`admin_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_paycheck;
CREATE TABLE dbshop_paycheck (
  `paycheck_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_name` char(50) NOT NULL,
  `money_change_num` float(10,2) NOT NULL,
  `currency_code` char(10) NOT NULL,
  `pay_state` tinyint(2) NOT NULL,
  `paycheck_time` int(10) NOT NULL,
  `paycheck_finish_time` int(11) DEFAULT NULL,
  `pay_code` char(20) DEFAULT NULL,
  `pay_name` char(50) DEFAULT NULL,
  `admin_id` int(11) NOT NULL DEFAULT '0',
  `admin_name` char(50) DEFAULT NULL,
  `paycheck_info` char(255) DEFAULT NULL,
  PRIMARY KEY (`paycheck_id`),
  KEY `user_id` (`user_id`,`user_name`,`currency_code`,`paycheck_time`,`pay_code`),
  KEY `pay_name` (`pay_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_order_refund;
CREATE TABLE dbshop_order_refund (
  `refund_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_sn` char(50) NOT NULL,
  `refund_price` float(10,2) NOT NULL DEFAULT '0.00',
  `goods_id_str` char(100) NOT NULL,
  `refund_type` tinyint(1) NOT NULL,
  `refund_state` tinyint(1) NOT NULL DEFAULT '0',
  `refund_time` int(10) NOT NULL,
  `finish_refund_time` int(10) DEFAULT NULL,
  `refund_info` char(255) NOT NULL,
  `re_refund_info` char(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` char(100) NOT NULL,
  `admin_id` int(11) DEFAULT '0',
  `admin_name` char(100) DEFAULT NULL,
  `bank_name` char(100) DEFAULT NULL,
  `bank_account` char(50) DEFAULT NULL,
  `bank_card_number` char(50) DEFAULT NULL,
  PRIMARY KEY (`refund_id`),
  KEY `order_id` (`order_sn`,`goods_id_str`,`refund_type`,`refund_time`,`user_id`,`user_name`,`admin_id`,`admin_name`),
  KEY `refund_state` (`refund_state`),
  KEY `refund_price` (`refund_price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_virtual_goods;
CREATE TABLE dbshop_virtual_goods (
  `virtual_goods_id` int(11) NOT NULL AUTO_INCREMENT,
  `virtual_goods_account` char(150) NOT NULL,
  `virtual_goods_account_type` tinyint(1) NOT NULL DEFAULT '1',
  `virtual_goods_password` char(150) NOT NULL,
  `virtual_goods_password_type` tinyint(1) NOT NULL DEFAULT '1',
  `virtual_goods_end_time` int(10) DEFAULT '0',
  `virtual_goods_state` tinyint(1) NOT NULL DEFAULT '1',
  `order_sn` char(50) DEFAULT NULL,
  `order_id` int(11) NOT NULL DEFAULT '0',
  `goods_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `user_name` char(100) DEFAULT NULL,
  PRIMARY KEY (`virtual_goods_id`),
  KEY `virtual_goods_account` (`virtual_goods_account`,`virtual_goods_password`,`virtual_goods_end_time`,`virtual_goods_state`,`order_sn`,`order_id`,`goods_id`,`user_id`,`user_name`),
  KEY `virtual_goods_account_type` (`virtual_goods_account_type`),
  KEY `virtual_goods_password_type` (`virtual_goods_password_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_user_integral_type;
CREATE TABLE dbshop_user_integral_type (
  `integral_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `default_integral_num` int(11) NOT NULL DEFAULT '0',
  `integral_type_mark` char(20) NOT NULL,
  `integral_currency_con` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`integral_type_id`),
  KEY `integral_type_mark` (`integral_type_mark`),
  KEY `integral_currency_con` (`integral_currency_con`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS dbshop_user_integral_type_extend;
CREATE TABLE dbshop_user_integral_type_extend (
  `integral_type_id` int(11) NOT NULL,
  `integral_type_name` char(50) NOT NULL,
  `language` char(10) NOT NULL,
  KEY `integral_type_id` (`integral_type_id`,`integral_type_name`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;