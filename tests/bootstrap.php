<?php

use \Charcoal\Config\GenericConfig;

/** Composer autoloader for Charcoal's PSR4-compliant Unit Tests */
$autoloader = require dirname(__DIR__).'/vendor/autoload.php';
$autoloader->add('Charcoal\\', __DIR__.'/src/');
$autoloader->add('Charcoal\\Tests\\', __DIR__.'/Charcoal/');

$databaseConfig = [
    'host'     => 'localhost',
    'database' => 'charcoal_test',
    'username' => 'root',
    'password' => '',
];

$appEnv = getenv('APPLICATION_ENV');
if ($appEnv !== 'testing') {
    $configPath = realpath(__DIR__.'/../../../../config/config.php');

    if (file_exists($configPath)) {
        $localConfig = new GenericConfig($configPath);

        if (isset($localConfig['databases'])) {
            $databaseIdent = isset($localConfig['default_database']) ? $localConfig['default_database'] : 'default';
            $localConfig   = $localConfig['databases'];

            if (isset($localConfig[$databaseIdent])) {
                $databaseConfig = array_merge($databaseConfig, $localConfig[$databaseIdent]);
            }
        }
    }
}

$dsn = sprintf(
    'mysql:host=%s;dbname=%s',
    $databaseConfig['host'],
    $databaseConfig['database']
);

$db = new PDO(
    $dsn,
    $databaseConfig['username'],
    $databaseConfig['password'],
    [ PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' ]
);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

$GLOBALS['pdo'] = $db;
