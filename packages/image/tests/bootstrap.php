<?php


date_default_timezone_set('UTC');

/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require dirname(__DIR__).'/vendor/autoload.php';

define('EXAMPLES_PATH', __DIR__.'/examples');
define('OUTPUT_PATH', __DIR__.'/tmp/images');

if (OUTPUT_PATH && !file_exists(OUTPUT_PATH)) {
    mkdir(OUTPUT_PATH, 0777, true);
}

define('EXAMPLES_DIR', realpath(EXAMPLES_PATH));
define('OUTPUT_DIR', realpath(OUTPUT_PATH));
