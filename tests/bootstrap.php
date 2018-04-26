<?php

global $autoloader;

/**
 * Assigin the Composer autoloader to the global state.
 *
 * @see \Charcoal\Tests\Config\FileLoader\YamlFileLoaderTest::testAddFileWithNoYamlParser()
 *     The autoloader is needed to temporarily remove the "symfony/yaml" package
 *     in order to test the Config's behaviour when faced with a missiong YAML parser.
 *
 * @var \Composer\Autoload\ClassLoader $autoloader
 */
$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';
