<?php

use \Charcoal\App\App;
use \Charcoal\App\AppConfig;
use \Charcoal\App\AppContainer;

use \Charcoal\Config\GenericConfig;

use \Psr\Log\NullLogger;

/** Composer autoloader for Charcoal's PSR4-compliant Unit Tests */
$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';
$autoloader->add('Charcoal\\', __DIR__.'/src/');
$autoloader->add('Charcoal\\Tests\\', __DIR__);
$autoloader->add('Charcoal\\Tests\\', __DIR__.'/Charcoal/');


$dsn = 'mysql:host=localhost;dbname=charcoal_examples';
$username = 'root';
$password = '';

$db = new PDO($dsn, $username, $password, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);


$GLOBALS['pdo'] = $db;
