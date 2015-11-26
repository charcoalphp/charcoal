<?php

namespace Charcoal\Tests\Core;

/**
 *
 */
class MapFactoryTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    /**
     *
     */
    public function setUp()
    {
        $this->obj = new \Charcoal\Factory\MapFactory();
    }

    public function testAddClass()
    {
        $this->assertEquals([], $this->obj->map());
        $ret = $this->obj->add_class('foo', '\Charcoal\Factory\MapFactory');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['foo'=>'\Charcoal\Factory\MapFactory'], $this->obj->map());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->add_class('foo', 'foobar');
    }

    public function testAddClassTypeNotStringThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->add_class(false, 'foo');
    }

    public function testCreate()
    {
        $this->obj->add_class('foo', '\Charcoal\Factory\MapFactory');
        $ret = $this->obj->create('foo');
        $this->assertInstanceOf('\Charcoal\Factory\MapFactory', $ret);


    }
}
