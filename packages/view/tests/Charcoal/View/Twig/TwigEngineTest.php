<?php

namespace Charcoal\Tests\View\Twig;

use InvalidArgumentException;
use RuntimeException;

// From 'charcoal-view'
use Charcoal\View\Twig\TwigEngine;
use Charcoal\View\Twig\TwigLoader;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\View\Twig\Mock\MockHelpers;

/**
 *
 */
class TwigEngineTest extends AbstractTestCase
{
    /**
     * @var TwigEngine
     */
    private $obj;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $loader = new TwigLoader([
            'base_path' => __DIR__,
            'paths'     => [ 'templates' ],
        ]);
        $this->obj = new TwigEngine([
                'config'    => false,
                'loader'    => $loader,
                'helpers'   => [],
                'cache'     => null,
                'debug'     => true,
            ]);
    }

    /**
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('twig', $this->obj->type());
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
        // $this->assertArraySubsets($arr, $this->obj->helpers());
        $this->assertTrue(
            empty(array_diff_key($arr, $this->obj->helpers())) && empty(array_diff_key($this->obj->helpers(), $arr))
        ); // compare structure (keys) only
        $this->assertTrue(
            empty(array_diff_assoc($arr, $this->obj->helpers())) && empty(array_diff_assoc($this->obj->helpers(), $arr))
        ); // compare structure (keys) and values strictly

        $helpers = new MockHelpers();
        $this->obj->setHelpers($helpers);
        //  $this->assertArraySubsets($helpers->toArray(), $this->obj->helpers());
        $this->assertTrue(
            empty(array_diff_key($helpers->toArray(), $this->obj->helpers())) && empty(array_diff_key($this->obj->helpers(), $helpers->toArray()))
        );

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
        // $this->assertArraySubsets($arr, $this->obj->helpers());

        $this->assertTrue(
            empty(array_diff_key($arr, $this->obj->helpers())) && empty(array_diff_key($this->obj->helpers(), $arr))
        );
        $this->assertTrue(
            empty(array_diff_assoc($arr, $this->obj->helpers())) && empty(array_diff_assoc($this->obj->helpers(), $arr))
        );

        $helpers = new MockHelpers();
        $this->obj->mergeHelpers($helpers);

        // $this->assertNotArraySubset($arr, $this->obj->helpers());
        // $this->assertArraySubsets($helpers->toArray(), $this->obj->helpers());
        $this->assertTrue(
            empty(array_diff_key($helpers->toArray(), $this->obj->helpers())) && empty(array_diff_key($this->obj->helpers(), $helpers->toArray()))
        );

        $this->expectException(InvalidArgumentException::class);
        $this->obj->mergeHelpers('foobar');
    }

    /**
     * @return void
     */
    public function testAddHelperTooLate()
    {
        $template = 'Hello {{ foo }}';
        $context  = [ 'foo' => 'World!' ];
        $this->obj->renderTemplate($template, $context);

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
        $this->assertEquals('Hello World!', trim($this->obj->renderTemplate('Hello {{ foo }}', [ 'foo' => 'World!' ])));
    }
}
