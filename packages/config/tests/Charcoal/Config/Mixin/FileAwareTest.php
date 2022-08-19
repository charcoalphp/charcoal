<?php

namespace Charcoal\Tests\Config\Mixin;

// From 'charcoal-config'
use Charcoal\Tests\Config\Mixin\FileLoader\AbstractFileLoaderTestCase;
use Charcoal\Config\FileAwareInterface;
use Charcoal\Config\FileAwareTrait;
use InvalidArgumentException;

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
     * @covers ::loadFile()
     * @return void
     */
    public function testLoadWithUnsupportedFormat()
    {
        $this->expectExceptionMessageMatches('/^Unsupported file format for ".+?"; must be one of ".+?"$/');
        $this->expectException(InvalidArgumentException::class);

        $path = $this->getPathToFixture('fail/unsupported.txt');
        $data = $this->obj->loadFile($path);
    }

    /**
     * @covers ::loadFile()
     * @return void
     */
    public function testLoadWithInvalidPath()
    {
        $this->expectExceptionMessageMatches('/^File ".+?" does not exist$/');
        $this->expectException(InvalidArgumentException::class);

        $path = $this->getPathToFixture('fail/missing.ini');
        $data = $this->obj->loadFile($path);
    }

    /**
     * @covers ::loadFile()
     * @return void
     */
    public function testLoadWithInvalidType()
    {
        $this->expectExceptionMessage('File must be a string');
        $this->expectException(InvalidArgumentException::class);

        $path = null;
        $data = $this->obj->loadFile($path);
    }
}
