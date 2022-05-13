<?php

namespace Charcoals\Tests\Image;

use InvalidArgumentException;

use Imagick;

use Charcoal\Image\ImageFactory;
use Charcoal\Image\Imagick\ImagickImage as Image;

class ImagickImageTest extends \PHPUnit\Framework\TestCase
{
    private $factory;

    public function imageFactory()
    {
        if ($this->factory === null) {
            $this->factory = new ImageFactory();
        }

        return $this->factory;
    }

    public function createImage()
    {
        return $this->imageFactory()->create('imagick');
    }

    public function testFromFactory()
    {
        $obj = $this->createImage();
        $this->assertInstanceOf(Image::class, $obj);
    }

    public function testCreate()
    {
        $obj = $this->createImage();
        $ret = $obj->create(1, 1);
        $this->assertSame($ret, $obj);

        $this->expectException('\InvalidArgumentException');
        $obj->create('foo', 'bar');
    }

    public function testCreateMinWidth()
    {
        $obj = $this->createImage();
        $this->expectException('\InvalidArgumentException');
        $obj->create(400, 0);
    }

    public function testCreateMinHeigth()
    {
        $obj = $this->createImage();
        $this->expectException('\InvalidArgumentException');
        $obj->create(0, 400);
    }

    public function testOpen()
    {
        $obj = $this->createImage();
        $ret = $obj->open(EXAMPLES_DIR.'/test01.jpg');
        $this->assertSame($ret, $obj);

        $this->expectException('\InvalidArgumentException');
        $obj->open(false);
    }

    public function testOpenInvalidFile()
    {
        $obj = $this->createImage();
        $this->expectException('\Exception');
        $obj->open('foo/bar/baz.png');
    }

    public function testOpenWithoutParamUseSource()
    {
        $obj1 = $this->createImage();
        $obj1->open(EXAMPLES_DIR.'/test01.jpg');

        $id1 = $obj1->imagick()->identifyImage();

        $obj2 = $this->createImage();
        $obj2->setSource(EXAMPLES_DIR.'/test01.jpg');
        $obj2->open();

        $id2 = $obj2->imagick()->identifyImage();

        $this->assertEquals($id1, $id2);
    }

    public function testWidth()
    {
        $obj = $this->createImage();
        $ret = $obj->open(EXAMPLES_DIR.'/test01.jpg');

        $width = $obj->width();
        $this->assertEquals(3456, $width);
    }

    public function testHeight()
    {
        $obj = $this->createImage();
        $ret = $obj->open(EXAMPLES_DIR.'/test01.jpg');

        $height = $obj->height();
        $this->assertEquals(2304, $height);
    }

    public function testImagickChannel()
    {
        $obj = $this->createImage();
        $ret = $obj->imagickChannel('red');
        $this->assertEquals(Imagick::CHANNEL_RED, $ret);

        $this->expectException('\InvalidArgumentException');
        $obj->imagickChannel('foobar');
    }

    public function testImagickGravity()
    {
        $obj = $this->createImage();
        $ret = $obj->imagickGravity('ne');
        $this->assertEquals(Imagick::GRAVITY_NORTHEAST, $ret);

        $this->expectException('\InvalidArgumentException');
        $obj->imagickGravity('foobar');
    }

    /**
     * @dataProvider effectProvider
     */
    public function testEffects($effect, $filename)
    {
        $obj = $this->createImage();
        $obj->open(EXAMPLES_DIR.'/test02.png');

        $obj->processEffect($effect);
        $obj->save(OUTPUT_DIR.'/'.$filename);

        $this->assertTrue(file_exists(OUTPUT_DIR.'/'.$filename));
    }

    /**
     * @dataProvider invalidEffectProvider
     */
    public function testInvalidEffect($effect)
    {
        $obj = $this->createImage();
        $obj->open(EXAMPLES_DIR.'/test02.png');

        $this->expectException(InvalidArgumentException::class);
        $obj->processsEffect($effect);
    }

    public function effectProvider()
    {
        return [
            # Blur
            [ [ 'type' => 'blur' ], 'imagick-blur-default.png' ],
            [ [ 'type' => 'blur', 'radius' => 5, 'sigma' => 15 ], 'imagick-blur-strong.png' ],
            [ [ 'type' => 'blur', 'mode' => 'adaptive', 'radius' => 5, 'sigma' => 15 ], 'imagick-blur-adaptive-strong.png' ],
            [ [ 'type' => 'blur', 'mode' => 'gaussian', 'radius' => 5, 'sigma' => 15 ], 'imagick-blur-gaussian-strong.png' ],
            [ [ 'type' => 'blur', 'mode' => 'radial', 'angle' => 8 ], 'imagick-blur-radial-8.png' ],
            [ [ 'type' => 'blur', 'mode' => 'motion', 'radius' => 5, 'sigma' => 15, 'angle' => 45 ], 'imagick-blur-motion-strong.png' ],
            # Dither
            [ [ 'type' => 'dither' ], 'imagick-dither-default.png' ],
            [ [ 'type' => 'dither', 'colors' => 3 ], 'imagick-dithers-3colors.png' ],
            # Grayscale
            [ [ 'type' => 'grayscale' ], 'imagick-grayscale-default.png' ],
            # Mask
            // [ [ 'type' => 'mask' ] ],
            # Mirror
            [ [ 'type' => 'mirror' ], 'imagick-mirror-default.png' ],
            [ [ 'type' => 'mirror', 'axis' => 'x' ], 'imagick-mirror-x.png' ],
            [ [ 'type' => 'mirror', 'axis' => 'y' ], 'imagick-mirror-y.png' ],
            # Modulate
            [ [ 'type' => 'modulate' ], 'imagick-modulate-default.png' ],
            [ [ 'type' => 'modulate', 'luminance' => 50 ], 'imagick-modulate-brightness.png' ],
            [ [ 'type' => 'modulate', 'luminance' => 20, 'hue' => -20, 'saturation' => 40 ], 'imagick-modulate-hsl.png' ],
            # Resize
            // [ [ 'type' => 'resize', 'size' => '50%' ], 'imagick-resize-size-half.png' ],
            [ [ 'type' => 'resize', 'width' => 400 ], 'imagick-resize-width-400.png' ],
            [ [ 'type' => 'resize', 'height' => 1200 ], 'imagick-resize-height-1200.png' ],
            [ [ 'type' => 'resize', 'mode' => 'best_fit', 'width' => 300, 'height' => 300 ], 'imagick-resize-bestfit-300.png' ],
            # Revert
            [ [ 'type' => 'revert' ], 'imagick-revert-default.png' ],
            [ [ 'type' => 'revert', 'channel' => 'red' ], 'imagick-revert-red.png' ],
            # Rotate
            [ [ 'type' => 'rotate' ], 'imagick-rotate-default.png' ],
            [ [ 'type' => 'rotate', 'angle' => 90 ], 'imagick-rotate-90.png' ],
            [ [ 'type' => 'rotate', 'angle' => -135 ], 'imagick-rotate-135.png' ],
            [ [ 'type' => 'rotate', 'angle' => 135, 'background_color' => 'rgb(0,0,0)' ], 'imagick-rotate-135-black.png' ],
            # Sepia
            [ [ 'type' => 'sepia' ], 'imagick-sepia-default.png' ],
            [ [ 'type' => 'sepia', 'threshold' => 115 ], 'imagick-sepia-115.png' ],
            # Sharpen
            [ [ 'type' => 'sharpen' ], 'imagick-sharpen-default.png' ],
            [ [ 'type' => 'sharpen', 'radius' => 5, 'sigma' => 15 ], 'imagick-sharpen-strong.png' ],
            [ [ 'type' => 'sharpen', 'mode' => 'unsharp' ], 'imagick-sharpen-unsharp.png' ],

            # Threshold
            [ [ 'type' => 'threshold' ], 'imagick-threshold-default.png' ],
            # Tint
            [ [ 'type' => 'tint', 'color' => 'rgb(255,0,0)' ], 'imagick-tint-red.png' ],
            [ [ 'type' => 'tint', 'color' => 'rgb(255,0,0)', 'midtone' => false ], 'imagick-tint-red-colorize.png' ],
            # Watermarkk
            [ [ 'type' => 'watermark', 'watermark' => EXAMPLES_DIR.'/watermark.png' ], 'imagick-watermark-default.png' ],
            [ [ 'type' => 'watermark', 'watermark' => EXAMPLES_DIR.'/watermark.png', 'gravity' => 'nw' ], 'imagick-watermark-nw.png' ],
            [ [ 'type' => 'watermark', 'watermark' => EXAMPLES_DIR.'/watermark.png', 'gravity' => 'n' ], 'imagick-watermark-n.png' ],
            [ [ 'type' => 'watermark', 'watermark' => EXAMPLES_DIR.'/watermark.png', 'gravity' => 'ne' ], 'imagick-watermark-ne.png' ],
            [ [ 'type' => 'watermark', 'watermark' => EXAMPLES_DIR.'/watermark.png', 'gravity' => 'w' ], 'imagick-watermark-w.png' ],
            [ [ 'type' => 'watermark', 'watermark' => EXAMPLES_DIR.'/watermark.png', 'gravity' => 'center' ], 'imagick-watermark-center.png' ],
            [ [ 'type' => 'watermark', 'watermark' => EXAMPLES_DIR.'/watermark.png', 'gravity' => 'e' ], 'imagick-watermark-e.png' ],
            [ [ 'type' => 'watermark', 'watermark' => EXAMPLES_DIR.'/watermark.png', 'gravity' => 'sw' ], 'imagick-watermark-sw.png' ],
            [ [ 'type' => 'watermark', 'watermark' => EXAMPLES_DIR.'/watermark.png', 'gravity' => 's' ], 'imagick-watermark-s.png' ],
            [ [ 'type' => 'watermark', 'watermark' => EXAMPLES_DIR.'/watermark.png', 'gravity' => 'se' ], 'imagick-watermark-se.png' ],
            [ [ 'type' => 'watermark', 'watermark' => EXAMPLES_DIR.'/watermark.png', 'opacity' => 0.5 ], 'imagick-watermark-05.png' ]
        ];
    }

    public function invalidEffectProvider()
    {
        return [
            # Blur
            [ [ 'type' => 'blur', 'mode' => 'soft' ], 'imagick-blur-soft.png' ],
            # Mask
            [ [ 'type' => 'mask' ] ],
        ];
    }
}
