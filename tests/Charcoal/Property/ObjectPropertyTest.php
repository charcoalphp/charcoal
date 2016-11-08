<?php

namespace Charcoal\Tests\Property;

use \Psr\Log\NullLogger;

use \Charcoal\Property\ObjectProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class ObjectPropertyTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new ObjectProperty([
            'logger' => new NullLogger()
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
}
