<?php

// Composer autoloader for Charcoal's psr4-compliant Unit Tests
$autoloader = require __DIR__.'/../vendor/autoload.php';
$autoloader->add('Charcoal\\', __DIR__.'/src/');
$autoloader->add('Charcoal\\Tests\\', __DIR__);

define('EXAMPLES_DIR', realpath(__DIR__.'/examples'));
define('OUTPUT_DIR', realpath(__DIR__.'/tmp/images'));
