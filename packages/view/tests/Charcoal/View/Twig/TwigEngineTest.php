<?php

namespace Charcoal\Tests\View\Twig;

use InvalidArgumentException;
use RuntimeException;

// From 'charcoal-view'
use Charcoal\View\Twig\TwigEngine;
use Charcoal\View\Twig\TwigLoader;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\View\ViewConfig;
use Charcoal\Tests\View\Twig\Mock\MockHelpers;

/**
 *
 */
class TwigEngineTest extends AbstractTestCase
{
    private \Charcoal\View\Twig\TwigEngine $obj;

    public function setUp(): void
    {
        $loader = new TwigLoader([
            'base_path' => __DIR__,
            'paths'     => [ 'templates' ],
        ]);
        $this->obj = new TwigEngine([
                'config'    => new ViewConfig(),
                'loader'    => $loader,
                'helpers'   => [],
                'cache'     => null,
                'debug'     => true,
            ]);
    }

    public function testType(): void
    {
        $this->assertEquals('twig', $this->obj->type());
    }

    public function testSetHelpers(): void
    {
        $ret = $this->obj->setHelpers([]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals([], $this->obj->helpers());

        $arr = [ 'foo' => 'baz' ];
        $this->obj->setHelpers($arr);
        // $this->assertArraySubsets($arr, $this->obj->helpers());
        $this->assertTrue(
            array_diff_key($arr, $this->obj->helpers()) === [] && array_diff_key($this->obj->helpers(), $arr) === []
        ); // compare structure (keys) only
        $this->assertTrue(
            array_diff_assoc($arr, $this->obj->helpers()) === [] && array_diff_assoc($this->obj->helpers(), $arr) === []
        ); // compare structure (keys) and values strictly

        $helpers = new MockHelpers();
        $this->obj->setHelpers($helpers);
        //  $this->assertArraySubsets($helpers->toArray(), $this->obj->helpers());
        $this->assertTrue(
            array_diff_key($helpers->toArray(), $this->obj->helpers()) === [] && array_diff_key($this->obj->helpers(), $helpers->toArray()) === []
        );

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setHelpers('foobar');
    }

    public function testMergeHelpers(): void
    {
        $ret = $this->obj->mergeHelpers([]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals([], $this->obj->helpers());

        $arr = [ 'foo' => 'baz' ];
        $this->obj->mergeHelpers($arr);
        // $this->assertArraySubsets($arr, $this->obj->helpers());

        $this->assertTrue(
            array_diff_key($arr, $this->obj->helpers()) === [] && array_diff_key($this->obj->helpers(), $arr) === []
        );
        $this->assertTrue(
            array_diff_assoc($arr, $this->obj->helpers()) === [] && array_diff_assoc($this->obj->helpers(), $arr) === []
        );

        $helpers = new MockHelpers();
        $this->obj->mergeHelpers($helpers);

        // $this->assertNotArraySubset($arr, $this->obj->helpers());
        // $this->assertArraySubsets($helpers->toArray(), $this->obj->helpers());
        $this->assertTrue(
            array_diff_key($helpers->toArray(), $this->obj->helpers()) === [] && array_diff_key($this->obj->helpers(), $helpers->toArray()) === []
        );

        $this->expectException(InvalidArgumentException::class);
        $this->obj->mergeHelpers('foobar');
    }

    public function testAddHelperTooLate(): void
    {
        $template = 'Hello {{ foo }}';
        $context  = [ 'foo' => 'World!' ];
        $this->obj->renderTemplate($template, $context);

        $this->expectException(RuntimeException::class);
        $this->obj->addHelper('foo', 'World');
    }

    public function testRender(): void
    {
        $this->assertEquals('Hello Charcoal', trim($this->obj->render('foo', [ 'foo' => 'Charcoal' ])));
    }

    public function testRenderTemplate(): void
    {
        $this->assertEquals('Hello World!', trim($this->obj->renderTemplate('Hello {{ foo }}', [ 'foo' => 'World!' ])));
    }
}
