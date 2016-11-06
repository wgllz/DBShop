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
  'Region\Module'                      => __DIR__ . '/Module.php',
  'Region\Controller\RegionController' => __DIR__ . '/src/Region/Controller/RegionController.php',
  'Region\Model\Region'                => __DIR__ . '/src/Region/Model/Region.php',
  'Region\Model\RegionExtend'          => __DIR__ . '/src/Region/Model/RegionExtend.php',
  'Region\Model\RegionExtendTable'     => __DIR__ . '/src/Region/Model/RegionExtendTable.php',
  'Region\Model\RegionTable'           => __DIR__ . '/src/Region/Model/RegionTable.php',
);
