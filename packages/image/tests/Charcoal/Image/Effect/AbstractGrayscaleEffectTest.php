<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractGrayscaleEffectTest extends \PHPUnit\Framework\TestCase
{
    public $obj;

    protected function setUp(): void
    {
        $img = $this->getMockForAbstractClass(\Charcoal\Image\AbstractImage::class);
        $img->method('driverType')->willReturn('imagick');
        $this->obj = $this->getMockForAbstractClass(\Charcoal\Image\Effect\AbstractGrayscaleEffect::class);
        $this->obj->setImage($img);
    }

    public function testSetData(): void
    {
        $obj = $this->obj;
        $ret = $obj->setData([]);
        $this->assertSame($ret, $obj);
    }
}
