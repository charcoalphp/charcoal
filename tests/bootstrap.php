<?php


date_default_timezone_set('UTC');

/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';

define('EXAMPLES_DIR', realpath(__DIR__.'/examples'));
define('OUTPUT_DIR', realpath(__DIR__.'/tmp/images'));
