<?php
return array(
        'driver' => 'Pdo',
        'dsn' => 'mysql:dbname={dbname};port={dbport};host={hostname}',
        'username' => '{username}',
        'password' => '{password}',
        'driver_options' => array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        )
);