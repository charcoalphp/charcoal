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

    protected function setUp(): void
    {
        $this->obj = $this->getMockForAbstractClass(AbstractMetadata::class);
    }

    public function testSetData(): void
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

    public function testArrayAccessOffsetExists(): void
    {
        $obj = $this->obj;
        $this->assertFalse(isset($obj['x']));
    }
}
