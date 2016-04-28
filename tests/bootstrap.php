<?php

use \Charcoal\App\App;
use \Charcoal\App\AppConfig;
use \Charcoal\App\AppContainer;

use \Psr\Log\NullLogger;

/** Composer autoloader for Charcoal's PSR4-compliant Unit Tests */
$autoloader = require __DIR__.'/../vendor/autoload.php';
$autoloader->add('Charcoal\\', __DIR__.'/src/');
$autoloader->add('Charcoal\\Tests\\', __DIR__);

// Disable Logger
$GLOBALS['container']['logger'] = function ($c) {
    return new NullLogger();
};

$config = new AppConfig();
$config->merge([
    'base_path' => (dirname(__DIR__) . '/'),
    'databases'=>[
        'default'=>[
            'database'=>'charcoal_examples',
            'username'=>'root',
            'password'=>''
        ]
    ],
    'default_database'=>'default'
]);
$GLOBALS['container'] = new AppContainer([
    'config' => $config,
    'metadata' => [
        'paths' => __DIR__.'/metadata/'
    ]

]);

// Charcoal / Slim is the main app
$GLOBALS['app'] = App::instance($GLOBALS['container']);
$GLOBALS['app']->setLogger(new \Psr\Log\NullLogger());
