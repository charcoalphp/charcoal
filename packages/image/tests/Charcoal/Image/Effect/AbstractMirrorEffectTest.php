<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractMirrorEffectTest extends \PHPUnit\Framework\TestCase
{
    public $obj;

    public function setUp()
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $img->method('driverType')->willReturn('imagick');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractMirrorEffect');
        $this->obj->setImage($img);
    }

    public function testDefaults()
    {
        $obj = $this->obj;

        $this->assertEquals(
            'y',
            $obj->axis()
        );
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData(
            [
            'axis'=>'x'
            ]
        );
        $this->assertSame($ret, $obj);

        $this->assertEquals('x', $obj->axis());
    }

    public function testSetAxis()
    {
        $obj = $this->obj;

        $ret = $obj->setAxis('x');
        $this->assertSame($ret, $obj);
        $this->assertEquals('x', $obj->axis());

        $this->expectException('\InvalidArgumentException');
        $obj->setAxis('foobar');
    }
}
