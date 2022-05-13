<?php

namespace Charcoal\Image\Effect;

use InvalidArgumentException;

use Charcoal\Image\AbstractEffect;
use Charcoal\Image\ImageInterface;
use Charcoal\Image\Effect\LayerEffectInterface;
use Charcoal\Image\Effect\LayerEffectTrait;

/**
 * Composite a watermark on top of the image.
 */
abstract class AbstractWatermarkEffect extends AbstractEffect implements LayerEffectInterface
{
    use LayerEffectTrait;

    /**
     * The watermark image source (path or Image).
     * @var string|ImageInterface $watermark
     */
    private $watermark;

    /**
     * @param string|ImageInterface $watermark The watermark (path or Image).
     * @throws InvalidArgumentException If the watermark value is not a string or an Image.
     * @return self
     */
    public function setWatermark($watermark)
    {
        if (is_string($watermark) || ($watermark instanceof ImageInterface)) {
            $this->watermark = $watermark;
            return $this;
        } else {
            throw new InvalidArgumentException(
                'Watermark must be a string'
            );
        }
    }

    /**
     * @return string
     */
    public function watermark()
    {
        return $this->watermark;
    }
}
