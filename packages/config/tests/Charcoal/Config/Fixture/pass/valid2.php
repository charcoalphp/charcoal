<?php

$this['host']    = 'localhost';
$this['port']    = 11211;
$this['memory']  = false;
$this['database.charset'] = 'utf8mb4';
$this['database.drivers'] = [
    'pdo_mysql',
    'pdo_pgsql',
    'pdo_sqlite',
];
$this['database'] = array_replace($this['database'], [
    'name' => 'mydb',
    'user' => 'myname',
    'pass' => 'secret',
]);
