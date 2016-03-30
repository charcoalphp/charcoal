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
    * @param integer $x
    * @param integer $y
    * @param integer $width
    * @param integer $height
    * @return void
    */
    protected function doCrop($width, $height, $x, $y)
    {
        $this->image()->imagick()->cropImage($width, $height, $x, $y);
    }
}
