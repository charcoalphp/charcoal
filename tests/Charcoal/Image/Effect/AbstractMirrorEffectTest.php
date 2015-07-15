<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractMirrorEffectTest extends \PHPUnit_Framework_Testcase
{
    public $obj;

    public function setUp()
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractMirrorEffect');
        $this->obj->set_image($img);
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
        $ret = $obj->set_data(
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

        $ret = $obj->set_axis('x');
        $this->assertSame($ret, $obj);
        $this->assertEquals('x', $obj->axis());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_axis('foobar');
    }
}
