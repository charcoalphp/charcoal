<?php

namespace Charcoals\Tests\Image\Effect;


class AbstractDitherEffectTest extends \PHPUnit_Framework_Testcase
{
    public $obj;

    public function setUp()
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractDitherEffect');
        $this->obj->set_image($img);
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
        $ret = $obj->set_data(
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
        $ret = $obj->set_colors(6);
        $this->assertSame($ret, $obj);
        $this->assertEquals(6, $obj->colors());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_colors(false);
    }

    public function testSetMode()
    {
        $obj = $this->obj;
        $ret = $obj->set_mode('checks');
        $this->assertSame($ret, $obj);
        $this->assertEquals('checks', $obj->mode());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_mode('foobar');
    }
}
