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
  'Admin\Module'                         => __DIR__ . '/Module.php',
  'Admin\Controller\AdminController'     => __DIR__ . '/src/Admin/Controller/AdminController.php',
  'Admin\Controller\BaseController'      => __DIR__ . '/src/Admin/Controller/BaseController.php',
  'Admin\Controller\HomeController'      => __DIR__ . '/src/Admin/Controller/HomeController.php',
  'Admin\FormValidate\FormAdminValidate' => __DIR__ . '/src/Admin/FormValidate/FormAdminValidate.php',
  'Admin\Helper\DbshopCommonFun'         => __DIR__ . '/src/Admin/Helper/DbshopCommonFun.php',
  'Admin\Helper\Helper'                  => __DIR__ . '/src/Admin/Helper/Helper.php',
  'Admin\Model\Admin'                    => __DIR__ . '/src/Admin/Model/Admin.php',
  'Admin\Model\AdminGroup'               => __DIR__ . '/src/Admin/Model/AdminGroup.php',
  'Admin\Model\AdminGroupExtend'         => __DIR__ . '/src/Admin/Model/AdminGroupExtend.php',
  'Admin\Model\AdminGroupExtendTable'    => __DIR__ . '/src/Admin/Model/AdminGroupExtendTable.php',
  'Admin\Model\AdminGroupTable'          => __DIR__ . '/src/Admin/Model/AdminGroupTable.php',
  'Admin\Model\AdminTable'               => __DIR__ . '/src/Admin/Model/AdminTable.php',
  'Admin\Model\DbshopSqlTransaction'     => __DIR__ . '/src/Admin/Model/DbshopSqlTransaction.php',
);
