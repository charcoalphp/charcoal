<?php

namespace Charcoal\Tests\Config\FileLoader;

// From 'charcoal-config'
use Charcoal\Tests\Config\FileLoader\AbstractFileLoaderTest;
use Charcoal\Config\AbstractConfig;
use Charcoal\Config\GenericConfig;

/**
 * Test {@see AbstractConfig::loadPhpFile() PHP Config File Loading}
 *
 * @coversDefaultClass \Charcoal\Config\AbstractConfig
 */
class PhpFileLoaderTest extends AbstractFileLoaderTest
{
    /**
     * Asserts that the Config supports PHP config files.
     *
     * @covers ::loadPhpFile()
     * @covers ::loadFile()
     * @return void
     */
    public function testAddFile()
    {
        $path = $this->getPathToFixture('pass/valid1.php');
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
     * Asserts that the scope of PHP config files is bound to the Config.
     *
     * @covers ::loadPhpFile()
     * @return void
     */
    public function testAddFileThatMutatesContext()
    {
        $path = $this->getPathToFixture('pass/valid2.php');
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
     * @covers ::loadPhpFile()
     * @return void
     */
    public function testAddEmptyFile()
    {
        $path = $this->getPathToFixture('pass/empty.php');
        $this->cfg->addFile($path);

        $this->assertEquals([], $this->cfg->data());
    }

    /**
     * Assert that a broken file is NOT ignored.
     *
     * @expectedException              UnexpectedValueException
     * @expectedExceptionMessageRegExp /^PHP file ".+?" could not be parsed: .+$/
     *
     * @requires PHP >= 7.0
     * @covers   ::loadPhpFile()
     * @return   void
     */
    public function testAddMalformedFileInPhp7()
    {
        // phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
        $path = $this->getPathToFixture('fail/malformed.php');
        $this->cfg->addFile($path);
        // phpcs:enable
    }

    /**
     * Assert that an exception thrown within the file is caught.
     *
     * @expectedException              UnexpectedValueException
     * @expectedExceptionMessageRegExp /^PHP file ".+?" could not be parsed: Thrown Exception$/
     *
     * @covers ::loadPhpFile()
     * @return void
     */
    public function testAddExceptionalFile()
    {
        $path = $this->getPathToFixture('fail/exception.php');
        $this->cfg->addFile($path);
    }

    /**
     * Assert that an ordered list is NOT ignored.
     *
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Entity array access only supports non-numeric keys
     *
     * @covers ::loadPhpFile()
     * @return void
     */
    public function testAddFileWithInvalidArray()
    {
        $path = $this->getPathToFixture('fail/invalid1.php');
        $this->cfg->addFile($path);
    }

    /**
     * Assert that an invalid file is silently ignored.
     *
     * @covers ::loadPhpFile()
     * @return void
     */
    public function testAddFileWithInvalidType()
    {
        $path = $this->getPathToFixture('pass/invalid2.php');
        $this->cfg->addFile($path);

        $this->assertEquals([], $this->cfg->data());
    }
}
