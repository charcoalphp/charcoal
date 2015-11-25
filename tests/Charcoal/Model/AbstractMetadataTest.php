<?php

namespace Charcoal\Tests\Model;

class AbstractMetadataTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Model\AbstractMetadata');
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->set_data([
            'properties'=>[],
            'foo'=>'bar'
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals([], $obj->properties());
        $this->assertEquals('bar', $obj->foo);
    }

    public function testArrayAccessOffsetExists()
    {
        $obj = $this->obj;
        $this->assertFalse(isset($obj['x']));
    }
}
