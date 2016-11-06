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
  'Orders\Module'                          => __DIR__ . '/Module.php',
  'Orders\Controller\OrdersController'     => __DIR__ . '/src/Orders/Controller/OrdersController.php',
  'Orders\Model\Order'                     => __DIR__ . '/src/Orders/Model/Order.php',
  'Orders\Model\OrderDeliveryAddress'      => __DIR__ . '/src/Orders/Model/OrderDeliveryAddress.php',
  'Orders\Model\OrderDeliveryAddressTable' => __DIR__ . '/src/Orders/Model/OrderDeliveryAddressTable.php',
  'Orders\Model\OrderGoods'                => __DIR__ . '/src/Orders/Model/OrderGoods.php',
  'Orders\Model\OrderGoodsTable'           => __DIR__ . '/src/Orders/Model/OrderGoodsTable.php',
  'Orders\Model\OrderLog'                  => __DIR__ . '/src/Orders/Model/OrderLog.php',
  'Orders\Model\OrderLogTable'             => __DIR__ . '/src/Orders/Model/OrderLogTable.php',
  'Orders\Model\OrderTable'                => __DIR__ . '/src/Orders/Model/OrderTable.php',
  'Orders\Model\OrderAmountLog'            => __DIR__ . '/src/Orders/Model/OrderAmountLog.php',
  'Orders\Model\OrderAmountLogTable'       => __DIR__ . '/src/Orders/Model/OrderAmountLogTable.php',
  'Orders\Model\OrderRefund'               => __DIR__ . '/src/Orders/Model/OrderRefund.php',
  'Orders\Model\OrderRefundTable'          => __DIR__ . '/src/Orders/Model/OrderRefundTable.php',
);
