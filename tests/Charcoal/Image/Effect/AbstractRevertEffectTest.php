<?php

namespace Charcoals\Tests\Image\Effect;


class AbstractRevertEffectTest extends \PHPUnit_Framework_Testcase
{
    public $obj;

    public function setUp()
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractRevertEffect');
        $this->obj->set_image($img);
    }

    public function testDefaults()
    {
        $obj = $this->obj;

        $this->assertEquals('all', $obj->channel());
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->set_data(
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
        $ret = $obj->set_channel('gray');
        $this->assertSame($ret, $obj);
        $this->assertEquals('gray', $obj->channel());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_channel('foobar');
    }
}
