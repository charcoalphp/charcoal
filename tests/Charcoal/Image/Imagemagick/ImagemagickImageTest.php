<?php

namespace Charcoals\Tests\Image;

use \Charcoal\Image\Imagemagick\ImagemagickImage as Image;

class ImagemagickImageTest extends \PHPUnit_Framework_Testcase
{
    public static function setUpBeforeClass()
    {
        if (!file_exists(OUTPUT_DIR)) {
            mkdir(OUTPUT_DIR);
        }
    }

    public function testFromFactory()
    {
        $obj = \Charcoal\Image\ImageFactory::instance()->get('imagemagick');

        $this->assertInstanceOf('\Charcoal\Image\Imagemagick\ImagemagickImage', $obj);
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

        //$id1 = $obj1->imagick()->identifyImage();

        $obj2 = new Image();
        $obj2->set_source(EXAMPLES_DIR.'/test01.jpg');
        $obj2->open();

        //$id2 = $obj2->imagick()->identifyImage();

        //$this->assertEquals($id1, $id2);
    }

    public function testWidth()
    {
        $obj = new Image();
        $obj->open(EXAMPLES_DIR.'/test01.jpg');
        
        $width = $obj->width();
        $this->assertEquals(3456, $width);
    }

    public function testHeight()
    {
        $obj = new Image();
        $obj->open(EXAMPLES_DIR.'/test01.jpg');
        
        $height = $obj->height();
        $this->assertEquals(2304, $height);
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
            [['type'=>'blur'], 'imagemagick-blur-default.png'],
            [['type'=>'blur', 'radius'=>5, 'sigma'=>15], 'imagemagick-blur-strong.png'],
            [['type'=>'blur', 'mode'=>'adaptive', 'radius'=>5, 'sigma'=>15], 'blur-adaptive-strong.png'],
            [['type'=>'blur', 'mode'=>'gaussian', 'radius'=>5, 'sigma'=>15], 'imagemagick-blur-gaussian-strong.png'],
            [['type'=>'blur', 'mode'=>'radial', 'angle'=>8], 'imagemagick-blur-radial-8.png'],
            [['type'=>'blur', 'mode'=>'motion', 'radius'=>5, 'sigma'=>15, 'angle'=>45], 'imagemagick-blur-motion-strong.png'],
            //[['type'=>'dither'], 'imagemagick-dither-default.png'],
            //[['type'=>'dither', 'colors'=>3], 'imagemagick-dithers-3colors.png'],
            [['type'=>'grayscale'], 'imagemagick-grayscale-default.png'],
            //[['type'=>'mask']],
            [['type'=>'mirror'], 'imagemagick-mirror-default.png'],
            [['type'=>'mirror', 'axis'=>'x'], 'imagemagick-mirror-x.png'],
            [['type'=>'mirror', 'axis'=>'y'], 'imagemagick-mirror-y.png'],
            [['type'=>'modulate'], 'imagemagick-modulate-default.png'],
            [['type'=>'modulate', 'luminance'=>50], 'imagemagick-modulate-brightness.png'],
            [['type'=>'modulate', 'luminance'=>20, 'hue'=>-20, 'saturation'=>40], 'imagemagick-modulate-hsl.png'],
            //[['type'=>'resize', 'width'=>400], 'imagemagick-resize-width-400.png'],
            //[['type'=>'resize', 'height'=>1200], 'imagemagick-resize-height-1200.png'],
            //[['type'=>'resize', 'mode'=>'best_fit', 'width'=>300, 'height'=>300], 'imagemagick-resize-bestfit-300.png'],
            [['type'=>'revert'], 'imagemagick-revert-default.png'],
            [['type'=>'revert', 'channel'=>'red'], 'imagemagick-revert-red.png'],
            [['type'=>'rotate'], 'imagemagick-rotate-default.png'],
            [['type'=>'rotate', 'angle'=>90], 'imagemagick-rotate-90.png'],
            [['type'=>'rotate', 'angle'=>-135], 'imagemagick-rotate-135.png'],
            [['type'=>'rotate', 'angle'=>135, 'background_color'=>'rgb(0,0,0)'], 'imagemagick-rotate-135-black.png'],
            [['type'=>'sepia'], 'imagemagick-sepia-default.png'],
            [['type'=>'sepia', 'threshold'=>115], 'imagemagick-sepia-115.png'],
            [['type'=>'sharpen'], 'imagemagick-sharpen-default.png'],
            [['type'=>'sharpen', 'radius'=>5, 'sigma'=>15], 'imagemagick-sharpen-strong.png'],
            [['type'=>'threshold'], 'imagemagick-threshold-default.png'],
            [['type'=>'tint', 'color'=>'rgb(100%,0,0)'], 'imagemagick-tint-red.png'],
            [['type'=>'tint', 'color'=>'rgb(100%,0,0)', 'midtone'=>false], 'imagemagick-tint-red-colorize.png'],
            //[['type'=>'watermark', 'watermark'=>EXAMPLES_DIR.'/watermark.png'], 'imagemagick-watermark-default.png']
        ];
    }
}
