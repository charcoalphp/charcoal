<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractRotateEffectTest extends \PHPUnit_Framework_Testcase
{
    public $obj;

    public function setUp()
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractRotateEffect');
        $this->obj->set_image($img);
    }

    public function testDefaults()
    {
        $obj = $this->obj;

        $this->assertEquals(0, $obj->angle());
        $this->assertEquals('rgb(100%, 100%, 100%, 0)', $obj->background_color());
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->set_data(
            [
            'angle'=>42,
            'background_color'=>'blue'
            ]
        );
        $this->assertSame($ret, $obj);

        $this->assertEquals(42, $obj->angle());
        $this->assertEquals('blue', $obj->background_color());
    }

    public function testSetAngle()
    {
        $obj = $this->obj;
        $ret = $obj->set_angle(135);
        $this->assertSame($ret, $obj);
        $this->assertEquals(135, $obj->angle());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_angle('foobar');
    }

    public function testSetBackgroundColor()
    {
        $obj = $this->obj;
        $ret = $obj->set_background_color('red');
        $this->assertSame($ret, $obj);
        $this->assertEquals('red', $obj->background_color());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_background_color(false);
    }
}
