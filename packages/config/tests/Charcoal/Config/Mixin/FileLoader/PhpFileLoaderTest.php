<?php

declare(strict_types=1);

namespace Charcoal\Tests\Config\Mixin\FileLoader;

// From 'charcoal-config'
use Charcoal\Tests\Config\Mixin\FileLoader\AbstractFileLoaderTestCase;
use Charcoal\Config\FileAwareTrait;
use UnexpectedValueException;

/**
 * Test {@see FileAwareTrait::loadPhpFile() PHP File Loading}
 */
#[\PHPUnit\Framework\Attributes\CoversTrait(\Charcoal\Config\FileAwareTrait::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\FileAwareTrait::class, 'loadPhpFile()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\FileAwareTrait::class, 'loadFile()')]
class PhpFileLoaderTest extends AbstractFileLoaderTestCase
{
    /**
     * Asserts that the File Loader supports PHP config files.
     */
    public function testLoadFile(): void
    {
        $path = $this->getPathToFixture('pass/valid1.php');
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
     * Asserts that the scope of PHP config files is bound to the File Loader.
     */
    public function testLoadFileThatMutatesContext(): void
    {
        $path = $this->getPathToFixture('pass/valid3.php');
        $data = $this->obj->loadFile($path);

        $this->assertEquals([], $data);
        $this->assertEquals('baz', $this->obj->foo);
    }

    /**
     * Asserts that an empty file is silently ignored.
     */
    public function testLoadEmptyFile(): void
    {
        $path = $this->getPathToFixture('pass/empty.php');
        $data = $this->obj->loadFile($path);

        $this->assertEquals([], $data);
    }

    /**
     * Asserts that a broken file is NOT ignored.
     */
    #[\PHPUnit\Framework\Attributes\RequiresPhp('>=8.1.0')]
    public function testLoadMalformedFile(): void
    {
        $this->expectExceptionMessageMatches('/^PHP file ".+?" could not be parsed: .+$/');
        $this->expectException(UnexpectedValueException::class);

        // phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
        $path = $this->getPathToFixture('fail/malformed.php');
        $this->obj->loadFile($path);
        // phpcs:enable
    }

    /**
     * Asserts that an exception thrown within the file is caught.
     */
    public function testLoadExceptionalFile(): void
    {
        $this->expectExceptionMessageMatches('/^PHP file ".+?" could not be parsed: Thrown Exception$/');
        $this->expectException(UnexpectedValueException::class);

        $path = $this->getPathToFixture('fail/exception.php');
        $this->obj->loadFile($path);
    }
}
