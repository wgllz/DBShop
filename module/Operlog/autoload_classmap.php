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
  'Operlog\Module'                       => __DIR__ . '/Module.php',
  'Operlog\Controller\OperlogController' => __DIR__ . '/src/Operlog/Controller/OperlogController.php',
  'Operlog\Model\Operlog'                => __DIR__ . '/src/Operlog/Model/Operlog.php',
  'Operlog\Model\OperlogTable'           => __DIR__ . '/src/Operlog/Model/OperlogTable.php',
);
