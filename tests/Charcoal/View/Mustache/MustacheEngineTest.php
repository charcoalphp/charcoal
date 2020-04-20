<?php

namespace Charcoal\Tests\View\Mustache;

use InvalidArgumentException;
use RuntimeException;

// From 'charcoal-view'
use Charcoal\View\Mustache\MustacheEngine;
use Charcoal\View\Mustache\MustacheLoader;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\View\Mustache\Mock\MockHelpers;

/**
 *
 */
class MustacheEngineTest extends AbstractTestCase
{
    /**
     * @var MustacheEngine
     */
    private $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $loader = new MustacheLoader([
            'base_path' => __DIR__,
            'paths'     => [ 'templates' ],
        ]);
        $this->obj = new MustacheEngine([
            'loader' => $loader,
            'cache'  => false,
        ]);
    }

    /**
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('mustache', $this->obj->type());
    }

    /**
     * @return void
     */
    public function testSetHelpers()
    {
        $ret = $this->obj->setHelpers([]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals([], $this->obj->helpers());

        $arr = [ 'foo' => 'baz' ];
        $this->obj->setHelpers($arr);
        $this->assertArraySubset($arr, $this->obj->helpers());

        $helpers = new MockHelpers();
        $this->obj->setHelpers($helpers);
        $this->assertArraySubsets($helpers->toArray(), $this->obj->helpers());

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setHelpers('foobar');
    }

    /**
     * @return void
     */
    public function testMergeHelpers()
    {
        $ret = $this->obj->mergeHelpers([]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals([], $this->obj->helpers());

        $arr = [ 'foo' => 'baz' ];
        $this->obj->mergeHelpers($arr);
        $this->assertArraySubset($arr, $this->obj->helpers());

        $helpers = new MockHelpers();
        $this->obj->mergeHelpers($helpers);

        $this->assertNotArraySubset($arr, $this->obj->helpers());
        $this->assertArraySubsets($helpers->toArray(), $this->obj->helpers());

        $this->expectException(InvalidArgumentException::class);
        $this->obj->mergeHelpers('foobar');
    }

    /**
     * @return void
     */
    public function testAddHelperTooLate()
    {
        $this->obj->renderTemplate('Hello, {{foo}}!', []);

        $this->expectException(RuntimeException::class);
        $this->obj->addHelper('foo', 'World');
    }

    /**
     * @return void
     */
    public function testRender()
    {
        $this->assertEquals('Hello Charcoal', trim($this->obj->render('foo', [ 'foo' => 'Charcoal' ])));
    }

    /**
     * @return void
     */
    public function testRenderTemplate()
    {
        $this->assertEquals('Hello World!', trim($this->obj->renderTemplate('Hello {{bar}}', [ 'bar' => 'World!' ])));
    }
}
