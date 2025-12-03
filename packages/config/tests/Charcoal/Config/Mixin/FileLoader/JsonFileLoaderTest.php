<?php

namespace Charcoal\Tests\Config\Mixin\FileLoader;

// From 'charcoal-config'
use Charcoal\Tests\Config\Mixin\FileLoader\AbstractFileLoaderTestCase;
use Charcoal\Config\FileAwareTrait;
use UnexpectedValueException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileAwareTrait::class)]
class JsonFileLoaderTest extends AbstractFileLoaderTestCase
{
    /**
     * Asserts that the File Loader supports JSON config files.
     *
     * @covers FileAwareTrait::loadJsonFile()
     * @covers FileAwareTrait::loadFile()
     * @return void
     */
    public function testLoadFile()
    {
        $path = $this->getPathToFixture('pass/valid.json');
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
     * Asserts that an empty file is silently ignored.
     *
     * @covers FileAwareTrait::loadJsonFile()
     * @return void
     */
    public function testLoadEmptyFile()
    {
        $path = $this->getPathToFixture('pass/empty.json');
        $data = $this->obj->loadFile($path);

        $this->assertEquals([], $data);
    }

    /**
     * Asserts that a broken file is NOT ignored.
     *
     * @covers FileAwareTrait::loadJsonFile()
     * @return void
     */
    public function testLoadMalformedFile()
    {
        $this->expectExceptionMessageMatches('/^JSON file ".+?" could not be parsed: .+$/');
        $this->expectException(UnexpectedValueException::class);

        // phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
        $path = $this->getPathToFixture('fail/malformed.json');
        $data = @$this->obj->loadFile($path);
        // phpcs:enable
    }
}
