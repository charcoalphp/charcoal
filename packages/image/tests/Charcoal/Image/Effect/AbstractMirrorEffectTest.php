<?php

namespace Charcoal\Tests\Image\Effect;

use Charcoal\Image\Effect\AbstractMirrorEffect;
use Charcoal\Tests\Mock\ImageMock;

class AbstractMirrorEffectTest extends \PHPUnit\Framework\TestCase
{
    public $obj;

    protected function setUp(): void
    {
        $img = new ImageMock();
        $this->obj = new class () extends AbstractMirrorEffect {
            public function process(?array $data = null) {}
        };
        $this->obj->setImage($img);
    }

    public function testDefaults(): void
    {
        $obj = $this->obj;

        $this->assertEquals(
            'y',
            $obj->axis()
        );
    }

    public function testSetData(): void
    {
        $obj = $this->obj;
        $ret = $obj->setData(
            [
            'axis'=>'x'
            ]
        );
        $this->assertSame($ret, $obj);

        $this->assertEquals('x', $obj->axis());
    }

    public function testSetAxis(): void
    {
        $obj = $this->obj;

        $ret = $obj->setAxis('x');
        $this->assertSame($ret, $obj);
        $this->assertEquals('x', $obj->axis());

        $this->expectException('\InvalidArgumentException');
        $obj->setAxis('foobar');
    }
}
