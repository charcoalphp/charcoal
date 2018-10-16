<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractDitherEffectTest extends \PHPUnit\Framework\TestCase
{
    public $obj;

    public function setUp()
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $img->method('driverType')->willReturn('imagick');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractDitherEffect');
        $this->obj->setImage($img);
    }

    public function testDefaults()
    {
        $obj = $this->obj;

        $this->assertEquals(16, $obj->colors());
        $this->assertEquals('', $obj->mode());
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData(
            [
            'colors'=>8,
            'mode'=>'h6x6a'
            ]
        );
        $this->assertSame($ret, $obj);

        $this->assertEquals(8, $obj->colors());
        $this->assertEquals('h6x6a', $obj->mode());
    }

    public function testSetColors()
    {
        $obj = $this->obj;
        $ret = $obj->setColors(6);
        $this->assertSame($ret, $obj);
        $this->assertEquals(6, $obj->colors());

        $this->expectException('\InvalidArgumentException');
        $obj->setColors(false);
    }

    public function testSetMode()
    {
        $obj = $this->obj;
        $ret = $obj->setMode('checks');
        $this->assertSame($ret, $obj);
        $this->assertEquals('checks', $obj->mode());

        $this->expectException('\InvalidArgumentException');
        $obj->setMode('foobar');
    }
}
