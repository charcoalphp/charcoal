<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractBlurEffectTest extends \PHPUnit\Framework\TestCase
{
    public $obj;

    public function setUp()
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $img->method('driverType')->willReturn('imagick');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractBlurEffect');
        $this->obj->setImage($img);
    }

    public function testDefaults()
    {
        $obj = $this->obj;

        $this->assertEquals(0, $obj->radius());
        $this->assertEquals(1, $obj->sigma());
        $this->assertEquals('standard', $obj->mode());
        $this->assertEquals('all', $obj->channel());
        $this->assertEquals(0, $obj->angle());
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData(
            [
            'radius'=>8,
            'sigma'=>12,
            'mode'=>'gaussian',
            'channel'=>'blue',
            'angle'=>40
            ]
        );
        $this->assertSame($ret, $obj);

        $this->assertEquals(8, $obj->radius());
        $this->assertEquals(12, $obj->sigma());
        $this->assertEquals('gaussian', $obj->mode());
        $this->assertEquals('blue', $obj->channel());
        $this->assertEquals(40, $obj->angle());
    }

    public function testSetRadius()
    {
        $obj = $this->obj;

        $ret = $obj->setRadius(5.6);
        $this->assertSame($ret, $obj);
        $this->assertEquals(5.6, $obj->radius());

        $this->expectException('\InvalidArgumentException');
        $obj->setRadius(false);
    }

    public function testSetRadiusNegativeThrowsException()
    {
        $this->expectException('\InvalidArgumentException');
        $obj = $this->obj;
        $obj->setRadius(-1);
    }

    public function testSetSigma()
    {
        $obj = $this->obj;

        $ret = $obj->setSigma(5.6);
        $this->assertSame($ret, $obj);
        $this->assertEquals(5.6, $obj->sigma());

        $this->expectException('\InvalidArgumentException');
        $obj->setSigma(false);
    }

    public function testSetSigmaNegativeThrowsException()
    {
        $this->expectException('\InvalidArgumentException');
        $obj = $this->obj;
        $obj->setSigma(-1);
    }

    public function testSetMode()
    {
        $obj = $this->obj;
        $ret = $obj->setMode('radial');
        $this->assertSame($ret, $obj);
        $this->assertEquals('radial', $obj->mode());

        $this->expectException('\InvalidArgumentException');
        $obj->setMode('foobar');
    }

    public function testSetChannel()
    {
        $obj = $this->obj;
        $ret = $obj->setChannel('alpha');
        $this->assertSame($ret, $obj);
        $this->assertEquals('alpha', $obj->channel());

        $this->expectException('\InvalidArgumentException');
        $obj->setChannel('foobar');
    }

    public function testSetAngle()
    {
        $obj = $this->obj;
        $ret = $obj->setAngle(45);
        $this->assertSame($ret, $obj);
        $this->assertEquals(45, $obj->angle());

        $this->expectException('\InvalidArgumentException');
        $obj->setAngle(false);
    }
}
