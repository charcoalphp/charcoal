<?php

namespace Charcoal\Tests\Config\Config\FileLoader;

// From 'charcoal-config'
use Charcoal\Tests\Config\Config\FileLoader\AbstractFileLoaderTestCase;
use Charcoal\Config\AbstractConfig;
use Charcoal\Config\GenericConfig;

/**
 * Test {@see AbstractConfig::loadJsonFile() JSON Config File Loading}
 *
 * @coversDefaultClass \Charcoal\Config\AbstractConfig
 */
class JsonFileLoaderTest extends AbstractFileLoaderTestCase
{
    /**
     * Asserts that the Config supports JSON config files.
     *
     * @covers ::loadJsonFile()
     * @covers ::loadFile()
     * @return void
     */
    public function testAddFile()
    {
        $path = $this->getPathToFixture('pass/valid.json');
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
     * Assert that an empty file is silently ignored.
     *
     * @covers ::loadJsonFile()
     * @return void
     */
    public function testAddEmptyFile()
    {
        $path = $this->getPathToFixture('pass/empty.json');
        $this->cfg->addFile($path);

        $this->assertEquals([], $this->cfg->data());
    }

    /**
     * Assert that a broken file is NOT ignored.
     *
     * @expectedException              UnexpectedValueException
     * @expectedExceptionMessageRegExp /^JSON file ".+?" could not be parsed: .+$/
     *
     * @covers ::loadJsonFile()
     * @return void
     */
    public function testAddMalformedFile()
    {
        // phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
        $path = $this->getPathToFixture('fail/malformed.json');
        @$this->cfg->addFile($path);
        // phpcs:enable
    }

    /**
     * Assert that an ordered list is NOT ignored.
     *
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Entity array access only supports non-numeric keys
     *
     * @covers ::loadJsonFile()
     * @return void
     */
    public function testAddFileWithInvalidArray()
    {
        $path = $this->getPathToFixture('fail/invalid1.json');
        $this->cfg->addFile($path);
    }

    /**
     * Assert that an invalid file is silently ignored.
     *
     * @covers ::loadJsonFile()
     * @return void
     */
    public function testAddFileWithInvalidType()
    {
        $path = $this->getPathToFixture('pass/invalid2.json');
        $this->cfg->addFile($path);

        $this->assertEquals([], $this->cfg->data());
    }
}
