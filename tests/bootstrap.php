<?php

$autoloader = require __DIR__.'/../vendor/autoload.php';
$autoloader->add('Charcoal\\', __DIR__.'/src/');
$autoloader->add('Charcoal\\Tests\\', __DIR__);

\Charcoal\Charcoal::init([
    'config'=>new \Charcoal\CharcoalConfig()
]);
