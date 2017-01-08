<?php
/**
 * DBShop 电子商务系统
 *
 * ==========================================================================
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2016 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 * ==========================================================================
 *
 * @author    静静的风
 *
 */
return array(
  'Plugin\Module'                       => __DIR__ . '/Module.php',
  'Plugin\Controller\IndexController'   => __DIR__ . '/src/Plugin/Controller/IndexController.php',

  'Plugin\Model\Plugin'                 => __DIR__ . '/src/Plugin/Model/Plugin.php',
  'Plugin\Model\PluginTable'            => __DIR__ . '/src/Plugin/Model/PluginTable.php',
);
