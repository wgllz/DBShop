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
  'Express\Module'                          => __DIR__ . '/Module.php',
  'Express\Controller\ExpressController'    => __DIR__ . '/src/Express/Controller/ExpressController.php',
  'Express\Model\Express'                   => __DIR__ . '/src/Express/Model/Express.php',
  'Express\Model\ExpressIndividuation'      => __DIR__ . '/src/Express/Model/ExpressIndividuation.php',
  'Express\Model\ExpressIndividuationTable' => __DIR__ . '/src/Express/Model/ExpressIndividuationTable.php',
  'Express\Model\ExpressTable'              => __DIR__ . '/src/Express/Model/ExpressTable.php',
  'Express\Common\Service\ExpressService'   => __DIR__ . '/src/Express/Service/ExpressService.php',
  'Express\Common\Service\ExpressStateApi'  => __DIR__ . '/src/Express/Service/ExpressStateApi.php',
);
