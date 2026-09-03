<?php

namespace Charcoals\Tests\Image;

use InvalidArgumentException;

use Charcoal\Image\ImageFactory;
use Charcoal\Image\Imagemagick\ImagemagickImage as Image;

class ImagemagickImageTest extends \PHPUnit\Framework\TestCase
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
        return $this->imageFactory()->create('imagemagick');
    }

    public function testFromFactory()
    {
        $obj = $this->createImage();
        $this->assertInstanceOf(Image::class, $obj);
    }

//    public function testCreate()
//    {
//        $obj = $this->createImage();
//        $ret = $obj->create(1, 1);
//        $this->assertSame($ret, $obj);
//
//        $this->expectException(InvalidArgumentException::class);
//        $obj->create('foo', 'bar');
//    }

    public function testCreateMinWidth()
    {
        $obj = $this->createImage();
        $this->expectException(InvalidArgumentException::class);
        $obj->create(400, 0);
    }

    public function testCreateMinHeigth()
    {
        $obj = $this->createImage();
        $this->expectException(InvalidArgumentException::class);
        $obj->create(0, 400);
    }

    public function testOpen()
    {
        $obj = $this->createImage();
        $ret = $obj->open(EXAMPLES_DIR.'/test01.jpg');
        $this->assertSame($ret, $obj);

        $this->expectException(InvalidArgumentException::class);
        $obj->open(false);
    }

    public function testOpenInvalidFile()
    {
        $obj = $this->createImage();
        $this->expectException('\Exception');
        $obj->open('foo/bar/baz.png');
    }

//    public function testOpenWithoutParamUseSource()
//    {
//        $obj1 = $this->createImage();
//        $obj1->open(EXAMPLES_DIR.'/test01.jpg');
//
//        //$id1 = $obj1->imagick()->identifyImage();
//
//        $obj2 = $this->createImage();
//        $obj2->setSource(EXAMPLES_DIR.'/test01.jpg');
//        $obj2->open();
//
//        //$id2 = $obj2->imagick()->identifyImage();
//
//        //$this->assertEquals($id1, $id2);
//    }

    public function testWidth()
    {
        $obj = $this->createImage();
        $obj->open(EXAMPLES_DIR.'/test01.jpg');

        $width = $obj->width();
        $this->assertEquals(3456, $width);
    }

    public function testHeight()
    {
        $obj = $this->createImage();
        $obj->open(EXAMPLES_DIR.'/test01.jpg');

        $height = $obj->height();
        $this->assertEquals(2304, $height);
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
    public function testInvalidEffext($effect)
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
            [ [ 'type' => 'blur' ], 'imagemagick-blur-default.png' ],
            [ [ 'type' => 'blur', 'radius' => 5, 'sigma' => 15 ], 'imagemagick-blur-strong.png' ],
            [ [ 'type' => 'blur', 'mode' => 'adaptive', 'radius' => 5, 'sigma' => 15 ], 'blur-adaptive-strong.png' ],
            [ [ 'type' => 'blur', 'mode' => 'gaussian', 'radius' => 5, 'sigma' => 15 ], 'imagemagick-blur-gaussian-strong.png' ],
            [ [ 'type' => 'blur', 'mode' => 'radial', 'angle' => 8 ], 'imagemagick-blur-radial-8.png' ],
            [ [ 'type' => 'blur', 'mode' => 'motion', 'radius' => 5, 'sigma' => 15, 'angle' => 45 ], 'imagemagick-blur-motion-strong.png' ],
            # Dither
            // [ [ 'type' => 'dither' ], 'imagemagick-dither-default.png' ],
            // [ [ 'type' => 'dither', 'colors' => 3 ], 'imagemagick-dithers-3colors.png' ],
            # Grayscale
            [ [ 'type' => 'grayscale' ], 'imagemagick-grayscale-default.png' ],
            # Mask
            // [ [ 'type' => 'mask' ] ],
            # Mirror
            [ [ 'type' => 'mirror' ], 'imagemagick-mirror-default.png' ],
            [ [ 'type' => 'mirror', 'axis' => 'x' ], 'imagemagick-mirror-x.png' ],
            [ [ 'type' => 'mirror', 'axis' => 'y' ], 'imagemagick-mirror-y.png' ],
            # Modulate
            [ [ 'type' => 'modulate' ], 'imagemagick-modulate-default.png' ],
            [ [ 'type' => 'modulate', 'luminance' => 50 ], 'imagemagick-modulate-brightness.png' ],
            [ [ 'type' => 'modulate', 'luminance' => 20, 'hue' => -20, 'saturation' => 40 ], 'imagemagick-modulate-hsl.png' ],
            # Resize
            [ [ 'type' => 'resize', 'size' => '50%' ], 'imagick-resize-size-half.png' ],
            [ [ 'type' => 'resize', 'width' => 400 ], 'imagick-resize-width-400.png' ],
            [ [ 'type' => 'resize', 'height' => 1200 ], 'imagick-resize-height-1200.png' ],
            [ [ 'type' => 'resize', 'mode' => 'best_fit', 'width' => 300, 'height' => 300 ], 'imagick-resize-bestfit-300.png' ],
            # Revert
            [ [ 'type' => 'revert' ], 'imagemagick-revert-default.png' ],
            [ [ 'type' => 'revert', 'channel' => 'red' ], 'imagemagick-revert-red.png' ],
            # Rotate
            [ [ 'type' => 'rotate' ], 'imagemagick-rotate-default.png' ],
            [ [ 'type' => 'rotate', 'angle' => 90 ], 'imagemagick-rotate-90.png' ],
            [ [ 'type' => 'rotate', 'angle' => -135 ], 'imagemagick-rotate-135.png' ],
            [ [ 'type' => 'rotate', 'angle' => 135, 'background_color' => 'rgb(0,0,0)' ], 'imagemagick-rotate-135-black.png' ],
            # Sepia
            [ [ 'type' => 'sepia' ], 'imagemagick-sepia-default.png' ],
            [ [ 'type' => 'sepia', 'threshold' => 115 ], 'imagemagick-sepia-115.png' ],
            # Sharpen
            [ [ 'type' => 'sharpen' ], 'imagemagick-sharpen-default.png' ],
            [ [ 'type' => 'sharpen', 'radius' => 5, 'sigma' => 15 ], 'imagemagick-sharpen-strong.png' ],
            # Threshold
            [ [ 'type' => 'threshold' ], 'imagemagick-threshold-default.png' ],
            # Tint
            [ [ 'type' => 'tint', 'color' => 'rgb(255,0,0)' ], 'imagemagick-tint-red.png' ],
            [ [ 'type' => 'tint', 'color' => 'rgb(255,0,0)', 'midtone' => false ], 'imagemagick-tint-red-colorize.png' ],
            # Watermarkks
            // [ [ 'type' => 'watermark', 'watermark' => EXAMPLES_DIR.'/watermark.png' ], 'imagemagick-watermark-default.png' ]
        ];
    }

    public function invalidEffectProvider()
    {
        return [
            # Dither
            [ [ 'type' => 'dither' ], 'imagemagick-dither-default.png' ],
            [ [ 'type' => 'dither', 'colors' => 3 ], 'imagemagick-dithers-3colors.png' ],
            # Mask
            [ [ 'type' => 'mask' ] ],

            # Watermarkk
            [ [ 'type' => 'watermark', 'watermark' => EXAMPLES_DIR.'/watermark.png' ], 'imagemagick-watermark-default.png' ]
        ];
    }

    /**
     * Regression tests: effect parameters that are concatenated into the ImageMagick
     * command line (background colors, geometry/size strings, watermark paths) must
     * never be interpretable as shell metacharacters by ImagemagickImage::exec().
     *
     * Each case embeds a "; touch <marker> ;" payload in the parameter under test and
     * asserts the marker file was never created, regardless of whether ImageMagick
     * itself accepts or rejects the (bogus) literal value.
     *
     * @param  array  $effect The effect data, with a shell-injection payload embedded.
     * @param  string $marker Path to the marker file the payload attempts to create.
     * @return void
     */
    private function assertEffectDoesNotShellInject(array $effect, $marker)
    {
        if (file_exists($marker)) {
            unlink($marker);
        }

        $obj = $this->createImage();
        $obj->open(EXAMPLES_DIR.'/test02.png');

        try {
            $obj->processEffect($effect);
        } catch (\Exception $e) {
            // ImageMagick may legitimately reject the bogus literal value;
            // what matters here is that the shell never ran the injected command.
        }

        $this->assertFalse(
            file_exists($marker),
            'Effect parameter was interpreted as a shell command instead of a literal value'
        );

        if (file_exists($marker)) {
            unlink($marker);
        }
    }

    public function testResizeBackgroundColorIsNotShellInjectable()
    {
        $marker = sys_get_temp_dir() . '/charcoal_test_marker_bg_' . uniqid();
        $this->assertEffectDoesNotShellInject([
            'type'             => 'resize',
            'width'            => 10,
            'height'           => 10,
            'background_color' => '" ; touch ' . $marker . ' ; echo "',
        ], $marker);
    }

    public function testResizeSizeIsNotShellInjectable()
    {
        $marker = sys_get_temp_dir() . '/charcoal_test_marker_size_' . uniqid();
        $this->assertEffectDoesNotShellInject([
            'type' => 'resize',
            'size' => '50%" ; touch ' . $marker . ' ; echo "',
        ], $marker);
    }

    public function testCropGeometryIsNotShellInjectable()
    {
        $marker = sys_get_temp_dir() . '/charcoal_test_marker_geo_' . uniqid();
        $this->assertEffectDoesNotShellInject([
            'type'     => 'crop',
            'geometry' => '10x10+0+0" ; touch ' . $marker . ' ; echo "',
        ], $marker);
    }

    public function testWatermarkIsNotShellInjectable()
    {
        // The watermark path must point to a file that genuinely exists (open()
        // validates it with file_exists() first), so the payload is embedded in
        // the *name* of a real file rather than appended after a valid path —
        // mirroring a file uploaded with an unusual-but-legal filename. The
        // marker name is kept relative (no embedded "/") because copy() itself
        // rejects a second absolute path fragment inside a filename, which is
        // an unrelated PHP stream-wrapper quirk, not part of what's under test.
        $markerName = 'charcoal_test_marker_wm_' . uniqid() . '.tmp';
        $marker = getcwd() . '/' . $markerName;
        $maliciousWatermark = sys_get_temp_dir() . '/charcoal_test_wm_' . uniqid() . '; touch ' . $markerName . ' ;.png';
        copy(EXAMPLES_DIR . '/watermark.png', $maliciousWatermark);

        try {
            $this->assertEffectDoesNotShellInject([
                'type'      => 'watermark',
                'watermark' => $maliciousWatermark,
            ], $marker);
        } finally {
            if (file_exists($maliciousWatermark)) {
                unlink($maliciousWatermark);
            }
        }
    }
}
