<?php

namespace Charcoal\Tests\Config\Mixin\FileLoader;

use LogicException;
use ReflectionProperty;

// From 'charcoal-config'
use Charcoal\Tests\Config\Mixin\FileLoader\AbstractFileLoaderTestCase;
use Charcoal\Config\FileAwareTrait;
use UnexpectedValueException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileAwareTrait::class)]
class YamlFileLoaderTest extends AbstractFileLoaderTestCase
{
    /**
     * Asserts that the File Loader supports '.yml' YAML config files.
     *
     * @covers FileAwareTrait::loadYamlFile()
     * @covers FileAwareTrait::loadFile()
     * @return void
     */
    public function testLoadFileWithYmlExtension()
    {
        $path = $this->getPathToFixture('pass/valid1.yml');
        $data = $this->obj->loadFile($path);

        $this->assertEquals('localhost', $data['host']);
        $this->assertEquals('11211', $data['port']);
        $this->assertEquals(
            [
                'pdo_mysql',
                'pdo_pgsql',
                'pdo_sqlite',
            ],
            $data['drivers']
        );
    }

    /**
     * Asserts that the File Loader supports '.yaml' YAML config files.
     *
     * @covers FileAwareTrait::loadYamlFile()
     * @covers FileAwareTrait::loadFile()
     * @return void
     */
    public function testLoadFileWithYamlExtension()
    {
        $path = $this->getPathToFixture('pass/valid2.yaml');
        $data = $this->obj->loadFile($path);

        $this->assertEquals('localhost', $data['host']);
        $this->assertEquals('11211', $data['port']);
        $this->assertEquals(
            [
                'pdo_mysql',
                'pdo_pgsql',
                'pdo_sqlite',
            ],
            $data['drivers']
        );
    }

    /**
     * Asserts that the File Loader throws an exception if the YAML Parser is unavailable.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @covers FileAwareTrait::loadYamlFile()
     * @return void
     */
    public function testLoadFileWithNoYamlParser()
    {
        if (class_exists('Symfony\Component\Yaml\Parser', false)) {
            $this->markTestSkipped(
              'The Symfony YAML component was loaded before the test could run'
            );
            return;
        }

        $this->expectExceptionMessage('YAML format requires the Symfony YAML component');
        $this->expectException(LogicException::class);

        $this->disableSymfonyYamlComponent();

        $path = $this->getPathToFixture('pass/valid1.yml');
        $data = $this->obj->loadFile($path);
    }

    /**
     * Asserts that an empty file is silently ignored.
     *
     * @covers FileAwareTrait::loadYamlFile()
     * @return void
     */
    public function testLoadEmptyFile()
    {
        $path = $this->getPathToFixture('pass/empty.yml');
        $data = $this->obj->loadFile($path);

        $this->assertEquals([], $data);
    }

    /**
     * Asserts that a broken file is NOT ignored.
     *
     * @covers FileAwareTrait::loadYamlFile()
     * @return void
     */
    public function testLoadMalformedFile()
    {
        $this->expectExceptionMessageMatches('/^YAML file ".+?" could not be parsed: .+$/');
        $this->expectException(UnexpectedValueException::class);

        $path = $this->getPathToFixture('pass/malformed.yml');
        $data = $this->obj->loadFile($path);
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

        // If PSR-0/4 autoloading was optimized
        $classMap = $autoloader->getClassMap();
        if (isset($classMap['Symfony\\Component\\Yaml\\Parser'])) {
            $refClassMap = new ReflectionProperty($autoloader, 'classMap');

            unset($classMap['Symfony\\Component\\Yaml\\Parser']);
            $refClassMap->setValue($autoloader, $classMap);
        }

        $prefixesPsr4 = $autoloader->getPrefixesPsr4();
        if (isset($prefixesPsr4['Symfony\\Component\\Yaml\\'])) {
            $refPrefixesPsr4 = new ReflectionProperty($autoloader, 'prefixDirsPsr4');

            unset($prefixesPsr4['Symfony\\Component\\Yaml\\']);
            $refPrefixesPsr4->setValue($autoloader, $prefixesPsr4);
        }
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

        // If PSR-0/4 autoloading was optimized
        $classMap = $autoloader->getClassMap();
        if (!isset($classMap['Symfony\\Component\\Yaml\\Parser'])) {
            $refClassMap = new ReflectionProperty($autoloader, 'classMap');

            $refClassLoader  = $refClassMap->getDeclaringClass();
            $classLoaderPath = $refClassLoader->getFileName();

            $vendorDir = dirname(dirname($classLoaderPath));
            $prefixesPsr4['Symfony\\Component\\Yaml\\Parser'] = [ $vendorDir.'/symfony/yaml/Parser.php' ];
            $refClassMap->setValue($autoloader, $prefixesPsr4);
        }

        $prefixesPsr4 = $autoloader->getPrefixesPsr4();
        if (!isset($prefixesPsr4['Symfony\\Component\\Yaml\\'])) {
            $refPrefixesPsr4 = new ReflectionProperty($autoloader, 'prefixDirsPsr4');

            $refClassLoader  = $refPrefixesPsr4->getDeclaringClass();
            $classLoaderPath = $refClassLoader->getFileName();

            $vendorDir = dirname(dirname($classLoaderPath));
            $prefixesPsr4['Symfony\\Component\\Yaml\\'] = [ $vendorDir.'/symfony/yaml' ];
            $refPrefixesPsr4->setValue($autoloader, $prefixesPsr4);
        }
    }
}
