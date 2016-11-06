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
  'Stock\Module'                      => __DIR__ . '/Module.php',
  'Stock\Controller\StateController'  => __DIR__ . '/src/Stock/Controller/StateController.php',
  'Stock\Controller\StockController'  => __DIR__ . '/src/Stock/Controller/StockController.php',
  'Stock\Model\StockState'            => __DIR__ . '/src/Stock/Model/StockState.php',
  'Stock\Model\StockStateExtend'      => __DIR__ . '/src/Stock/Model/StockStateExtend.php',
  'Stock\Model\StockStateExtendTable' => __DIR__ . '/src/Stock/Model/StockStateExtendTable.php',
  'Stock\Model\StockStateTable'       => __DIR__ . '/src/Stock/Model/StockStateTable.php',
);
