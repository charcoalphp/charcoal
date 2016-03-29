<?php

namespace Charcoal\Image\Imagick\Effect;

use \Imagick;

use \Charcoal\Image\Effect\AbstractResizeEffect;

/**
 * Resize Effect for the Imagick driver
 */
class ImagickCropEffect extends AbstractResizeEffect
{
    /**
    * @param integer $x
    * @param integer $y
    * @param integer $width
    * @param integer $height
    * @return void
    */
    protected function doCrop($x, $y, $width, $height)
    {
        $this->image()->imagick()->cropImage($x, $y, $width, $height);
    }
}
