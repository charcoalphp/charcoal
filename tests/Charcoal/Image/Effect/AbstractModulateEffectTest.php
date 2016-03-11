<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractModulateEffectTest extends \PHPUnit_Framework_Testcase
{
    public $obj;

    public function setUp()
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $img->method('driverType')->willReturn('imagick');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractModulateEffect');
        $this->obj->setImage($img);
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
        $ret = $obj->setData(
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

        $ret = $obj->setHue(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->hue());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setHue(false);
    }

    public function testSetHueMaxExeption()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = $this->obj;
        $obj->setHue(101);
    }

    public function testSetHueMinExeption()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = $this->obj;
        $obj->setHue(-101);
    }

    public function testSetSaturation()
    {
        $obj = $this->obj;

        $ret = $obj->setSaturation(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->saturation());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setSaturation(false);
    }

    public function testSetSaturationMaxExeption()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = $this->obj;
        $obj->setSaturation(101);
    }

    public function testSetSaturationMinExeption()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = $this->obj;
        $obj->setSaturation(-101);
    }

    public function testSetLuminance()
    {
        $obj = $this->obj;

        $ret = $obj->setLuminance(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->luminance());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setLuminance(false);
    }

    public function testSetLuminanceMaxExeption()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = $this->obj;
        $obj->setLuminance(101);
    }

    public function testSetLuminanceMinExeption()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = $this->obj;
        $obj->setLuminance(-101);
    }
}
