<?php

namespace Charcoal\Tests\Image\Effect;

use Charcoal\Image\Effect\AbstractRotateEffect;
use Charcoal\Tests\Mock\ImageMock;

class AbstractRotateEffectTest extends \PHPUnit\Framework\TestCase
{
    public $obj;

    protected function setUp(): void
    {
        $img = new ImageMock();
        $this->obj = new class () extends AbstractRotateEffect {
            public function process(?array $data = null) {}
        };
        $this->obj->setImage($img);
    }

    public function testDefaults(): void
    {
        $obj = $this->obj;

        $this->assertEquals(0, $obj->angle());
        $this->assertEquals('rgb(100%, 100%, 100%, 0)', $obj->backgroundColor());
    }

    public function testSetData(): void
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

    public function testSetAngle(): void
    {
        $obj = $this->obj;
        $ret = $obj->setAngle(135);
        $this->assertSame($ret, $obj);
        $this->assertEquals(135, $obj->angle());

        $this->expectException('\InvalidArgumentException');
        $obj->setAngle('foobar');
    }

    public function testSetBackgroundColor(): void
    {
        $obj = $this->obj;
        $ret = $obj->setBackgroundColor('red');
        $this->assertSame($ret, $obj);
        $this->assertEquals('red', $obj->backgroundColor());

        $this->expectException('\InvalidArgumentException');
        $obj->setBackgroundColor(false);
    }
}
