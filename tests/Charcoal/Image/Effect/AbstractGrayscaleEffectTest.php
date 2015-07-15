<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractGrayscaleEffectTest extends \PHPUnit_Framework_Testcase
{
    public $obj;

    public function setUp()
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractGrayscaleEffect');
        $this->obj->set_image($img);
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->set_data([]);
        $this->assertSame($ret, $obj);
    }
}
