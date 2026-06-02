<?php

declare(strict_types=1);

namespace Charcoal\Tests\Config\Mixin\FileLoader;

// From 'charcoal-config'
use Charcoal\Tests\Config\Mixin\FileLoader\AbstractFileLoaderTestCase;
use Charcoal\Config\FileAwareTrait;
use UnexpectedValueException;

/**
 * Test {@see FileAwareTrait::loadIniFile() INI File Loading}
 */
#[\PHPUnit\Framework\Attributes\CoversTrait(\Charcoal\Config\FileAwareTrait::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\FileAwareTrait::class, 'loadIniFile()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\FileAwareTrait::class, 'loadFile()')]
class IniFileLoaderTest extends AbstractFileLoaderTestCase
{
    /**
     * Asserts that the File Loader supports INI config files.
     */
    public function testLoadFile(): void
    {
        $path = $this->getPathToFixture('pass/valid1.ini');
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
     * Asserts that the File Loader does NOT support key-paths in INI config files.
     *
     * @see    \Charcoal\Tests\Config\Config\ConfigFileAwareTest::testLoadIniFileWithDelimitedData
     */
    public function testLoadFileWithDelimitedData(): void
    {
        $path = $this->getPathToFixture('pass/valid2.ini');
        $data = $this->obj->loadFile($path);

        $this->assertEquals('localhost', $data['host']);
        $this->assertEquals('utf8mb4', $data['database.charset']);
        $this->assertEquals(
            [
                'pdo_mysql',
                'pdo_pgsql',
                'pdo_sqlite',
            ],
            $data['database.drivers']
        );
    }

    /**
     * Asserts that an empty file is silently ignored.
     */
    public function testLoadEmptyFile(): void
    {
        $path = $this->getPathToFixture('pass/empty.ini');
        $data = $this->obj->loadFile($path);

        $this->assertEquals([], $data);
    }

    /**
     * Asserts that a broken file is NOT ignored.
     */
    public function testLoadMalformedFile(): void
    {
        $this->expectExceptionMessageMatches('/^INI file ".+?" is empty or invalid$/');
        $this->expectException(UnexpectedValueException::class);

        // phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
        $path = $this->getPathToFixture('fail/malformed.ini');
        @$this->obj->loadFile($path);
        // phpcs:enable
    }

    /**
     * Asserts that an unparsable file is silently ignored.
     */
    public function testLoadUnparsableFile(): void
    {
        // phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
        $path = $this->getPathToFixture('pass/unparsable.ini');
        $data = @$this->obj->loadFile($path);
        // phpcs:enable

        $this->assertEquals([], $data);
    }
}
