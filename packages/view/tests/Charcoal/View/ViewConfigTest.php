<?php

declare(strict_types=1);

namespace Charcoal\Tests\View\Mustache;

// From 'charcoal-view'
use Charcoal\View\ViewConfig;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class ViewConfigTest extends AbstractTestCase
{
    /**
     * @var MustacheEngine
     */
    private \Charcoal\View\ViewConfig $obj;

    public function setUp(): void
    {
        $this->obj = new ViewConfig();
    }

    public function testDefaults(): void
    {
        $this->assertEquals('.', $this->obj->separator());
        $this->assertEquals([], $this->obj['paths']);
        $this->assertEquals([ 'cache' => '../cache/mustache' ], $this->obj['engines.mustache']);
        $this->assertEquals([], $this->obj['engines.php']);
        $this->assertEquals([], $this->obj['engines.php-mustache']);
        $this->assertEquals([ 'cache' => '../cache/twig' ], $this->obj['engines.twig']);
        $this->assertEquals('mustache', $this->obj['default_engine']);
    }

    public function testSetPaths(): void
    {
        $ret = $this->obj->setPaths(['foo', 'bar']);
        $this->assertSame($ret, $this->obj);

        $this->assertEquals(['foo', 'bar'], $this->obj->paths());
    }

    public function testSetEngines(): void
    {
        $ret = $this->obj->setEngines([ 'foo' => [] ]);
        $this->assertSame($ret, $this->obj);

        $this->assertEquals([ 'foo' => [] ], $this->obj->engines());
    }

    public function testEngine(): void
    {
        $this->assertEquals([ 'cache' => '../cache/mustache' ], $this->obj->engine('mustache'));

        $this->obj->addEngine('mustache', [ 'foo' => 'bar' ]);
        $this->assertEquals([ 'foo' => 'bar' ], $this->obj->engine('mustache'));
    }

    public function testEngineDefaultEngine(): void
    {
        $this->obj->addEngine('mustache', [ 'foo' => 'bar' ]);
        $this->assertEquals([ 'foo' => 'bar' ], $this->obj->engine());
    }

    public function testEngineInvalid(): void
    {
        $this->expectException('\InvalidArgumentException');
        $this->obj->engine('foobar');
    }

    public function testSetDefaultEngine(): void
    {
        $ret = $this->obj->setDefaultEngine('php');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('php', $this->obj->defaultEngine());
    }
}
