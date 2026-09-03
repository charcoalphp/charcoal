<?php

namespace Charcoal\Tests\App\Script;

use InvalidArgumentException;

use Charcoal\App\Script\PathScriptTrait;
use Charcoal\Tests\AbstractTestCase;

class PathScriptTraitTest extends AbstractTestCase
{
    /**
     * @var PathScriptStub
     */
    private $obj;

    /**
     * @var string
     */
    private $basePath;

    public function setUp(): void
    {
        $this->basePath = sys_get_temp_dir() . '/charcoal-path-script-' . uniqid('', true);
        mkdir($this->basePath);
        file_put_contents($this->basePath . '/sample.txt', 'ok');

        $this->obj = new PathScriptStub($this->basePath);
        PathScriptStub::clearGlobCache();
    }

    public function tearDown(): void
    {
        if (is_file($this->basePath . '/sample.txt')) {
            unlink($this->basePath . '/sample.txt');
        }
        if (is_dir($this->basePath)) {
            rmdir($this->basePath);
        }
    }

    public function testFilterPathTrimsSeparators()
    {
        $this->assertSame('foo/bar', $this->obj->filterPath('/foo/bar/'));
    }

    public function testFilterPathRejectsNonString()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->filterPath([ 'nope' ]);
    }

    public function testPathExistsFindsFile()
    {
        $matches = $this->obj->pathExists('sample.txt');
        $this->assertNotEmpty($matches);
    }

    public function testProcessMultiplePaths()
    {
        $paths = $this->obj->processMultiplePaths('sample.txt');
        $this->assertSame([ 'sample.txt' ], array_values($paths));
    }

    public function testProcessMultiplePathsThrowsOnEmpty()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->processMultiplePaths('missing-file.txt');
    }

    public function testFilterWritablePathOnDirectory()
    {
        $path = $this->obj->filterWritablePath('.', 'out.txt');
        $this->assertStringEndsWith('out.txt', $path);
    }
}

class PathScriptStub
{
    use PathScriptTrait;

    public const DIRECTORY_SEPARATORS = '/\\';
    public const DEFAULT_BASENAME = 'default.txt';

    /**
     * @var string
     */
    private $basePath;

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    public function basePath()
    {
        return $this->basePath;
    }

    public function maxDepth()
    {
        return 2;
    }

    public static function clearGlobCache()
    {
        static::$globCache = [];
    }

    protected function parseAsArray($var, $delimiter = '[\s,]+')
    {
        if (is_string($var)) {
            return preg_split('#' . $delimiter . '#', $var);
        }

        return $var;
    }
}
