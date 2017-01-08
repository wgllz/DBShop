<?php
$defaultModule	= include DBSHOP_PATH . '/data/moduledata/moduleini/Module.default.php';
$extendModule	= include DBSHOP_PATH . '/data/moduledata/moduleini/Module.extend.php';
$module['modules']	= !empty($extendModule['modules']) ? array_merge($defaultModule['modules'], $extendModule['modules']) : $defaultModule['modules'];

return $module;