<?php

namespace Charcoal\Tests\View\Mustache;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-view'
use Charcoal\View\Mustache\MustacheEngine;
use Charcoal\View\Mustache\MustacheLoader;
use Charcoal\Tests\AbstractTestCase;

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
        $logger = new NullLogger();
        $loader = new MustacheLoader([
            'logger'    => $logger,
            'base_path' => __DIR__,
            'paths'     => ['templates']
        ]);
        $this->obj = new MustacheEngine([
            'logger' => $logger,
            'loader' => $loader
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
    }

    /**
     * @return void
     */
    public function testRender()
    {
        $this->assertEquals('Hello Charcoal', trim($this->obj->render('foo', ['foo'=>'Charcoal'])));
    }

    /**
     * @return void
     */
    public function testRenderTemplate()
    {
        $this->assertEquals('Hello World!', trim($this->obj->renderTemplate('Hello {{bar}}', ['bar'=>'World!'])));
    }
}
