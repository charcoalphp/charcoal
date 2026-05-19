<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractTintEffectTest extends \PHPUnit\Framework\TestCase
{
    public $obj;

    protected function setUp(): void
    {
        $img = $this->getMockForAbstractClass(\Charcoal\Image\AbstractImage::class);
        $this->obj = $this->getMockForAbstractClass(\Charcoal\Image\Effect\AbstractTintEffect::class);
        $this->obj->setImage($img);
    }

    public function testDefaults(): void
    {
        $obj = $this->obj;

        $this->assertEquals('rgb(0,0,0)', $obj->color());
        $this->assertEquals(0.5, $obj->opacity());
        $this->assertTrue($obj->midtone());
    }

    public function testSetData(): void
    {
        $obj = $this->obj;
        $ret = $obj->setData(
            [
            'color'=>'white',
            'opacity'=>0.42,
            'midtone'=>false
            ]
        );
        $this->assertSame($ret, $obj);

        $this->assertEquals('white', $obj->color());
        $this->assertEquals(0.42, $obj->opacity());
        $this->assertFalse($obj->midtone());
    }

    public function testSetColor(): void
    {
        $obj = $this->obj;
        $ret = $obj->setColor('#ff00ff');
        $this->assertSame($ret, $obj);
        $this->assertEquals('#ff00ff', $obj->color());

        $this->expectException('\InvalidArgumentException');
        $obj->setColor(false);
    }

    public function testSetOpacity(): void
    {
        $obj = $this->obj;
        $ret = $obj->setOpacity(0.42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(0.42, $obj->opacity());

        $this->expectException('\InvalidArgumentException');
        $obj->setOpacity(false);
    }

    public function testSetMidtone(): void
    {
        $obj = $this->obj;
        $ret = $obj->setMidtone(false);
        $this->assertSame($ret, $obj);
        $this->assertFalse($obj->midtone());
    }
}
