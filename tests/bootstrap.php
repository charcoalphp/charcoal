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

// Import Local Configuration
$GLOBALS['container']['charcoal/config'] = function ($c) {

    $config = new CharcoalConfig();

    $env = preg_replace('/[^A-Za-z0-9_]+/', '', $c->environment['APPLICATION_ENV']);
    $dir = dirname(__DIR__);
    $xts = [ 'ini', 'json', 'php' ];

    while ($xts) {
        $cfg = sprintf('%1$s/config/config.%2$s.%3$s', $dir, $env, array_pop($xts));

        if (file_exists($cfg)) {
            $config->add_file($cfg);
            break;
        }
    }

    return $config;
};

$GLOBALS['app'] = new App($GLOBALS['container']);

CharcoalModule::setup($GLOBALS['app']);
