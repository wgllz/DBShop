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
'OrderTable'                => function  () { return new \Orders\Model\OrderTable();                 },
'OrderLogTable'             => function  () { return new \Orders\Model\OrderLogTable();              },
'OrderGoodsTable'           => function  () { return new \Orders\Model\OrderGoodsTable();            },
'OrderDeliveryAddressTable' => function  () { return new \Orders\Model\OrderDeliveryAddressTable();  },
'OrderAmountLogTable'       => function  () { return new \Orders\Model\OrderAmountLogTable();        },
'OrderRefundTable'          => function  () { return new \Orders\Model\OrderRefundTable();           },
);