<?php

use \Slim\Container;
use \Slim\App;

use \Charcoal\CharcoalModule as CharcoalModule;
use \Charcoal\CharcoalConfig;

/** Composer autoloader for Charcoal's PSR4-compliant Unit Tests */
$autoloader = require __DIR__.'/../vendor/autoload.php';
$autoloader->add('Charcoal\\', __DIR__.'/src/');
$autoloader->add('Charcoal\\Tests\\', __DIR__);

/** @todo For now, this var needs to be set automatically */
//Charcoal::init();
//Charcoal::config()['ROOT'] = '';




// create container and configure it
$container = new Container();

$container['config'] = function($c) {
    $config = new CharcoalConfig();
    //$config->add_file('../config/config.php');
    return $config;
};

/*
$container['flash'] = function ($c) {
    return new Messages;
};
*/

$app = new App($container);

CharcoalModule::setup($app);
