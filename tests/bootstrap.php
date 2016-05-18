<?php

use \Charcoal\App\App;
use \Charcoal\App\AppConfig;
use \Charcoal\App\AppContainer;

$autoloader = require __DIR__.'/../vendor/autoload.php';
$autoloader->add('Charcoal\\', __DIR__.'/src/');
$autoloader->add('Charcoal\\Tests\\', __DIR__);

$config = new AppConfig([
    'base_path' => (dirname(__DIR__) . '/'),
    'metadata' => [
        'paths' => [
            'metadata/',
            'vendor/locomotivemtl/charcoal-app/metadata/',
            'vendor/locomotivemtl/charcoal-property/metadata/',
            'vendor/locomotivemtl/charcoal-base/metadata/'
        ]
    ],
    'service_providers' => [
        'charcoal/email/service-provider/email' => [],
        'charcoal/model/service-provider/model' => []
    ]
]);
$GLOBALS['container'] = new AppContainer([
    'config' => $config
]);

$GLOBALS['logger'] = new \Psr\Log\NullLogger();

// Charcoal / Slim is the main app
$GLOBALS['app'] = new App($GLOBALS['container']);
$GLOBALS['app']->setLogger($GLOBALS['logger']);
