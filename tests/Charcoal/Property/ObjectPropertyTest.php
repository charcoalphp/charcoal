<?php

namespace Charcoal\Tests\Property;

use Charcoal\Property\ObjectProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class ObjectPropertyTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    public $obj;

    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new ObjectProperty([
            'container'  => $container,
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    public function testType()
    {
        $this->assertEquals('object', $this->obj->type());
    }

    public function testAccessingObjTypeBeforeSetterThrowsException()
    {
        $this->setExpectedException('\Exception');
        $this->obj->objType();
    }

    public function testSetObjType()
    {
        $ret = $this->obj->setObjType('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->objType());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setObjType(false);
    }

    public function testSetPattern()
    {
        $ret = $this->obj->setPattern('{{foo}}');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('{{foo}}', $this->obj->pattern());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setPattern([]);
    }

    public function testSqlExtra()
    {
        $this->assertEquals('', $this->obj->sqlExtra());
    }

    // public function testSqlType()
    // {
    //     $this->obj->setObjType('charcoal/model/model');
    //     $this->assertEquals('', $this->obj->sqlType());
    // }

    public function testSqlTypeMultiple()
    {
        $this->obj->setMultiple(true);
        $this->assertEquals('TEXT', $this->obj->sqlType());
    }

    public function testParseOneWithScalarValue()
    {
        $this->assertEquals('foobar', $this->obj->parseOne('foobar'));

        $mock = $this->getMock('\Charcoal\Source\StorableInterface');
        $this->assertNull($this->obj->parseOne($mock));

        // Force ID to 'foo'.
        $mock->expects($this->any())
            ->method('id')
            ->will($this->returnValue('foo'));
        $this->assertEquals('foo', $this->obj->parseOne($mock));
    }

    public function testParseOneWithObjectWithoutIdReturnsNull()
    {
        $mock = $this->getMock('\Charcoal\Source\StorableInterface');
        $this->assertNull($this->obj->parseOne($mock));
    }

    public function testParseOneWithObjectWithIdReturnsId()
    {
        $mock = $this->getMock('\Charcoal\Source\StorableInterface');
        $mock->expects($this->any())
            ->method('id')
            ->will($this->returnValue('foo'));
        $this->assertEquals('foo', $this->obj->parseOne($mock));
    }

    public function testStorageVal()
    {
        $this->assertNull($this->obj->storageVal(''));
        $this->assertNull($this->obj->storageVal(null));
    }
}
