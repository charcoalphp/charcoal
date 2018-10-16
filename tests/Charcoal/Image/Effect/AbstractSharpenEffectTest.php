<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractSharpenEffectTest extends \PHPUnit\Framework\TestCase
{
    public $obj;

    public function setUp()
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractSharpenEffect');
        $this->obj->setImage($img);
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
        $ret = $obj->setData(
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

    public function testSetAmount()
    {
        $obj = $this->obj;

        $ret = $obj->setAmount(4.2);
        $this->assertSame($ret, $obj);
        $this->assertEquals(4.2, $obj->amount());

        $this->expectException('\InvalidArgumentException');
        $obj->setAmount('foobar');
    }

    public function testSetAmountNegativeThrowsException()
    {
        $this->expectException('\InvalidArgumentException');
        $obj = $this->obj;
        $obj->setAmount(-1);
    }

    public function testSetThreshold()
    {
        $obj = $this->obj;

        $ret = $obj->setThreshold(0.42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(0.42, $obj->threshold());

        $this->expectException('\InvalidArgumentException');
        $obj->setThreshold('foobar');
    }

    public function testSetMode()
    {
        $obj = $this->obj;
        $ret = $obj->setMode('unsharp');
        $this->assertSame($ret, $obj);
        $this->assertEquals('unsharp', $obj->mode());

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
}
