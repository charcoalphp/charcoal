<?php

namespace Charcoal\Tests\Config\FileLoader;

// From 'charcoal-config'
use Charcoal\Tests\Config\FileLoader\AbstractFileLoaderTest;
use Charcoal\Config\AbstractConfig;
use Charcoal\Config\GenericConfig;

/**
 * Test {@see AbstractConfig::loadIniFile() INI Config File Loading}
 *
 * @coversDefaultClass \Charcoal\Config\AbstractConfig
 */
class IniFileLoaderTest extends AbstractFileLoaderTest
{
    /**
     * Asserts that the Config supports INI config files.
     *
     * @covers ::loadIniFile()
     * @covers ::loadFile()
     * @return void
     */
    public function testAddFile()
    {
        $path = $this->getPathToFixture('pass/valid1.ini');
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
     * Asserts that the Config supports key-paths in INI config files.
     *
     * @covers ::loadIniFile()
     * @return void
     */
    public function testAddFileWithDelimitedData()
    {
        $path = $this->getPathToFixture('pass/valid2.ini');
        $this->cfg->addFile($path);

        $this->assertEquals('localhost', $this->cfg['host']);
        $this->assertEquals('utf8mb4', $this->cfg['database']['charset']);
        $this->assertEquals(
            [
                'pdo_mysql',
                'pdo_pgsql',
                'pdo_sqlite',
            ],
            $this->cfg['database']['drivers']
        );
    }

    /**
     * Assert that an empty file is silently ignored.
     *
     * @covers ::loadIniFile()
     * @return void
     */
    public function testAddEmptyFile()
    {
        $path = $this->getPathToFixture('pass/empty.ini');
        $this->cfg->addFile($path);

        $this->assertEquals([], $this->cfg->data());
    }

    /**
     * Assert that a broken file is NOT ignored.
     *
     * @expectedException              UnexpectedValueException
     * @expectedExceptionMessageRegExp /^INI file ".+?" is empty or invalid$/
     *
     * @covers ::loadIniFile()
     * @return void
     */
    public function testAddMalformedFile()
    {
        // phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
        $path = $this->getPathToFixture('fail/malformed.ini');
        @$this->cfg->addFile($path);
        // phpcs:enable
    }

    /**
     * Assert that an unparsable file is silently ignored.
     *
     * @covers ::loadIniFile()
     * @return void
     */
    public function testAddUnparsableFile()
    {
        // phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
        $path = $this->getPathToFixture('pass/invalid.ini');
        @$this->cfg->addFile($path);
        // phpcs:enable

        $this->assertEquals([], $this->cfg->data());
    }
}
