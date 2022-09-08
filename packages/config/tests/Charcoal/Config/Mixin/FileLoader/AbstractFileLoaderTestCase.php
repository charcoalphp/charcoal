<?php

namespace Charcoal\Tests\Config\Mixin\FileLoader;

// From 'charcoal-config'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\FixturesTrait;
use Charcoal\Tests\Config\Mock\FileLoader;

/**
 * Base FileAwareTrait Test
 */
abstract class AbstractFileLoaderTestCase extends AbstractTestCase
{
    use FixturesTrait;

    /**
     * @var FileLoader
     */
    public $obj;

    /**
     * Create a FileLoader instance.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->obj = $this->createLoader();
    }

    /**
     * Create a FileLoader instance.
     *
     * @return FileLoader
     */
    public function createLoader()
    {
        return new FileLoader();
    }
}
