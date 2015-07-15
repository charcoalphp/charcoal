<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractModulateEffectTest extends \PHPUnit_Framework_Testcase
{
    public $obj;

    public function setUp()
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractModulateEffect');
        $this->obj->set_image($img);
    }

    public function testDefaults()
    {
        $obj = $this->obj;

        $this->assertEquals(0, $obj->hue());
        $this->assertEquals(0, $obj->saturation());
        $this->assertEquals(0, $obj->luminance());
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->set_data(
            [
            'hue'=>50,
            'saturation'=>25,
            'luminance'=>-75
            ]
        );
        $this->assertSame($ret, $obj);

        $this->assertEquals(50, $obj->hue());
        $this->assertEquals(25, $obj->saturation());
        $this->assertEquals(-75, $obj->luminance());
    }

    public function testSetHue()
    {
        $obj = $this->obj;

        $ret = $obj->set_hue(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->hue());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_hue(false);
    }

    public function testSetHueMaxExeption()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = $this->obj;
        $obj->set_hue(101);
    }

    public function testSetHueMinExeption()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = $this->obj;
        $obj->set_hue(-101);
    }

    public function testSetSaturation()
    {
        $obj = $this->obj;

        $ret = $obj->set_saturation(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->saturation());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_saturation(false);
    }

    public function testSetSaturationMaxExeption()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = $this->obj;
        $obj->set_saturation(101);
    }

    public function testSetSaturationMinExeption()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = $this->obj;
        $obj->set_saturation(-101);
    }

    public function testSetLuminance()
    {
        $obj = $this->obj;

        $ret = $obj->set_luminance(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->luminance());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_luminance(false);
    }

    public function testSetLuminanceMaxExeption()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = $this->obj;
        $obj->set_luminance(101);
    }

    public function testSetLuminanceMinExeption()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = $this->obj;
        $obj->set_luminance(-101);
    }
}
