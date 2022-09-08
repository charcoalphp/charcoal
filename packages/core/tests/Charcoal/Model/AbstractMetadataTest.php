<?php

namespace Charcoal\Tests\Model;

// From 'charcoal-core'
use Charcoal\Model\AbstractMetadata;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class AbstractMetadataTest extends AbstractTestCase
{
    /**
     * @var AbstractMetadata
     */
    public $obj;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->obj = $this->getMockForAbstractClass(AbstractMetadata::class);
    }

    /**
     * @return void
     */
    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->merge([
            'properties'=>[],
            'foo'=>'bar'
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals([], $obj->properties());
        $this->assertEquals('bar', $obj->foo);
    }

    /**
     * @return void
     */
    public function testArrayAccessOffsetExists()
    {
        $obj = $this->obj;
        $this->assertFalse(isset($obj['x']));
    }
}
