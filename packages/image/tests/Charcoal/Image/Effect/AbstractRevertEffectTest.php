<?php

namespace Charcoal\Tests\Image\Effect;

use Charcoal\Image\Effect\AbstractRevertEffect;
use Charcoal\Tests\Mock\ImageMock;

class AbstractRevertEffectTest extends \PHPUnit\Framework\TestCase
{
    public $obj;

    protected function setUp(): void
    {
        $img = new ImageMock();
        $this->obj = new class () extends AbstractRevertEffect {
            public function process(?array $data = null) {}
        };
        $this->obj->setImage($img);
    }

    public function testDefaults(): void
    {
        $obj = $this->obj;

        $this->assertEquals('all', $obj->channel());
    }

    public function testSetData(): void
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

    public function testSetChannel(): void
    {
        $obj = $this->obj;
        $ret = $obj->setChannel('gray');
        $this->assertSame($ret, $obj);
        $this->assertEquals('gray', $obj->channel());

        $this->expectException('\InvalidArgumentException');
        $obj->setChannel('foobar');
    }
}
