<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractModulateEffectTest extends \PHPUnit\Framework\TestCase
{
    public $obj;

    protected function setUp(): void
    {
        $img = $this->getMockForAbstractClass(\Charcoal\Image\AbstractImage::class);
        $img->method('driverType')->willReturn('imagick');
        $this->obj = $this->getMockForAbstractClass(\Charcoal\Image\Effect\AbstractModulateEffect::class);
        $this->obj->setImage($img);
    }

    public function testDefaults(): void
    {
        $obj = $this->obj;

        $this->assertEquals(0, $obj->hue());
        $this->assertEquals(0, $obj->saturation());
        $this->assertEquals(0, $obj->luminance());
    }

    public function testSetData(): void
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

    public function testSetHue(): void
    {
        $obj = $this->obj;

        $ret = $obj->setHue(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->hue());

        $this->expectException('\InvalidArgumentException');
        $obj->setHue(false);
    }

    public function testSetHueMaxExeption(): void
    {
        $this->expectException('\InvalidArgumentException');

        $obj = $this->obj;
        $obj->setHue(101);
    }

    public function testSetHueMinExeption(): void
    {
        $this->expectException('\InvalidArgumentException');

        $obj = $this->obj;
        $obj->setHue(-101);
    }

    public function testSetSaturation(): void
    {
        $obj = $this->obj;

        $ret = $obj->setSaturation(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->saturation());

        $this->expectException('\InvalidArgumentException');
        $obj->setSaturation(false);
    }

    public function testSetSaturationMaxExeption(): void
    {
        $this->expectException('\InvalidArgumentException');

        $obj = $this->obj;
        $obj->setSaturation(101);
    }

    public function testSetSaturationMinExeption(): void
    {
        $this->expectException('\InvalidArgumentException');

        $obj = $this->obj;
        $obj->setSaturation(-101);
    }

    public function testSetLuminance(): void
    {
        $obj = $this->obj;

        $ret = $obj->setLuminance(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->luminance());

        $this->expectException('\InvalidArgumentException');
        $obj->setLuminance(false);
    }

    public function testSetLuminanceMaxExeption(): void
    {
        $this->expectException('\InvalidArgumentException');

        $obj = $this->obj;
        $obj->setLuminance(101);
    }

    public function testSetLuminanceMinExeption(): void
    {
        $this->expectException('\InvalidArgumentException');

        $obj = $this->obj;
        $obj->setLuminance(-101);
    }
}
