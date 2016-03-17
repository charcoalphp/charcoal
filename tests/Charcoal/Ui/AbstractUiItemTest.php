<?php

namespace Charcoal\Tests\Ui;

class AbstractUiTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Ui\AbstractUiItem');
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
    }

    public function testNoTemplateReturnsType()
    {
        $ret = $this->obj->setType('foobar/baz');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar/baz', $this->obj->template());
    }
}
