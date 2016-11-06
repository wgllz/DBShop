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
    'Email\Module'                     => __DIR__ . '/Module.php',
    'Email\Controller\EmailController' => __DIR__ . '/src/Email/Controller/EmailController.php',
    'Email\Common\Service\SendMail'    => __DIR__ . '/src/Email/Service/SendMail.php',
    'Email\Common\Service\SendPhoneSms'    => __DIR__ . '/src/Email/Service/SendPhoneSms.php',
);