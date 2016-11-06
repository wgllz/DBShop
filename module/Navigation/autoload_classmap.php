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
  'Navigation\Module'                          => __DIR__ . '/Module.php',
  'Navigation\Controller\NavigationController' => __DIR__ . '/src/Navigation/Controller/NavigationController.php',
  'Navigation\Model\Navigation'                => __DIR__ . '/src/Navigation/Model/Navigation.php',
  'Navigation\Model\NavigationExtend'          => __DIR__ . '/src/Navigation/Model/NavigationExtend.php',
  'Navigation\Model\NavigationExtendTable'     => __DIR__ . '/src/Navigation/Model/NavigationExtendTable.php',
  'Navigation\Model\NavigationTable'           => __DIR__ . '/src/Navigation/Model/NavigationTable.php',
);
