<?php

namespace Charcoal\Tests\Image\Effect;

use Charcoal\Image\Effect\AbstractDitherEffect;
use Charcoal\Tests\Mock\ImageMock;

class AbstractDitherEffectTest extends \PHPUnit\Framework\TestCase
{
    public $obj;

    protected function setUp(): void
    {
        $img = new ImageMock();
        $this->obj = new class () extends AbstractDitherEffect {
            public function process(?array $data = null) {}
        };
        $this->obj->setImage($img);
    }

    public function testDefaults(): void
    {
        $obj = $this->obj;

        $this->assertEquals(16, $obj->colors());
        $this->assertEquals('', $obj->mode());
    }

    public function testSetData(): void
    {
        $obj = $this->obj;
        $ret = $obj->setData(
            [
            'colors'=>8,
            'mode'=>'h6x6a'
            ]
        );
        $this->assertSame($ret, $obj);

        $this->assertEquals(8, $obj->colors());
        $this->assertEquals('h6x6a', $obj->mode());
    }

    public function testSetColors(): void
    {
        $obj = $this->obj;
        $ret = $obj->setColors(6);
        $this->assertSame($ret, $obj);
        $this->assertEquals(6, $obj->colors());

        $this->expectException('\InvalidArgumentException');
        $obj->setColors(false);
    }

    public function testSetMode(): void
    {
        $obj = $this->obj;
        $ret = $obj->setMode('checks');
        $this->assertSame($ret, $obj);
        $this->assertEquals('checks', $obj->mode());

        $this->expectException('\InvalidArgumentException');
        $obj->setMode('foobar');
    }
}
