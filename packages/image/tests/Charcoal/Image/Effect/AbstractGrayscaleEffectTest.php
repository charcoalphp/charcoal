<?php

namespace Charcoal\Tests\Image\Effect;

use Charcoal\Image\Effect\AbstractGrayscaleEffect;
use Charcoal\Tests\Mock\ImageMock;

class AbstractGrayscaleEffectTest extends \PHPUnit\Framework\TestCase
{
    public $obj;

    protected function setUp(): void
    {
        $img = new ImageMock();
        $this->obj = new class () extends AbstractGrayscaleEffect {
            public function process(?array $data = null) {}
        };
        $this->obj->setImage($img);
    }

    public function testSetData(): void
    {
        $obj = $this->obj;
        $ret = $obj->setData([]);
        $this->assertSame($ret, $obj);
    }
}
