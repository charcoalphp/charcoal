<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractRevertEffectTest extends \PHPUnit\Framework\TestCase
{
    public $obj;

    public function setUp()
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractRevertEffect');
        $this->obj->setImage($img);
    }

    public function testDefaults()
    {
        $obj = $this->obj;

        $this->assertEquals('all', $obj->channel());
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData(
            [
            'channel'=>'green'
            ]
        );
        $this->assertSame($ret, $obj);

        $this->assertEquals('green', $obj->channel());
    }

    public function testSetChannel()
    {
        $obj = $this->obj;
        $ret = $obj->setChannel('gray');
        $this->assertSame($ret, $obj);
        $this->assertEquals('gray', $obj->channel());

        $this->expectException('\InvalidArgumentException');
        $obj->setChannel('foobar');
    }
}
