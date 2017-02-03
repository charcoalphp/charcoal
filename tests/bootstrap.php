<?php

use PDO;

use Cache\Adapter\Void\VoidCachePool;

use Psr\Log\NullLogger;

use Charcoal\App\App;
use Charcoal\App\AppConfig;
use Charcoal\App\AppContainer;


// Composer autoloader for Charcoal's psr4-compliant Unit Tests
$autoloader = require __DIR__.'/../vendor/autoload.php';
$autoloader->add('Charcoal\\', __DIR__.'/src/');
$autoloader->add('Charcoal\\Tests\\', __DIR__);

$config = new AppConfig([
    'base_path' => (dirname(__DIR__).'/'),
    'metadata' => [
        'paths' => [
            dirname(__DIR__).'/metadata/'
        ]
    ]
]);
$GLOBALS['container'] = new AppContainer([
    'config' => $config,
    'cache'  => new VoidCachePool(),
    'logger' => new NullLogger(),
    'database' => new PDO('sqlite::memory:')
]);
