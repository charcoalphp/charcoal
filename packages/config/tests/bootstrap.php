<?php

if (($_ENV['TEST_MODE'] ?? '') === 'PACKAGE') {
    require getcwd().'/tests/bootstrap.php';
} else {
    /**
     * Assigin the Composer autoloader to the global state.
     *
     * @see \Charcoal\Tests\Config\Mixin\FileLoader\YamlFileLoaderTest::testAddFileWithNoYamlParser()
     *     The autoloader is needed to temporarily remove the "symfony/yaml" package
     *     in order to test the Config's behaviour when faced with a missiong YAML parser.
     *
     * @var \Composer\Autoload\ClassLoader $autoloader
     */
    $GLOBALS['autoloader'] = require dirname(__DIR__).'/vendor/autoload.php';
}
