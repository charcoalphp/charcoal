<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractThresholdEffectTest extends \PHPUnit\Framework\TestCase
{
    public $obj;

    public function setUp()
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractThresholdEffect');
        $this->obj->setImage($img);
    }

    public function testDefaults()
    {
        $obj = $this->obj;

        $this->assertEquals(0.5, $obj->threshold());
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData(
            [
            'threshold'=>0.1
            ]
        );
        $this->assertSame($ret, $obj);

        $this->assertEquals(0.1, $obj->threshold());
    }

    public function testSetThreshold()
    {
        $obj = $this->obj;

        $ret = $obj->setThreshold(0.42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(0.42, $obj->threshold());

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
        $obj->setThreshold(-1);
    }
}
