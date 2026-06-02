<?php

namespace Charcoal\Tests\Model;

use Exception;

// From 'charcoal-core'
use Charcoal\Model\ModelMetadata;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class ModelMetadataTest extends AbstractTestCase
{
    private \Charcoal\Model\ModelMetadata $obj;

    protected function setUp(): void
    {
        $this->obj = new ModelMetadata();
    }

    public function testSetIdent(): void
    {
        $ret = $this->obj->setIdent('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->ident());

        $this->expectException(Exception::class);
        $this->obj->setIdent(false);
    }

    public function testArrayAccessGet(): void
    {
        $obj = $this->obj;
        $obj->foo = 'bar';

        $this->assertEquals($obj->foo, $obj['foo']);
    }

    public function testArrayAccessSet(): void
    {
        $obj = $this->obj;
        $obj['foo'] = 'bar';

        $this->assertEquals($obj->foo, $obj['foo']);
    }

    public function testArrayAccessUnset(): void
    {
        $obj = $this->obj;
        $this->assertFalse(property_exists($obj, 'foo'));

        $obj['foo'] = 'bar';
        $this->assertTrue(property_exists($obj, 'foo'));

        unset($obj['foo']);
        //$this->assertObjectNotHasAttribute('foo', $obj);
    }

    public function testMerge(): void
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

    public function testMergeIsChainable(): void
    {
        $obj = $this->obj;
        $ret = $obj->merge([]);

        $this->assertSame($obj, $ret);
    }
}
