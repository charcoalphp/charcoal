<?php

namespace Charcoal\Tests\Image\Effect;

use Charcoal\Image\Effect\AbstractResizeEffect;
use Charcoal\Tests\Mock\ImageMock;

class AbstractResizeEffectTest extends \PHPUnit\Framework\TestCase
{
    public $obj;

    protected function setUp(): void
    {
        $img = new ImageMock();
        $this->obj = new class () extends AbstractResizeEffect {
            protected function doResize($width, $height, $bestFit = false) {}
        };
        $this->obj->setImage($img);
    }

    public function testDefaults(): void
    {
        $obj = $this->obj;

        $this->assertEquals('auto', $obj->mode());
        $this->assertEquals(null, $obj->size());
        $this->assertEquals(0, $obj->width());
        $this->assertEquals(0, $obj->height());
        $this->assertEquals('center', $obj->gravity());
        $this->assertEquals('rgba(100%, 100%, 100%, 0)', $obj->backgroundColor());
        $this->assertFalse($obj->adaptive());
    }

    public function testSetData(): void
    {
        $obj = $this->obj;
        $ret = $obj->setData([
            'mode'             => 'exact',
            'size'             => '50%',
            'width'            => 100,
            'height'           => 50,
            'gravity'          => 'e',
            'background_color' => 'red',
            'adaptive'         => true
        ]);
        $this->assertSame($ret, $obj);

        $this->assertEquals('exact', $obj->mode());
        $this->assertEquals('50%', $obj->size());
        $this->assertEquals(100, $obj->width());
        $this->assertEquals(50, $obj->height());
        $this->assertEquals('e', $obj->gravity());
        $this->assertEquals('red', $obj->backgroundColor());
        $this->assertTrue($obj->adaptive());
    }

    public function testSetMode(): void
    {
        $obj = $this->obj;
        $ret = $obj->setMode('width');
        $this->assertSame($ret, $obj);
        $this->assertEquals('width', $obj->mode());

        $this->expectException('\InvalidArgumentException');
        $obj->setMode('foobar');
    }

    public function testSetSize(): void
    {
        $obj = $this->obj;

        $ret = $obj->setSize('50%');
        $this->assertSame($ret, $obj);

        $this->assertEquals('50%', $obj->size());

        $obj->setSize(400);
        $this->assertEquals(400, $obj->size());

        $obj->setSize(null);
        $this->assertEquals(null, $obj->size());
    }

    public function testSetSizeException(): void
    {
        $obj = $this->obj;

        $this->expectException('\InvalidArgumentException');
        $obj->setSize(-1);

        $this->expectException('\InvalidArgumentException');
        $obj->setSize([ 'foo', 'bar' ]);
    }

    public function testSetWidth(): void
    {
        $obj = $this->obj;
        $ret = $obj->setWidth(400);
        $this->assertSame($ret, $obj);
        $this->assertEquals(400, $obj->width());
    }

    public function testSetWidthNegativeException(): void
    {
        $obj = $this->obj;
        $this->expectException('\InvalidArgumentException');
        $obj->setWidth(-1);
    }

    public function testSetHeight(): void
    {
        $obj = $this->obj;
        $ret = $obj->setHeight(400);
        $this->assertSame($ret, $obj);
        $this->assertEquals(400, $obj->height());
    }

    public function testSetHeightNegativeException(): void
    {
        $obj = $this->obj;
        $this->expectException('\InvalidArgumentException');
        $obj->setHeight(-1);
    }

    public function testSetGravity(): void
    {
        $obj = $this->obj;
        $ret = $obj->setGravity('nw');
        $this->assertSame($ret, $obj);
        $this->assertEquals('nw', $obj->gravity());

        $this->expectException('\InvalidArgumentException');
        $obj->setGravity('foobar');
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

    public function testSetAdaptive(): void
    {
        $obj = $this->obj;
        $ret = $obj->setAdaptive(true);
        $this->assertSame($ret, $obj);
        $this->assertTrue($obj->adaptive());
    }

    public function testAutoMode(): void
    {
        $obj = $this->obj;
        $obj->setMode('auto');

        $obj->setWidth(100);
        $obj->setHeight(100);
        $this->assertEquals('exact', $obj->autoMode());

        $obj->setWidth(100);
        $obj->setHeight(0);
        $this->assertEquals('width', $obj->autoMode());

        $obj->setWidth(0);
        $obj->setHeight(100);
        $this->assertEquals('height', $obj->autoMode());

        $obj->setWidth(0);
        $obj->setHeight(0);
        $this->assertEquals('none', $obj->autoMode());
    }

    public function testProcessExactParametersException(): void
    {
        $obj = $this->obj;
        $obj->setMode('exact');
        $this->expectException('\Exception');
        $obj->process();
    }

    public function testProcessWidthParameterException(): void
    {
        $obj = $this->obj;
        $obj->setMode('width');
        $this->expectException('\Exception');
        $obj->process();
    }

    public function testProcessHeightParameterException(): void
    {
        $obj = $this->obj;
        $obj->setMode('height');
        $this->expectException('\Exception');
        $obj->process();
    }

    public function testProcessBestFitParameterException(): void
    {
        $obj = $this->obj;
        $obj->setMode('best_fit');
        $this->expectException('\Exception');
        $obj->process();
    }

    public function testProcessCropException(): void
    {
        $obj = $this->obj;
        $obj->setMode('crop');
        $this->expectException('\Exception');
        $obj->process();
    }

    public function testProcessFillException(): void
    {
        $obj = $this->obj;
        $obj->setMode('fill');
        $this->expectException('\Exception');
        $obj->process();
    }
}
