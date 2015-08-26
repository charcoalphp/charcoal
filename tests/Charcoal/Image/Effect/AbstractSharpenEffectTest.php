<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractSharpenEffectTest extends \PHPUnit_Framework_Testcase
{
    public $obj;

    public function setUp()
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractSharpenEffect');
        $this->obj->set_image($img);
    }

    public function testDefaults()
    {
        $obj = $this->obj;

        $this->assertEquals(0, $obj->radius());
        $this->assertEquals(1, $obj->sigma());
        $this->assertEquals(1, $obj->amount());
        $this->assertEquals(0.05, $obj->threshold());
        $this->assertEquals('standard', $obj->mode());
        $this->assertEquals('all', $obj->channel());
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->set_data(
            [
            'radius'=>8,
            'sigma'=>12,
            'amount'=>2.0,
            'threshold'=>0.1,
            'mode'=>'unsharp',
            'channel'=>'blue'
            ]
        );
        $this->assertSame($ret, $obj);

        $this->assertEquals(8, $obj->radius());
        $this->assertEquals(12, $obj->sigma());
        $this->assertEquals(2.0, $obj->amount());
        $this->assertEquals(0.1, $obj->threshold());
        $this->assertEquals('unsharp', $obj->mode());
        $this->assertEquals('blue', $obj->channel());
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

    public function testSetAmount()
    {
        $obj = $this->obj;

        $ret = $obj->set_amount(4.2);
        $this->assertSame($ret, $obj);
        $this->assertEquals(4.2, $obj->amount());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_amount('foobar');
    }

     public function testSetAmountNegativeThrowsException()
     {
        $this->setExpectedException('\InvalidArgumentException');
        $obj = $this->obj;
        $obj->set_amount(-1);
     }

        public function testSetThreshold()
        {
            $obj = $this->obj;

            $ret = $obj->set_threshold(0.42);
            $this->assertSame($ret, $obj);
            $this->assertEquals(0.42, $obj->threshold());

            $this->setExpectedException('\InvalidArgumentException');
            $obj->set_threshold('foobar');
        }

        public function testSetMode()
        {
            $obj = $this->obj;
            $ret = $obj->set_mode('unsharp');
            $this->assertSame($ret, $obj);
            $this->assertEquals('unsharp', $obj->mode());

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
}
