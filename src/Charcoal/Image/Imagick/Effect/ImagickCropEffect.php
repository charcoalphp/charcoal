<?php

namespace Charcoal\Image\Imagick\Effect;

use \Imagick;

use \Charcoal\Image\Effect\AbstractCropEffect;

/**
 * Resize Effect for the Imagick driver
 */
class ImagickCropEffect extends AbstractCropEffect
{
    /**
     * @param integer $width  The crop width.
     * @param integer $height The crop height.
     * @param integer $x      The x-position (in pixel) of the crop.
     * @param integer $y      The y-position (in pixel) of the crop.
     * @return void
     */
    protected function doCrop($width, $height, $x, $y)
    {
        $this->image()->imagick()->cropImage($width, $height, $x, $y);
    }
}
