<?php

namespace Charcoal\Tests\Ui;

use Pimple\Container;

class AbstractUiTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $container = new Container;
        $container['translator'] = $GLOBALS['translator'];

        $this->obj = $this->getMockForAbstractClass('\Charcoal\Ui\AbstractUiItem', [[
            'container' => $container
        ]]);
    }

    public function testSetType()
    {
        $ret = $this->obj->setType('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->type());
    }

    public function testSetTemplate()
    {
        $ret = $this->obj->setTemplate('foo/bar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo/bar', $this->obj->template());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setTemplate(false);
    }

    public function testNoTemplateReturnsType()
    {
        $ret = $this->obj->setType('foobar/baz');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar/baz', $this->obj->template());
    }

    public function testSetTitle()
    {
        $ret = $this->obj->setTitle('Hello');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Hello', (string)$this->obj->title());
    }

    public function testSetSubtitle()
    {
        $ret = $this->obj->setSubtitle('Hello');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Hello', (string)$this->obj->subtitle());
    }

    public function testSetDescription()
    {
        $ret = $this->obj->setDescription('Hello');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Hello', (string)$this->obj->description());
    }

    public function testSetNotes()
    {
        $ret = $this->obj->setNotes('Hello');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Hello', (string)$this->obj->notes());
    }

    public function testShowTitle()
    {
        $this->assertFalse($this->obj->showTitle());
        $this->obj->setTitle('Foo');
        $this->assertTrue($this->obj->showTitle());
        $ret = $this->obj->setShowTitle(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->showTitle());
    }

    public function testShowSubtitle()
    {
        $this->assertFalse($this->obj->showSubtitle());
        $this->obj->setSubtitle('Foo');
        $this->assertTrue($this->obj->showSubtitle());
        $ret = $this->obj->setShowSubtitle(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->showSubtitle());
    }

    public function testShowDescription()
    {
        $this->assertFalse($this->obj->showDescription());
        $this->obj->setDescription('Foo');
        $this->assertTrue($this->obj->showDescription());
        $ret = $this->obj->setShowDescription(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->showDescription());
    }

    public function testShowNotes()
    {
        $this->assertFalse($this->obj->showNotes());
        $this->obj->setNotes('Foo');
        $this->assertTrue($this->obj->showNotes());
        $ret = $this->obj->setShowNotes(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->showNotes());
    }
}
