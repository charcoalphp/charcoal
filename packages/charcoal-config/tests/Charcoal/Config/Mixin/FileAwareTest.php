<?php

namespace Charcoal\Tests\Config\Mixin;

// From 'charcoal-config'
use Charcoal\Tests\Config\Mixin\FileLoader\AbstractFileLoaderTestCase;
use Charcoal\Config\FileAwareInterface;
use Charcoal\Config\FileAwareTrait;

/**
 * Test FileAwareTrait
 *
 * @coversDefaultClass \Charcoal\Config\FileAwareTrait
 */
class FileAwareTest extends AbstractFileLoaderTestCase
{
    /**
     * Asserts that the object implements FileAwareInterface.
     *
     * @coversNothing
     * @return void
     */
    public function testFileAwareInterface()
    {
        $this->assertInstanceOf(FileAwareInterface::class, $this->obj);
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
        $data = $this->obj->loadFile($path);
    }

    /**
     * @expectedException              InvalidArgumentException
     * @expectedExceptionMessageRegExp /^File ".+?" does not exist$/
     *
     * @covers ::loadFile()
     * @return void
     */
    public function testLoadWithInvalidPath()
    {
        $path = $this->getPathToFixture('fail/missing.ini');
        $data = $this->obj->loadFile($path);
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage File must be a string
     *
     * @covers ::loadFile()
     * @return void
     */
    public function testLoadWithInvalidType()
    {
        $path = null;
        $data = $this->obj->loadFile($path);
    }
}
