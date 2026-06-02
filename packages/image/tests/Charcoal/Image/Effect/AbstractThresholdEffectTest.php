<?php

namespace Charcoal\Tests\Image\Effect;

use Charcoal\Image\Effect\AbstractThresholdEffect;
use Charcoal\Tests\Mock\ImageMock;

class AbstractThresholdEffectTest extends \PHPUnit\Framework\TestCase
{
    public $obj;

    protected function setUp(): void
    {
        $img = new ImageMock();
        $this->obj = new class () extends AbstractThresholdEffect {
            public function process(?array $data = null) {}
        };
        $this->obj->setImage($img);
    }

    public function testDefaults(): void
    {
        $obj = $this->obj;

        $this->assertEquals(0.5, $obj->threshold());
    }

    public function testSetData(): void
    {
        $obj = $this->obj;
        $ret = $obj->setData(
            [
            'threshold'=>0.1
            ]
        );
        $this->assertSame($ret, $obj);

        $this->assertEquals(0.1, $obj->threshold());
    }

    public function testSetThreshold(): void
    {
        $obj = $this->obj;

        $ret = $obj->setThreshold(0.42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(0.42, $obj->threshold());

        $this->expectException('\InvalidArgumentException');
        $obj->setThreshold('foobar');
    }

    public function testSetThresholdMinException(): void
    {
        $this->expectException('\InvalidArgumentException');
        $obj = $this->obj;
        $obj->setThreshold(-1);
    }

    public function testSetThresholdMaxException(): void
    {
        $this->expectException('\InvalidArgumentException');
        $obj = $this->obj;
        $obj->setThreshold(-1);
    }
}
