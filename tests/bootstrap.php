<?php

use \Charcoal\Charcoal;
use \Charcoal\CharcoalModule;
use \Charcoal\CharcoalConfig;

use \Psr\Log\NullLogger;

use \Slim\App;
use \Slim\Container;

/** Composer autoloader for Charcoal's PSR4-compliant Unit Tests */
$autoloader = require __DIR__.'/../vendor/autoload.php';
$autoloader->add('Charcoal\\', __DIR__.'/src/');
$autoloader->add('Charcoal\\Tests\\', __DIR__);

$GLOBALS['container'] = new Container();

// Disable Logger
$GLOBALS['container']['logger'] = function ($c) {

    return new NullLogger();
};


