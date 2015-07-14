<?php

namespace Charcoals\Tests\Image\Effect;


class AbstractBlurEffectTest extends \PHPUnit_Framework_Testcase
{
    public $obj;

    public function setUp()
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractBlurEffect');
        $this->obj->set_image($img);
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
        $ret = $obj->set_data(
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

        $ret = $obj->set_radius(5.6);
        $this->assertSame($ret, $obj);
        $this->assertEquals(5.6, $obj->radius());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_radius(false);
    }

    public function testSetRadiusNegativeThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $obj = $this->obj;
        $obj->set_radius(-1);
    }

    public function testSetSigma()
    {
        $obj = $this->obj;

        $ret = $obj->set_sigma(5.6);
        $this->assertSame($ret, $obj);
        $this->assertEquals(5.6, $obj->sigma());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_sigma(false);
    }

    public function testSetSigmaNegativeThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $obj = $this->obj;
        $obj->set_sigma(-1);
    }

    public function testSetMode()
    {
        $obj = $this->obj;
        $ret = $obj->set_mode('radial');
        $this->assertSame($ret, $obj);
        $this->assertEquals('radial', $obj->mode());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_mode('foobar');
    }

    public function testSetChannel()
    {
        $obj = $this->obj;
        $ret = $obj->set_channel('alpha');
        $this->assertSame($ret, $obj);
        $this->assertEquals('alpha', $obj->channel());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_channel('foobar');
    }

    public function testSetAngle()
    {
        $obj = $this->obj;
        $ret = $obj->set_angle(45);
        $this->assertSame($ret, $obj);
        $this->assertEquals(45, $obj->angle());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_angle(false);
    }
}
