<?php

namespace Charcoal\Tests\View\Mustache;

use PHPUnit_Framework_TestCase;

use Psr\Log\NullLogger;

use Charcoal\View\Mustache\MustacheEngine;
use Charcoal\View\Mustache\MustacheLoader;

/**
 *
 */
class MustacheEngineTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MustacheEngine
     */
    private $obj;

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
    public function testType()
    {
        $this->assertEquals('mustache', $this->obj->type());
    }

    public function testSetHelpers()
    {
        $ret = $this->obj->setHelpers([]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals([], $this->obj->helpers());
    }

    public function testRender()
    {
        $this->assertEquals('Hello Charcoal', trim($this->obj->render('foo', ['foo'=>'Charcoal'])));
    }

    public function testRenderTemplate()
    {
        $this->assertEquals('Hello World!', trim($this->obj->renderTemplate('Hello {{bar}}', ['bar'=>'World!'])));
    }
}
