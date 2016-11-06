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
  'User\Module'                               => __DIR__ . '/Module.php',
  'User\Controller\IntegralController'        => __DIR__ . '/src/User/Controller/IntegralController.php',
  'User\Controller\UserController'            => __DIR__ . '/src/User/Controller/UserController.php',
  'User\Controller\UsergroupController'       => __DIR__ . '/src/User/Controller/UsergroupController.php',
  'User\Controller\UserintegrationController' => __DIR__ . '/src/User/Controller/UserintegrationController.php',
  'User\Form\IntegrationForm'                 => __DIR__ . '/src/User/Form/IntegrationForm.php',
  'User\FormValidate\FormMessage'             => __DIR__ . '/src/User/FormValidate/FormMessage.php',
  'User\FormValidate\FormUserValidate'        => __DIR__ . '/src/User/FormValidate/FormUserValidate.php',
  'User\Model\IntegralRule'                   => __DIR__ . '/src/User/Model/IntegralRule.php',
  'User\Model\IntegralRuleTable'              => __DIR__ . '/src/User/Model/IntegralRuleTable.php',
  'User\Model\User'                           => __DIR__ . '/src/User/Model/User.php',
  'User\Model\UserAddress'                    => __DIR__ . '/src/User/Model/UserAddress.php',
  'User\Model\UserAddressTable'               => __DIR__ . '/src/User/Model/UserAddressTable.php',
  'User\Model\UserFavorites'                  => __DIR__ . '/src/User/Model/UserFavorites.php',
  'User\Model\UserFavoritesTable'             => __DIR__ . '/src/User/Model/UserFavoritesTable.php',
  'User\Model\UserGroup'                      => __DIR__ . '/src/User/Model/UserGroup.php',
  'User\Model\UserGroupExtend'                => __DIR__ . '/src/User/Model/UserGroupExtend.php',
  'User\Model\UserGroupExtendTable'           => __DIR__ . '/src/User/Model/UserGroupExtendTable.php',
  'User\Model\UserGroupTable'                 => __DIR__ . '/src/User/Model/UserGroupTable.php',
  'User\Model\UserMailLog'                    => __DIR__ . '/src/User/Model/UserMailLog.php',
  'User\Model\UserMailLogTable'               => __DIR__ . '/src/User/Model/UserMailLogTable.php',
  'User\Model\UserTable'                      => __DIR__ . '/src/User/Model/UserTable.php',
  'User\Model\UserMoneyLog'                   => __DIR__ . '/src/User/Model/UserMoneyLog.php',
  'User\Model\UserMoneyLogTable'              => __DIR__ . '/src/User/Model/UserMoneyLogTable.php',
  'User\Model\WithdrawLog'                    => __DIR__ . '/src/User/Model/WithdrawLog.php',
  'User\Model\WithdrawLogTable'               => __DIR__ . '/src/User/Model/WithdrawLogTable.php',
  'User\Model\PayCheck'                       => __DIR__ . '/src/User/Model/PayCheck.php',
  'User\Model\PayCheckTable'                  => __DIR__ . '/src/User/Model/PayCheckTable.php',

  'User\Model\UserIntegralTypeTable'          => __DIR__ . '/src/User/Model/UserIntegralTypeTable.php',
  'User\Model\UserIntegralTypeExtendTable'    => __DIR__ . '/src/User/Model/UserIntegralTypeExtendTable.php',

  'User\Service\UcenterService'               => __DIR__ . '/src/User/Service/UcenterService.php',
);
