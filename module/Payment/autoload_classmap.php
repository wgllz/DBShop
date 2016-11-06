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
  'Payment\Module'                       => __DIR__ . '/Module.php',
  'Payment\Controller\PaymentController' => __DIR__ . '/src/Payment/Controller/PaymentController.php',
  'Payment\Form\PaymentForm'             => __DIR__ . '/src/Payment/Form/PaymentForm.php',
  'Payment\Service\AlipayService'        => __DIR__ . '/src/Payment/Service/AlipayService.php',
  'Payment\Service\HdfkService'          => __DIR__ . '/src/Payment/Service/HdfkService.php',
  'Payment\Service\PaypalService'        => __DIR__ . '/src/Payment/Service/PaypalService.php',
  'Payment\Service\XxzfService'          => __DIR__ . '/src/Payment/Service/XxzfService.php',
);
