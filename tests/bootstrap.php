<?php

use Charcoal\Translator\LocalesManager;
use Charcoal\Translator\Translator;

$autoloader = require __DIR__.'/../vendor/autoload.php';
$autoloader->add('Charcoal\\', __DIR__.'/src/');
$autoloader->add('Charcoal\\Tests\\', __DIR__);

$GLOBALS['locales_manager'] = new LocalesManager([
    'locales' => [
        'en' => []
    ]
]);

$GLOBALS['translator'] = new Translator([
    'manager'=>$GLOBALS['locales_manager']
]);
