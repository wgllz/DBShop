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
//配送对外输出接口
'shop_express'               => function () {return new \Express\Common\Service\ExpressService();           },
'shop_express_state'     => function () {return new \Express\Common\Service\ExpressStateApi();              },
);