<?php

namespace Charcoal\Tests\Config;

// From 'charcoal-config'
use Charcoal\Tests\Config\AbstractConfigTest;
use Charcoal\Config\GenericConfig;

/**
 * Test the file loading capabilities of AbstractConfig
 *
 * For tests of supported formats, lookup {@see \Charcoal\Tests\Config\FileLoader}.
 *
 * @coversDefaultClass \Charcoal\Config\AbstractConfig
 */
class ConfigFileAwareTest extends AbstractConfigTest
{
    /**
     * @var GenericConfig
     */
    public $cfg;

    /**
     * Create a concrete GenericConfig instance.
     *
     * @return void
     */
    public function setUp()
    {
        $this->cfg = $this->createConfig();
    }

    /**
     * Create a GenericConfig instance.
     *
     * @param  mixed $data      Data to pre-populate the object.
     * @param  array $delegates Delegates to pre-populate the object.
     * @return GenericConfig
     */
    public function createConfig($data = null, array $delegates = null)
    {
        return new GenericConfig($data, $delegates);
    }

    /**
     * @expectedException              InvalidArgumentException
     * @expectedExceptionMessageRegExp /^Unsupported file format for ".+?"; must be one of ".+?"$/
     *
     * @covers ::loadFile()
     * @return void
     */
    public function testLoadWithUnsupportedFormat()
    {
        $path = $this->getPathToFixture('fail/unsupported.txt');
        $data = $this->cfg->loadFile($path);
    }

    /**
     * @expectedException              InvalidArgumentException
     * @expectedExceptionMessageRegExp /^Configuration file ".+?" does not exist$/
     *
     * @covers ::loadFile()
     * @return void
     */
    public function testLoadWithInvalidPath()
    {
        $path = $this->getPathToFixture('fail/missing.ini');
        $data = $this->cfg->loadFile($path);
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Configuration file must be a string
     *
     * @covers ::loadFile()
     * @return void
     */
    public function testLoadWithInvalidType()
    {
        $path = null;
        $data = $this->cfg->loadFile($path);
    }

    /**
     * @covers ::__construct()
     * @covers ::addFile()
     * @covers ::loadFile()
     *
     * @return void
     */
    public function testConstructWithSupportedFormat()
    {
        $path = $this->getPathToFixture('pass/valid.json');
        $cfg  = $this->createConfig($path);
        $this->assertEquals('localhost', $cfg->get('host'));
    }
}
