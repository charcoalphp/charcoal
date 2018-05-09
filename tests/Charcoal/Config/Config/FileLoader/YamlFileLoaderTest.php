<?php

namespace Charcoal\Tests\Config\Config\FileLoader;

use ReflectionProperty;

// From 'charcoal-config'
use Charcoal\Tests\Config\Config\FileLoader\AbstractFileLoaderTestCase;
use Charcoal\Config\AbstractConfig;
use Charcoal\Config\GenericConfig;

/**
 * Test {@see AbstractConfig::loadYamlFile() YAML Config File Loading}
 *
 * @coversDefaultClass \Charcoal\Config\AbstractConfig
 */
class YamlFileLoaderTest extends AbstractFileLoaderTestCase
{
    /**
     * Asserts that the Config supports '.yml' YAML config files.
     *
     * @covers ::loadYamlFile()
     * @covers ::loadFile()
     * @return void
     */
    public function testAddFileWithYmlExtension()
    {
        $path = $this->getPathToFixture('pass/valid1.yml');
        $this->cfg->addFile($path);

        $this->assertEquals('localhost', $this->cfg['host']);
        $this->assertEquals('11211', $this->cfg['port']);
        $this->assertEquals(
            [
                'pdo_mysql',
                'pdo_pgsql',
                'pdo_sqlite',
            ],
            $this->cfg['drivers']
        );
    }

    /**
     * Asserts that the Config supports '.yaml' YAML config files.
     *
     * @covers ::loadYamlFile()
     * @covers ::loadFile()
     * @return void
     */
    public function testAddFileWithYamlExtension()
    {
        $path = $this->getPathToFixture('pass/valid2.yaml');
        $this->cfg->addFile($path);

        $this->assertEquals('localhost', $this->cfg['host']);
        $this->assertEquals('11211', $this->cfg['port']);
        $this->assertEquals(
            [
                'pdo_mysql',
                'pdo_pgsql',
                'pdo_sqlite',
            ],
            $this->cfg['drivers']
        );
    }

    /**
     * Asserts that the Config throws an exception if the YAML Parser is unavailable.
     *
     * @expectedException        LogicException
     * @expectedExceptionMessage YAML format requires the Symfony YAML component
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @covers ::loadYamlFile()
     * @return void
     */
    public function testAddFileWithNoYamlParser()
    {
        $this->disableSymfonyYamlComponent();

        $path = $this->getPathToFixture('pass/valid1.yml');
        $this->cfg->addFile($path);
    }

    /**
     * Assert that an empty file is silently ignored.
     *
     * @covers ::loadYamlFile()
     * @return void
     */
    public function testAddEmptyFile()
    {
        $path = $this->getPathToFixture('pass/empty.yml');
        $this->cfg->addFile($path);

        $this->assertEquals([], $this->cfg->data());
    }

    /**
     * Assert that a broken file is NOT ignored.
     *
     * @expectedException              UnexpectedValueException
     * @expectedExceptionMessageRegExp /^YAML file ".+?" could not be parsed: .+$/
     *
     * @covers ::loadYamlFile()
     * @return void
     */
    public function testAddMalformedFile()
    {
        $path = $this->getPathToFixture('pass/malformed.yml');
        $this->cfg->addFile($path);
    }

    /**
     * Assert that an ordered list is NOT ignored.
     *
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Entity array access only supports non-numeric keys
     *
     * @covers ::loadYamlFile()
     * @return void
     */
    public function testAddFileWithInvalidArray()
    {
        $path = $this->getPathToFixture('fail/invalid1.yml');
        $this->cfg->addFile($path);
    }

    /**
     * Assert that an invalid file is silently ignored.
     *
     * @covers ::loadYamlFile()
     * @return void
     */
    public function testAddFileWithInvalidType()
    {
        $path = $this->getPathToFixture('pass/invalid2.yml');
        $this->cfg->addFile($path);

        $this->assertEquals([], $this->cfg->data());
    }

    /**
     * Remove the "symfony/yaml" package from Composer's search paths.
     *
     * @return void
     */
    public function disableSymfonyYamlComponent()
    {
        // phpcs:disable Squiz.PHP.GlobalKeyword.NotAllowed
        global $autoloader;
        // phpcs:enable

        $prefixesPsr4 = $autoloader->getPrefixesPsr4();
        if (!isset($prefixesPsr4['Symfony\\Component\\Yaml\\'])) {
            return;
        }

        $refPrefixesPsr4 = new ReflectionProperty($autoloader, 'prefixDirsPsr4');
        $refPrefixesPsr4->setAccessible(true);

        unset($prefixesPsr4['Symfony\\Component\\Yaml\\']);
        $refPrefixesPsr4->setValue($autoloader, $prefixesPsr4);
    }

    /**
     * Add the "symfony/yaml" package from Composer's search paths.
     *
     * @return void
     */
    public function enableSymfonyYamlComponent()
    {
        // phpcs:disable Squiz.PHP.GlobalKeyword.NotAllowed
        global $autoloader;
        // phpcs:enable

        $prefixesPsr4 = $autoloader->getPrefixesPsr4();
        if (isset($prefixesPsr4['Symfony\\Component\\Yaml\\'])) {
            return;
        }

        $refPrefixesPsr4 = new ReflectionProperty($autoloader, 'prefixDirsPsr4');
        $refPrefixesPsr4->setAccessible(true);

        $refClassLoader  = $refPrefixesPsr4->getDeclaringClass();
        $classLoaderPath = $refClassLoader->getFileName();

        $vendorDir = dirname(dirname($classLoaderPath));
        $prefixesPsr4['Symfony\\Component\\Yaml\\'] = [ $vendorDir.'/symfony/yaml' ];
        $refPrefixesPsr4->setValue($autoloader, $prefixesPsr4);
    }
}
