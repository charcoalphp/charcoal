<?php

namespace Charcoal\Tests\Config\Mixin\FileLoader;

use LogicException;
use ReflectionProperty;

// From 'charcoal-config'
use Charcoal\Tests\Config\Mixin\FileLoader\AbstractFileLoaderTestCase;
use Charcoal\Config\FileAwareTrait;
use UnexpectedValueException;

/**
 * Test {@see FileAwareTrait::loadYamlFile() YAML File Loading}
 */
#[\PHPUnit\Framework\Attributes\CoversTrait(\Charcoal\Config\FileAwareTrait::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\FileAwareTrait::class, 'loadYamlFile()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\FileAwareTrait::class, 'loadFile()')]
class YamlFileLoaderTest extends AbstractFileLoaderTestCase
{
    /**
     * Asserts that the File Loader supports '.yml' YAML config files.
     */
    public function testLoadFileWithYmlExtension(): void
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
     */
    public function testLoadFileWithYamlExtension(): void
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
     */
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    public function testLoadFileWithNoYamlParser(): void
    {
        if (class_exists(\Symfony\Component\Yaml\Parser::class, false)) {
            $this->markTestSkipped(
              'The Symfony YAML component was loaded before the test could run'
            );
        }

        $this->expectExceptionMessage('YAML format requires the Symfony YAML component');
        $this->expectException(LogicException::class);

        $this->disableSymfonyYamlComponent();

        $path = $this->getPathToFixture('pass/valid1.yml');
        $this->obj->loadFile($path);
    }

    /**
     * Asserts that an empty file is silently ignored.
     */
    public function testLoadEmptyFile(): void
    {
        $path = $this->getPathToFixture('pass/empty.yml');
        $data = $this->obj->loadFile($path);

        $this->assertEquals([], $data);
    }

    /**
     * Asserts that a broken file is NOT ignored.
     */
    public function testLoadMalformedFile(): void
    {
        $this->expectExceptionMessageMatches('/^YAML file ".+?" could not be parsed: .+$/');
        $this->expectException(UnexpectedValueException::class);

        $path = $this->getPathToFixture('pass/malformed.yml');
        $this->obj->loadFile($path);
    }

    /**
     * Remove the "symfony/yaml" package from Composer's search paths.
     */
    public function disableSymfonyYamlComponent(): void
    {
        // phpcs:disable Squiz.PHP.GlobalKeyword.NotAllowed
        global $autoloader;
        // phpcs:enable

        // If PSR-0/4 autoloading was optimized
        $classMap = $autoloader->getClassMap();
        if (isset($classMap[\Symfony\Component\Yaml\Parser::class])) {
            $refClassMap = new ReflectionProperty($autoloader, 'classMap');

            unset($classMap[\Symfony\Component\Yaml\Parser::class]);
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
     */
    public function enableSymfonyYamlComponent(): void
    {
        // phpcs:disable Squiz.PHP.GlobalKeyword.NotAllowed
        global $autoloader;
        // phpcs:enable

        // If PSR-0/4 autoloading was optimized
        $classMap = $autoloader->getClassMap();
        if (!isset($classMap[\Symfony\Component\Yaml\Parser::class])) {
            $refClassMap = new ReflectionProperty($autoloader, 'classMap');

            $refClassLoader  = $refClassMap->getDeclaringClass();
            $classLoaderPath = $refClassLoader->getFileName();

            $vendorDir = dirname($classLoaderPath, 2);
            $prefixesPsr4[\Symfony\Component\Yaml\Parser::class] = [ $vendorDir.'/symfony/yaml/Parser.php' ];
            $refClassMap->setValue($autoloader, $prefixesPsr4);
        }

        $prefixesPsr4 = $autoloader->getPrefixesPsr4();
        if (!isset($prefixesPsr4['Symfony\\Component\\Yaml\\'])) {
            $refPrefixesPsr4 = new ReflectionProperty($autoloader, 'prefixDirsPsr4');

            $refClassLoader  = $refPrefixesPsr4->getDeclaringClass();
            $classLoaderPath = $refClassLoader->getFileName();

            $vendorDir = dirname($classLoaderPath, 2);
            $prefixesPsr4['Symfony\\Component\\Yaml\\'] = [ $vendorDir.'/symfony/yaml' ];
            $refPrefixesPsr4->setValue($autoloader, $prefixesPsr4);
        }
    }
}
