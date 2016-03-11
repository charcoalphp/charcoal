<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractResizeEffectTest extends \PHPUnit_Framework_Testcase
{
    public $obj;

    public function setUp()
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $img->method('driverType')->willReturn('imagick');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractResizeEffect');
        $this->obj->setImage($img);
    }

    public function testDefaults()
    {
        $obj = $this->obj;

        $this->assertEquals('auto', $obj->mode());
        $this->assertEquals(0, $obj->width());
        $this->assertEquals(0, $obj->height());
        $this->assertEquals('center', $obj->gravity());
        $this->assertEquals('rgba(100%, 100%, 100%, 0)', $obj->backgroundColor());
        $this->assertFalse($obj->adaptive());
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData(
            [
            'mode'=>'exact',
            'width'=>100,
            'height'=>50,
            'gravity'=>'e',
            'backgroundColor'=>'red',
            'adaptive'=>true
            ]
        );
        $this->assertSame($ret, $obj);

        $this->assertEquals('exact', $obj->mode());
        $this->assertEquals(100, $obj->width());
        $this->assertEquals(50, $obj->height());
        $this->assertEquals('e', $obj->gravity());
        $this->assertEquals('red', $obj->backgroundColor());
        $this->assertTrue($obj->adaptive());
    }

    public function testSetMode()
    {
        $obj = $this->obj;
        $ret = $obj->setMode('width');
        $this->assertSame($ret, $obj);
        $this->assertEquals('width', $obj->mode());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMode('foobar');
    }

    public function testSetWidth()
    {
        $obj = $this->obj;
        $ret = $obj->setWidth(400);
        $this->assertSame($ret, $obj);
        $this->assertEquals(400, $obj->width());
    }

    public function testSetWidthNegativeException()
    {
        $obj = $this->obj;
        $this->setExpectedException('\InvalidArgumentException');
        $obj->setWidth(-1);
    }

    public function testSetHeight()
    {
        $obj = $this->obj;
        $ret = $obj->setHeight(400);
        $this->assertSame($ret, $obj);
        $this->assertEquals(400, $obj->height());
    }

    public function testSetHeightNegativeException()
    {
        $obj = $this->obj;
        $this->setExpectedException('\InvalidArgumentException');
        $obj->setHeight(-1);
    }

    public function testSetGravity()
    {
        $obj = $this->obj;
        $ret = $obj->setGravity('nw');
        $this->assertSame($ret, $obj);
        $this->assertEquals('nw', $obj->gravity());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setGravity('foobar');
    }

    public function testSetBackgroundColor()
    {
        $obj = $this->obj;
        $ret = $obj->setBackgroundColor('red');
        $this->assertSame($ret, $obj);
        $this->assertEquals('red', $obj->backgroundColor());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setBackgroundColor(false);
    }

    public function testSetAdaptive()
    {
        $obj = $this->obj;
        $ret = $obj->setAdaptive(true);
        $this->assertSame($ret, $obj);
        $this->assertTrue($obj->adaptive());
    }

    public function testAutoMode()
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

    public function testProcessExactParametersException()
    {
        $obj = $this->obj;
        $obj->setMode('exact');
        $this->setExpectedException('\Exception');
        $obj->process();
    }

    public function testProcessWidthParameterException()
    {
        $obj = $this->obj;
        $obj->setMode('width');
        $this->setExpectedException('\Exception');
        $obj->process();
    }

    public function testProcessHeightParameterException()
    {
        $obj = $this->obj;
        $obj->setMode('height');
        $this->setExpectedException('\Exception');
        $obj->process();
    }

    public function testProcessBestFitParameterException()
    {
        $obj = $this->obj;
        $obj->setMode('best_fit');
        $this->setExpectedException('\Exception');
        $obj->process();
    }

    public function testProcessCropException()
    {
        $obj = $this->obj;
        $obj->setMode('crop');
        $this->setExpectedException('\Exception');
        $obj->process();
    }

    public function testProcessFillException()
    {
        $obj = $this->obj;
        $obj->setMode('fill');
        $this->setExpectedException('\Exception');
        $obj->process();
    }
}
