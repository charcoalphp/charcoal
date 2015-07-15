<?php

namespace Charcoals\Tests\Image\Effect;

class AbstractResizeEffectTest extends \PHPUnit_Framework_Testcase
{
    public $obj;

    public function setUp()
    {
        $img = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Image\Effect\AbstractResizeEffect');
        $this->obj->set_image($img);
    }

    public function testDefaults()
    {
        $obj = $this->obj;

        $this->assertEquals('auto', $obj->mode());
        $this->assertEquals(0, $obj->width());
        $this->assertEquals(0, $obj->height());
        $this->assertEquals('center', $obj->gravity());
        $this->assertEquals('rgba(100%, 100%, 100%, 0)', $obj->background_color());
        $this->assertFalse($obj->adaptive());
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->set_data(
            [
            'mode'=>'exact',
            'width'=>100,
            'height'=>50,
            'gravity'=>'e',
            'background_color'=>'red',
            'adaptive'=>true
            ]
        );
        $this->assertSame($ret, $obj);

        $this->assertEquals('exact', $obj->mode());
        $this->assertEquals(100, $obj->width());
        $this->assertEquals(50, $obj->height());
        $this->assertEquals('e', $obj->gravity());
        $this->assertEquals('red', $obj->background_color());
        $this->assertTrue($obj->adaptive());
    }

    public function testSetMode()
    {
        $obj = $this->obj;
        $ret = $obj->set_mode('width');
        $this->assertSame($ret, $obj);
        $this->assertEquals('width', $obj->mode());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_mode('foobar');
    }

    public function testSetWidth()
    {
        $obj = $this->obj;
        $ret = $obj->set_width(400);
        $this->assertSame($ret, $obj);
        $this->assertEquals(400, $obj->width());
    }

    public function testSetWidthNegativeException()
    {
        $obj = $this->obj;
        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_width(-1);
    }

    public function testSetHeight()
    {
        $obj = $this->obj;
        $ret = $obj->set_height(400);
        $this->assertSame($ret, $obj);
        $this->assertEquals(400, $obj->height());
    }

    public function testSetHeightNegativeException()
    {
        $obj = $this->obj;
        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_height(-1);
    }

    public function testSetGravity()
    {
        $obj = $this->obj;
        $ret = $obj->set_gravity('nw');
        $this->assertSame($ret, $obj);
        $this->assertEquals('nw', $obj->gravity());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_gravity('foobar');
    }

    public function testSetBackgroundColor()
    {
        $obj = $this->obj;
        $ret = $obj->set_background_color('red');
        $this->assertSame($ret, $obj);
        $this->assertEquals('red', $obj->background_color());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_background_color(false);
    }

    public function testSetAdaptive()
    {
        $obj = $this->obj;
        $ret = $obj->set_adaptive(true);
        $this->assertSame($ret, $obj);
        $this->assertTrue($obj->adaptive());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_adaptive('foobar');
    }

    public function testAutoMode()
    {
        $obj = $this->obj;
        $obj->set_mode('auto');

        $obj->set_width(100);
        $obj->set_height(100);
        $this->assertEquals('exact', $obj->auto_mode());

        $obj->set_width(100);
        $obj->set_height(0);
        $this->assertEquals('width', $obj->auto_mode());

        $obj->set_width(0);
        $obj->set_height(100);
        $this->assertEquals('height', $obj->auto_mode());

        $obj->set_width(0);
        $obj->set_height(0);
        $this->assertEquals('none', $obj->auto_mode());
    }

    public function testProcessExactParametersException()
    {
        $obj = $this->obj;
        $obj->set_mode('exact');
        $this->setExpectedException('\Exception');
        $obj->process();
    }

    public function testProcessWidthParameterException()
    {
        $obj = $this->obj;
        $obj->set_mode('width');
        $this->setExpectedException('\Exception');
        $obj->process();
    }

    public function testProcessHeightParameterException()
    {
        $obj = $this->obj;
        $obj->set_mode('height');
        $this->setExpectedException('\Exception');
        $obj->process();
    }

    public function testProcessBestFitParameterException()
    {
        $obj = $this->obj;
        $obj->set_mode('best_fit');
        $this->setExpectedException('\Exception');
        $obj->process();
    }

    public function testProcessCropException()
    {
        $obj = $this->obj;
        $obj->set_mode('crop');
        $this->setExpectedException('\Exception');
        $obj->process();
    }

    public function testProcessFillException()
    {
        $obj = $this->obj;
        $obj->set_mode('fill');
        $this->setExpectedException('\Exception');
        $obj->process();
    }
}
