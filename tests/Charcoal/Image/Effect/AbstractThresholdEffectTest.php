<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractThresholdEffectTest extends \PHPUnit_Framework_Testcase
{
    public $obj;

    public function setUp()
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractThresholdEffect');
        $this->obj->set_image($img);
    }

    public function testDefaults()
    {
        $obj = $this->obj;

        $this->assertEquals(0.5, $obj->threshold());
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->set_data(
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

        $ret = $obj->set_threshold(0.42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(0.42, $obj->threshold());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_threshold('foobar');
    }

    public function testSetThresholdMinException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $obj = $this->obj;
        $obj->set_threshold(-1);
    }

    public function testSetThresholdMaxException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $obj = $this->obj;
        $obj->set_threshold(-1);
    }
}
