<?php
// Monolog Dependencies
use \Monolog\Logger;
use \Monolog\Processor\UidProcessor;
use \Monolog\Handler\StreamHandler;

$autoloader = require __DIR__.'/../vendor/autoload.php';
$autoloader->add('Charcoal\\', __DIR__.'/src/');
$autoloader->add('Charcoal\\Tests\\', __DIR__);

$uid_processor = new UidProcessor();
$handler = new StreamHandler('charcoal.test.log', Logger::DEBUG);

$logger = new Logger('Charcoal');
$logger->pushProcessor($uid_processor);
$logger->pushHandler($handler);
$GLOBALS['logger'] = $logger;
