<?php

namespace Charcoals\Tests\Image\Effect;


class AbstractSepiaEffectTest extends \PHPUnit_Framework_Testcase
{
    public $obj;

    public function setUp()
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractSepiaEffect');
        $this->obj->set_image($img);
    }

    public function testDefaults()
    {
        $obj = $this->obj;

        $this->assertEquals(75, $obj->threshold());
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->set_data(
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

        $ret = $obj->set_threshold(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->threshold());

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
        $obj->set_threshold(256);
    }
}
