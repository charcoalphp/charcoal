<?php

namespace Charcoal\Tests\View;

use PHPUnit_Framework_TestCase;

use Psr\Log\NullLogger;

use Charcoal\View\Mustache\MustacheLoader;
use Charcoal\View\Mustache\MustacheEngine;
use Charcoal\View\AbstractEngine;

/**
 *
 */
class AbstractEngineTest extends PHPUnit_Framework_TestCase
{
    /**
     * Instance of object under test
     * @var AbstractEngine $obj
     */
    public $obj;

    /**
     *
     */
    public function setUp()
    {
        $logger = new NullLogger();
        $loader = new MustacheLoader([
            'logger'=>$logger,
            'base_path'=>__DIR__,
            'paths'=>['Mustache/templates']
        ]);
        $this->obj = $this->getMockForAbstractClass(AbstractEngine::class, [[
            'logger'=>$logger,
            'loader'=>$loader
        ]]);
    }

    public function testLoadTemplate()
    {
        $this->assertEquals('', $this->obj->loadTemplate(''));

        $expected = file_get_contents(__DIR__.'/Mustache/templates/foo.mustache');
        $this->assertEquals($expected, $this->obj->loadTemplate('foo'));
    }

    public function testSetDynamicTemplate()
    {
        $this->assertNull($this->obj->setDynamicTemplate('foo', 'bar'));
    }
}
