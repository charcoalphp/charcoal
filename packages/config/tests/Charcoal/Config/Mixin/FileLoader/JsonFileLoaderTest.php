<?php

declare(strict_types=1);

namespace Charcoal\Tests\Config\Mixin\FileLoader;

// From 'charcoal-config'
use Charcoal\Tests\Config\Mixin\FileLoader\AbstractFileLoaderTestCase;
use Charcoal\Config\FileAwareTrait;
use UnexpectedValueException;

/**
 * Test {@see FileAwareTrait::loadJsonFile() JSON File Loading}
 */
#[\PHPUnit\Framework\Attributes\CoversTrait(\Charcoal\Config\FileAwareTrait::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\FileAwareTrait::class, 'loadJsonFile()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\FileAwareTrait::class, 'loadFile()')]
class JsonFileLoaderTest extends AbstractFileLoaderTestCase
{
    /**
     * Asserts that the File Loader supports JSON config files.
     */
    public function testLoadFile(): void
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
     */
    public function testLoadEmptyFile(): void
    {
        $path = $this->getPathToFixture('pass/empty.json');
        $data = $this->obj->loadFile($path);

        $this->assertEquals([], $data);
    }

    /**
     * Asserts that a broken file is NOT ignored.
     */
    public function testLoadMalformedFile(): void
    {
        $this->expectExceptionMessageMatches('/^JSON file ".+?" could not be parsed: .+$/');
        $this->expectException(UnexpectedValueException::class);

        // phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
        $path = $this->getPathToFixture('fail/malformed.json');
        @$this->obj->loadFile($path);
        // phpcs:enable
    }
}
