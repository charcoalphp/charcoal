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
    /**
     * @var ModelMetadata
     */
    private $obj;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->obj = new ModelMetadata();
    }

    /**
     * @return void
     */
    public function testSetIdent()
    {
        $ret = $this->obj->setIdent('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->ident());

        $this->expectException(Exception::class);
        $this->obj->setIdent(false);
    }

    /**
     * @return void
     */
    public function testArrayAccessGet()
    {
        $obj = $this->obj;
        $obj->foo = 'bar';

        $this->assertEquals($obj->foo, $obj['foo']);
    }

    /**
     * @return void
     */
    public function testArrayAccessSet()
    {
        $obj = $this->obj;
        $obj['foo'] = 'bar';

        $this->assertEquals($obj->foo, $obj['foo']);
    }

    /**
     * @return void
     */
    public function testArrayAccessUnset()
    {
        $obj = $this->obj;
        $this->assertObjectNotHasAttribute('foo', $obj);

        $obj['foo'] = 'bar';
        $this->assertObjectHasAttribute('foo', $obj);

        unset($obj['foo']);
        //$this->assertObjectNotHasAttribute('foo', $obj);
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function testMergeIsChainable()
    {
        $obj = $this->obj;
        $ret = $obj->merge([]);

        $this->assertSame($obj, $ret);
    }
}
