<?php

namespace Charcoal\Tests\Model;

// From 'charcoal-core'
use Charcoal\Model\ModelMetadata;

/**
 *
 */
class ModelMetadataTest extends \PHPUnit_Framework_TestCase
{

    private $obj;

    public function setUp()
    {
        $this->obj = new ModelMetadata();
    }

    public function testSetIdent()
    {
        $ret = $this->obj->setIdent('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->ident());

        $this->setExpectedException('\Exception');
        $this->obj->setIdent(false);
    }

    public function testArrayAccessGet()
    {
        $obj = $this->obj;
        $obj->foo = 'bar';

        $this->assertEquals($obj->foo, $obj['foo']);
    }

    public function testArrayAccessSet()
    {
        $obj = $this->obj;
        $obj['foo'] = 'bar';

        $this->assertEquals($obj->foo, $obj['foo']);
    }

    public function testArrayAccessUnset()
    {
        $obj = $this->obj;
        $this->assertObjectNotHasAttribute('foo', $obj);

        $obj['foo'] = 'bar';
        $this->assertObjectHasAttribute('foo', $obj);

        unset($obj['foo']);
        //$this->assertObjectNotHasAttribute('foo', $obj);
    }

    public function testMerge()
    {
        $data = [
            'foo' => 'bar',
            'bar' => 'foo'
        ];

        $obj = $this->obj;
        $obj->merge($data);

        $this->assertEquals($obj->foo, 'bar');
        $this->assertEquals($obj->bar, 'foo');
    }

    public function testMergeIsChainable()
    {
        $obj = $this->obj;
        $ret = $obj->merge([]);

        $this->assertSame($obj, $ret);
    }
}
