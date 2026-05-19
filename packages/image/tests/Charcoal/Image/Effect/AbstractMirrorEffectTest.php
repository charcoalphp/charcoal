<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractMirrorEffectTest extends \PHPUnit\Framework\TestCase
{
    public $obj;

    protected function setUp(): void
    {
        $img = $this->getMockForAbstractClass(\Charcoal\Image\AbstractImage::class);
        $img->method('driverType')->willReturn('imagick');
        $this->obj = $this->getMockForAbstractClass(\Charcoal\Image\Effect\AbstractMirrorEffect::class);
        $this->obj->setImage($img);
    }

    public function testDefaults(): void
    {
        $obj = $this->obj;

        $this->assertEquals(
            'y',
            $obj->axis()
        );
    }

    public function testSetData(): void
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

    public function testSetAxis(): void
    {
        $obj = $this->obj;

        $ret = $obj->setAxis('x');
        $this->assertSame($ret, $obj);
        $this->assertEquals('x', $obj->axis());

        $this->expectException('\InvalidArgumentException');
        $obj->setAxis('foobar');
    }
}
