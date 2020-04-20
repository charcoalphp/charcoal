<?php

namespace Charcoal\Tests\View;

// From 'charcoal-view'
use Charcoal\View\Mustache\MustacheLoader;
use Charcoal\View\Mustache\MustacheEngine;
use Charcoal\View\AbstractEngine;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class AbstractEngineTest extends AbstractTestCase
{
    /**
     * Instance of object under test
     * @var AbstractEngine $obj
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $loader = new MustacheLoader([
            'base_path' => __DIR__,
            'paths'     => [ 'Mustache/templates' ],
        ]);
        $this->obj = $this->getMockForAbstractClass(AbstractEngine::class, [
            [
                'loader' => $loader,
            ]
        ]);
    }

    /**
     * @return void
     */
    public function testLoadTemplate()
    {
        $this->assertEquals('', $this->obj->loadTemplate(''));

        $expected = file_get_contents(__DIR__.'/Mustache/templates/foo.mustache');
        $this->assertEquals($expected, $this->obj->loadTemplate('foo'));
    }

    /**
     * @return void
     */
    public function testSetDynamicTemplate()
    {
        $this->assertNull($this->obj->setDynamicTemplate('foo', 'bar'));
    }
}
