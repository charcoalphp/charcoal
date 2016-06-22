<?php

namespace Charcoal\Tests\View\Mustache;

use \Charcoal\View\Mustache\MustacheEngine;
use \Charcoal\View\Mustache\MustacheLoader;

/**
 *
 */
class MustacheEngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MustacheEngine
     */
    private $obj;

    private function getLoader()
    {
        $loader = new MustacheLoader([
            'logger'=>new \Psr\Log\NullLogger()
        ]);
        $loader->addPath(__DIR__.'/templates');
        return $loader;
    }

    public function setUp()
    {
        $this->obj = new MustacheEngine([
            'logger'=>new \Psr\Log\NullLogger(),

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
        $this->obj->setLoader($this->getLoader());
        $this->assertEquals('Hello Charcoal', trim($this->obj->render('foo', ['foo'=>'Charcoal'])));
    }

    public function testRenderTemplate()
    {
        $this->obj->setLoader($this->getLoader());
        $this->assertEquals('Hello World!', trim($this->obj->renderTemplate('Hello {{bar}}', ['bar'=>'World!'])));
    }
}
