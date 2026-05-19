<?php

namespace Charcoal\Tests\Config\Mixin;

// From 'charcoal-config'
use Charcoal\Tests\Config\Mixin\FileLoader\AbstractFileLoaderTestCase;
use Charcoal\Config\FileAwareInterface;
use Charcoal\Config\FileAwareTrait;
use InvalidArgumentException;

/**
 * Test FileAwareTrait
 */
#[\PHPUnit\Framework\Attributes\CoversTrait(\Charcoal\Config\FileAwareTrait::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\FileAwareTrait::class, 'loadFile()')]
class FileAwareTest extends AbstractFileLoaderTestCase
{
    /**
     * Asserts that the object implements FileAwareInterface.
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testFileAwareInterface(): void
    {
        $this->assertInstanceOf(FileAwareInterface::class, $this->obj);
    }

    public function testLoadWithUnsupportedFormat(): void
    {
        $this->expectExceptionMessageMatches('/^Unsupported file format for ".+?"; must be one of ".+?"$/');
        $this->expectException(InvalidArgumentException::class);

        $path = $this->getPathToFixture('fail/unsupported.txt');
        $this->obj->loadFile($path);
    }

    public function testLoadWithInvalidPath(): void
    {
        $this->expectExceptionMessageMatches('/^File ".+?" does not exist$/');
        $this->expectException(InvalidArgumentException::class);

        $path = $this->getPathToFixture('fail/missing.ini');
        $this->obj->loadFile($path);
    }

    public function testLoadWithInvalidType(): void
    {
        $this->expectExceptionMessage('File must be a string');
        $this->expectException(InvalidArgumentException::class);

        $path = null;
        $this->obj->loadFile($path);
    }
}
