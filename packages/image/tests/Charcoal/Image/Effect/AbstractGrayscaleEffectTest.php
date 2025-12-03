<?php

namespace Charcoals\Tests\Image\Effect;

use Charcoal\Image\Effect\AbstractGrayscaleEffect;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AbstractGrayscaleEffect::class)]
class AbstractGrayscaleEffectTest extends \PHPUnit\Framework\TestCase
{
    public $obj;

    protected function setUp(): void
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $img->method('driverType')->willReturn('imagick');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractGrayscaleEffect');
        $this->obj->setImage($img);
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData([]);
        $this->assertSame($ret, $obj);
    }
}
