<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractWatermarkEffectTest extends \PHPUnit_Framework_Testcase
{
    public $obj;

    public function setUp()
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractWatermarkEffect');
        $this->obj->set_image($img);
    }

    public function testDefaults()
    {
        $obj = $this->obj;

        $this->assertEquals(1.0, $obj->opacity());
        $this->assertEquals('center', $obj->gravity());
        $this->assertEquals(0, $obj->x());
        $this->assertEquals(0, $obj->y());
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->set_data(
            [
            'watermark'=>'foo/bar.png',
            'opacity'=>0.5,
            'gravity'=>'se',
            'x'=>-10,
            'y'=>20
            ]
        );
        $this->assertSame($ret, $obj);

        $this->assertEquals('foo/bar.png', $obj->watermark());
        $this->assertEquals(0.5, $obj->opacity());
        $this->assertEquals('se', $obj->gravity());
        $this->assertEquals(-10, $obj->x());
        $this->assertEquals(20, $obj->y());
    }

    public function testSetWatermark()
    {
        $obj = $this->obj;
        $ret = $obj->set_watermark('bar/baz.png');
        $this->assertSame($ret, $obj);
        $this->assertEquals('bar/baz.png', $obj->watermark());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_watermark(false);
    }

    public function testSetOpacity()
    {
        $obj = $this->obj;
        $ret = $obj->set_opacity(0.42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(0.42, $obj->opacity());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_opacity(false);
    }

    public function testSetGravity()
    {
        $obj = $this->obj;
        $ret = $obj->set_gravity('se');
        $this->assertSame($ret, $obj);
        $this->assertEquals('se', $obj->gravity());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_gravity('foobar');
    }

    public function testSetX()
    {
        $obj = $this->obj;
        $ret = $obj->set_x(15);
        $this->assertSame($ret, $obj);
        $this->assertEquals(15, $obj->x());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_x(false);
    }

    public function testSetY()
    {
        $obj = $this->obj;
        $ret = $obj->set_y(15);
        $this->assertSame($ret, $obj);
        $this->assertEquals(15, $obj->y());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_y(false);
    }
}
