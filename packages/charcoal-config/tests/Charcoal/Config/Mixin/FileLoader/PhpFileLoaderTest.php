<?php

namespace Charcoal\Tests\Config\Mixin\FileLoader;

// From 'charcoal-config'
use Charcoal\Tests\Config\Mixin\FileLoader\AbstractFileLoaderTestCase;
use Charcoal\Config\FileAwareTrait;

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
        $this->assertAttributeEquals('baz', 'foo', $this->obj);
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
     * @expectedException              UnexpectedValueException
     * @expectedExceptionMessageRegExp /^PHP file ".+?" could not be parsed: .+$/
     *
     * @requires PHP >= 7.0
     * @covers   ::loadPhpFile()
     * @return   void
     */
    public function testLoadMalformedFileInPhp7()
    {
        // phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
        $path = $this->getPathToFixture('fail/malformed.php');
        $data = $this->obj->loadFile($path);
        // phpcs:enable
    }

    /**
     * Asserts that an exception thrown within the file is caught.
     *
     * @expectedException              UnexpectedValueException
     * @expectedExceptionMessageRegExp /^PHP file ".+?" could not be parsed: Thrown Exception$/
     *
     * @covers ::loadPhpFile()
     * @return void
     */
    public function testLoadExceptionalFile()
    {
        $path = $this->getPathToFixture('fail/exception.php');
        $data = $this->obj->loadFile($path);
    }
}
