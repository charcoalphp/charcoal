<?php

namespace Charcoal\Tests\Config\Mixin\FileLoader;

// From 'charcoal-config'
use Charcoal\Tests\Config\Mixin\FileLoader\AbstractFileLoaderTestCase;
use Charcoal\Config\FileAwareTrait;
use UnexpectedValueException;

/**
 * Test {@see FileAwareTrait::loadIniFile() INI File Loading}
 *
 * @coversDefaultClass \Charcoal\Config\FileAwareTrait
 */
class IniFileLoaderTest extends AbstractFileLoaderTestCase
{
    /**
     * Asserts that the File Loader supports INI config files.
     *
     * @covers ::loadIniFile()
     * @covers ::loadFile()
     * @return void
     */
    public function testLoadFile()
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
     * @covers ::loadIniFile()
     * @return void
     */
    public function testLoadFileWithDelimitedData()
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
     *
     * @covers ::loadIniFile()
     * @return void
     */
    public function testLoadEmptyFile()
    {
        $path = $this->getPathToFixture('pass/empty.ini');
        $data = $this->obj->loadFile($path);

        $this->assertEquals([], $data);
    }

    /**
     * Asserts that a broken file is NOT ignored.
     *
     * @covers ::loadIniFile()
     * @return void
     */
    public function testLoadMalformedFile()
    {
        $this->expectExceptionMessageMatches('/^INI file ".+?" is empty or invalid$/');
        $this->expectException(UnexpectedValueException::class);

        // phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
        $path = $this->getPathToFixture('fail/malformed.ini');
        $data = @$this->obj->loadFile($path);
        // phpcs:enable
    }

    /**
     * Asserts that an unparsable file is silently ignored.
     *
     * @covers ::loadIniFile()
     * @return void
     */
    public function testLoadUnparsableFile()
    {
        // phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
        $path = $this->getPathToFixture('pass/unparsable.ini');
        $data = @$this->obj->loadFile($path);
        // phpcs:enable

        $this->assertEquals([], $data);
    }
}
