<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractSepiaEffectTest extends \PHPUnit\Framework\TestCase
{
    public $obj;

    public function setUp()
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractSepiaEffect');
        $this->obj->setImage($img);
    }

    public function testDefaults()
    {
        $obj = $this->obj;

        $this->assertEquals(75, $obj->threshold());
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData(
            [
            'threshold'=>100
            ]
        );
        $this->assertSame($ret, $obj);

        $this->assertEquals(100, $obj->threshold());
    }

    public function testSetThreshold()
    {
        $obj = $this->obj;

        $ret = $obj->setThreshold(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->threshold());

        $this->expectException('\InvalidArgumentException');
        $obj->setThreshold('foobar');
    }

    public function testSetThresholdMinException()
    {
        $this->expectException('\InvalidArgumentException');
        $obj = $this->obj;
        $obj->setThreshold(-1);
    }
    public function testSetThresholdMaxException()
    {
        $this->expectException('\InvalidArgumentException');
        $obj = $this->obj;
        $obj->setThreshold(256);
    }
}
