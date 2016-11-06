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
  'System\Module'                      => __DIR__ . '/Module.php',
  'System\Controller\OnlineController' => __DIR__ . '/src/System/Controller/OnlineController.php',
  'System\Controller\SystemController' => __DIR__ . '/src/System/Controller/SystemController.php',
  'System\Controller\OptimizationController'     => __DIR__ . '/src/System/Controller/OptimizationController.php',
  'System\Model\Online'                => __DIR__ . '/src/System/Model/Online.php',
  'System\Model\OnlineGroup'           => __DIR__ . '/src/System/Model/OnlineGroup.php',
  'System\Model\OnlineGroupTable'      => __DIR__ . '/src/System/Model/OnlineGroupTable.php',
  'System\Model\OnlineTable'           => __DIR__ . '/src/System/Model/OnlineTable.php',
);
