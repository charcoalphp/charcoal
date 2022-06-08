<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractRotateEffectTest extends \PHPUnit\Framework\TestCase
{
    public $obj;

    public function setUp()
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractRotateEffect');
        $this->obj->setImage($img);
    }

    public function testDefaults()
    {
        $obj = $this->obj;

        $this->assertEquals(0, $obj->angle());
        $this->assertEquals('rgb(100%, 100%, 100%, 0)', $obj->backgroundColor());
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData(
            [
            'angle'=>42,
            'backgroundColor'=>'blue'
            ]
        );
        $this->assertSame($ret, $obj);

        $this->assertEquals(42, $obj->angle());
        $this->assertEquals('blue', $obj->backgroundColor());
    }

    public function testSetAngle()
    {
        $obj = $this->obj;
        $ret = $obj->setAngle(135);
        $this->assertSame($ret, $obj);
        $this->assertEquals(135, $obj->angle());

        $this->expectException('\InvalidArgumentException');
        $obj->setAngle('foobar');
    }

    public function testSetBackgroundColor()
    {
        $obj = $this->obj;
        $ret = $obj->setBackgroundColor('red');
        $this->assertSame($ret, $obj);
        $this->assertEquals('red', $obj->backgroundColor());

        $this->expectException('\InvalidArgumentException');
        $obj->setBackgroundColor(false);
    }
}
