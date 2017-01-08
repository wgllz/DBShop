<?php
return array(
        'driver' => 'Pdo',
        'dsn' => 'mysql:dbname={dbname};port={dbport};host={hostname};charset=utf8',
        'username' => '{username}',
        'password' => '{password}',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))"
        )
);