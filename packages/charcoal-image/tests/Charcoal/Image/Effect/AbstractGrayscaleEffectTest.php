<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractGrayscaleEffectTest extends \PHPUnit\Framework\TestCase
{
    public $obj;

    public function setUp()
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
