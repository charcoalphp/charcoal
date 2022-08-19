<?php

namespace Charcoal\Tests\Config\Mixin\FileLoader;

// From 'charcoal-config'
use Charcoal\Tests\Config\Mixin\FileLoader\AbstractFileLoaderTestCase;
use Charcoal\Config\FileAwareTrait;
use UnexpectedValueException;

/**
 * Test {@see FileAwareTrait::loadPhpFile() PHP File Loading}
 *
 * @coversDefaultClass \Charcoal\Config\FileAwareTrait
 */
class PhpFileLoaderTest extends AbstractFileLoaderTestCase
{
    /**
     * Asserts that the File Loader supports PHP config files.
     *
     * @covers ::loadPhpFile()
     * @covers ::loadFile()
     * @return void
     */
    public function testLoadFile()
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
     *
     * @covers ::loadPhpFile()
     * @return void
     */
    public function testLoadFileThatMutatesContext()
    {
        $path = $this->getPathToFixture('pass/valid3.php');
        $data = $this->obj->loadFile($path);

        $this->assertEquals([], $data);
        $this->assertEquals('baz', $this->obj->foo);
    }

    /**
     * Asserts that an empty file is silently ignored.
     *
     * @covers ::loadPhpFile()
     * @return void
     */
    public function testLoadEmptyFile()
    {
        $path = $this->getPathToFixture('pass/empty.php');
        $data = $this->obj->loadFile($path);

        $this->assertEquals([], $data);
    }

    /**
     * Asserts that a broken file is NOT ignored.
     *
     * @requires PHP >= 7.0
     * @covers   ::loadPhpFile()
     * @return   void
     */
    public function testLoadMalformedFileInPhp7()
    {
        $this->expectExceptionMessageMatches('/^PHP file ".+?" could not be parsed: .+$/');
        $this->expectException(UnexpectedValueException::class);

        // phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
        $path = $this->getPathToFixture('fail/malformed.php');
        $data = $this->obj->loadFile($path);
        // phpcs:enable
    }

    /**
     * Asserts that an exception thrown within the file is caught.
     *
     * @covers ::loadPhpFile()
     * @return void
     */
    public function testLoadExceptionalFile()
    {
        $this->expectExceptionMessageMatches('/^PHP file ".+?" could not be parsed: Thrown Exception$/');
        $this->expectException(UnexpectedValueException::class);

        $path = $this->getPathToFixture('fail/exception.php');
        $data = $this->obj->loadFile($path);
    }
}
