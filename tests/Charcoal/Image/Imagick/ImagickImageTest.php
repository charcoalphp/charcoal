<?php

namespace Charcoals\Tests\Image;

use \Charcoal\Image\Imagick\ImagickImage as Image;

class ImagickImageTest extends \PHPUnit_Framework_Testcase
{
    public static function setUpBeforeClass()
    {
        if (!file_exists(OUTPUT_DIR)) {
            mkdir(OUTPUT_DIR);
        }
    }

    public function testFromFactory()
    {
        $obj = \Charcoal\Image\ImageFactory::instance()->get('imagick');

        $this->assertInstanceOf('\Charcoal\Image\Imagick\ImagickImage', $obj);
    }

    public function testCreate()
    {
        $obj = new Image();
        $ret = $obj->create(1, 1);
        $this->assertSame($ret, $obj);

        $this->setExpectedException('\InvalidArgumentException');
        $obj->create('foo', 'bar');
    }

    public function testCreateMinWidth()
    {
        $obj = new Image();
        $this->setExpectedException('\InvalidArgumentException');
        $obj->create(400, 0);
    }

    public function testCreateMinHeigth()
    {
        $obj = new Image();
        $this->setExpectedException('\InvalidArgumentException');
        $obj->create(0, 400);
    }

    public function testOpen()
    {
        $obj = new Image();
        $ret = $obj->open(EXAMPLES_DIR.'/test01.jpg');
        $this->assertSame($ret, $obj);

        $this->setExpectedException('\InvalidArgumentException');
        $obj->open(false);
    }

    public function testOpenInvalidFile()
    {
        $obj = new Image();
        $this->setExpectedException('\Exception');
        $obj->open('foo/bar/baz.png');
    }

    public function testOpenWithoutParamUseSource()
    {
        $obj1 = new Image();
        $obj1->open(EXAMPLES_DIR.'/test01.jpg');

        $id1 = $obj1->imagick()->identifyImage();

        $obj2 = new Image();
        $obj2->set_source(EXAMPLES_DIR.'/test01.jpg');
        $obj2->open();

        $id2 = $obj2->imagick()->identifyImage();

        $this->assertEquals($id1, $id2);
    }

    public function testWidth()
    {
        $obj = new Image();
        $ret = $obj->open(EXAMPLES_DIR.'/test01.jpg');
        
        $width = $obj->width();
        $this->assertEquals(3456, $width);
    }

    public function testHeight()
    {
        $obj = new Image();
        $ret = $obj->open(EXAMPLES_DIR.'/test01.jpg');
        
        $height = $obj->height();
        $this->assertEquals(2304, $height);
    }

    public function testImagickChannel()
    {
        $obj = new Image();
        $ret = $obj->imagick_channel('red');
        $this->assertEquals(\Imagick::CHANNEL_RED, $ret);

        $this->setExpectedException('\InvalidArgumentException');
        $obj->imagick_channel('foobar');
    }

    public function testImagickGravity()
    {
        $obj = new Image();
        $ret = $obj->imagick_gravity('ne');
        $this->assertEquals(\Imagick::GRAVITY_NORTHEAST, $ret);

        $this->setExpectedException('\InvalidArgumentException');
        $obj->imagick_gravity('foobar');
    }

    /**
    * @dataProvider effectProvider
    */
    public function testEffects($effect, $filename)
    {
        $obj = new Image();
        $obj->open(EXAMPLES_DIR.'/test02.png');

        $obj->process_effect($effect);
        $obj->save(OUTPUT_DIR.'/'.$filename);
    }

    public function effectProvider()
    {
        return [
            [['type'=>'blur'], 'imagick-blur-default.png'],
            [['type'=>'blur', 'radius'=>5, 'sigma'=>15], 'imagick-blur-strong.png'],
            [['type'=>'blur', 'mode'=>'adaptive', 'radius'=>5, 'sigma'=>15], 'imagick-blur-adaptive-strong.png'],
            [['type'=>'blur', 'mode'=>'gaussian', 'radius'=>5, 'sigma'=>15], 'imagick-blur-gaussian-strong.png'],
            [['type'=>'blur', 'mode'=>'radial', 'angle'=>8], 'imagick-blur-radial-8.png'],
            [['type'=>'blur', 'mode'=>'motion', 'radius'=>5, 'sigma'=>15, 'angle'=>45], 'imagick-blur-motion-strong.png'],
            [['type'=>'dither'], 'imagick-dither-default.png'],
            [['type'=>'dither', 'colors'=>3], 'imagick-dithers-3colors.png'],
            [['type'=>'grayscale'], 'imagick-grayscale-default.png'],
        //            [['type'=>'mask']],
            [['type'=>'mirror'], 'imagick-mirror-default.png'],
            [['type'=>'mirror', 'axis'=>'x'], 'imagick-mirror-x.png'],
            [['type'=>'mirror', 'axis'=>'y'], 'imagick-mirror-y.png'],
            [['type'=>'modulate'], 'imagick-modulate-default.png'],
            [['type'=>'modulate', 'luminance'=>50], 'imagick-modulate-brightness.png'],
            [['type'=>'modulate', 'luminance'=>20, 'hue'=>-20, 'saturation'=>40], 'imagick-modulate-hsl.png'],
            // Resize
            [['type'=>'resize', 'width'=>400], 'imagick-resize-width-400.png'],
            [['type'=>'resize', 'height'=>1200], 'imagick-resize-height-1200.png'],
            [['type'=>'resize', 'mode'=>'best_fit', 'width'=>300, 'height'=>300], 'imagick-resize-bestfit-300.png'],
            [['type'=>'revert'], 'imagick-revert-default.png'],
            [['type'=>'revert', 'channel'=>'red'], 'imagick-revert-red.png'],
            [['type'=>'rotate'], 'imagick-rotate-default.png'],
            [['type'=>'rotate', 'angle'=>90], 'imagick-rotate-90.png'],
            [['type'=>'rotate', 'angle'=>-135], 'imagick-rotate-135.png'],
            [['type'=>'rotate', 'angle'=>135, 'background_color'=>'rgb(0,0,0)'], 'imagick-rotate-135-black.png'],
            [['type'=>'sepia'], 'imagick-sepia-default.png'],
            [['type'=>'sepia', 'threshold'=>115], 'imagick-sepia-115.png'],
            [['type'=>'sharpen'], 'imagick-sharpen-default.png'],
            [['type'=>'sharpen', 'radius'=>5, 'sigma'=>15], 'imagick-sharpen-strong.png'],
            [['type'=>'threshold'], 'imagick-threshold-default.png'],
            [['type'=>'tint', 'color'=>'rgb(100%,0,0)'], 'imagick-tint-red.png'],
            [['type'=>'tint', 'color'=>'rgb(100%,0,0)', 'midtone'=>false], 'imagick-tint-red-colorize.png'],
            // Watermarkk
            [['type'=>'watermark', 'watermark'=>EXAMPLES_DIR.'/watermark.png'], 'imagick-watermark-default.png']
        ];
    }
}
