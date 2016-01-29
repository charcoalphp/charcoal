<?php

use \Charcoal\App\App;
use \Charcoal\App\AppConfig;
use \Slim\Container as SlimContainer;

/** Composer autoloader for Charcoal's PSR4-compliant Unit Tests */
$autoloader = require __DIR__.'/../vendor/autoload.php';
$autoloader->add('Charcoal\\', __DIR__.'/src/');
$autoloader->add('Charcoal\\Tests\\', __DIR__);

/** @todo For now, this var needs to be set automatically */
//Charcoal::init();
//Charcoal::config()['ROOT'] = '';

// Create container and configure it (with charcoal-config)
$GLOBALS['container'] = new SlimContainer();

$GLOBALS['container']['charcoal/app/config'] = function($c) {
    $config = new AppConfig();
    $config->setData([
        'logger'=>[
            'level'=>'debug'
        ]
    ]);
    return $config;
};

// Charcoal / Slim is the main app
$GLOBALS['app'] = new App($GLOBALS['container']);

$GLOBALS['app']->setLogger(new \Monolog\Logger('charcoal.test'));
