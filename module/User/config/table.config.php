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
'UserTable'            => function () { return new \User\Model\UserTable();            },
'UserGroupTable'       => function () { return new \User\Model\UserGroupTable();       },
'UserGroupExtendTable' => function () { return new \User\Model\UserGroupExtendTable(); },
'UserAddressTable'     => function () { return new \User\Model\UserAddressTable();     },
'UserMailLogTable'     => function () { return new \User\Model\UserMailLogTable();     },
'UserFavoritesTable'   => function () { return new \User\Model\UserFavoritesTable();   },
'IntegralRuleTable'    => function () { return new \User\Model\IntegralRuleTable();    },
'IntegralLogTable'     => function () { return new \User\Model\IntegralLogTable();     },
'OtherLoginTable'      => function () { return new \User\Model\OtherLoginTable();      },
'UserMoneyLogTable'    => function () { return new \User\Model\UserMoneyLogTable();    },
'WithdrawLogTable'     => function () { return new \User\Model\WithdrawLogTable();     },
'PayCheckTable'        => function () { return new \User\Model\PayCheckTable();           },
'UserIntegralTypeTable'       => function () { return new \User\Model\UserIntegralTypeTable();       },
'UserIntegralTypeExtendTable' => function () { return new \User\Model\UserIntegralTypeExtendTable(); },

'ucenter'              => function () { return new \User\Service\UcenterService();   },
'IntegralRuleService'  => function () { return new \User\Service\IntegralRuleService();},

'QqLogin'              => function () { return new \User\Service\OtherLogin\QqLogin(); },
'WeixinLogin'          => function () { return new \User\Service\OtherLogin\WeixinLogin(); },
'AlipayLogin'          => function () { return new \User\Service\OtherLogin\AlipayLogin(); },
);