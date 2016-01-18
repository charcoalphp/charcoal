<?php

namespace Charcoal\Tests\Core;

use \Charcoal\Core\CoreFactory as CoreFactory;

class IndexableTraitTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public static function setUpBeforeClass()
    {
        include 'IndexableClass.php';
    }

    public function setUp()
    {
        $this->obj = new IndexableClass();
    }

    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf('\Charcoal\Tests\Core\IndexableClass', $obj);
    }

    public function testSetId()
    {
        $obj = $this->obj;
        $ret = $obj->setId(1);
        $this->assertSame($ret, $obj);
        $this->assertEquals(1, $obj->id());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setId([1, 2, 3]);
    }

    public function testSetKey()
    {
        $obj = $this->obj;
        $this->assertEquals('id', $obj->key());

        $ret = $obj->setKey('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->key());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setKey([1, 2, 3]);
    }

    public function testSetInvalidKeyIdThrowsException()
    {
        $obj = $this->obj;
        $obj->setKey('foobar');

        $this->setExpectedException('\Exception');
        $obj->id();
    }

    public function testSetInvalidKeySetIdThrowsException()
    {
        $obj = $this->obj;
        $obj->setKey('foobar');

        $this->setExpectedException('\Exception');
        $obj->setId(1);
    }

    public function testSetIdWithCustomKey()
    {
        $obj = $this->obj;

        $obj->setKey('foo');
        $obj->setId('bar');
        $this->assertEquals('bar', $obj->id());
        $this->assertEquals('bar', $obj->foo());
    }
}
