<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Exception;

use \Charcoal\Image\Effect\AbstractCropEffect;

/**
 * Reisze Effect for the Imagemagick driver.
 *
 * See {@link http://www.imagemagick.org/script/command-line-processing.php#geometry Image Geometry}
 * for complete details about the geometry argument.
 */
class ImagemagickCropEffect extends AbstractCropEffect
{
    /**
     * @param  integer $width  The crop width.
     * @param  integer $height The crop height.
     * @param  integer $x      The x-position (in pixel) of the crop.
     * @param  integer $y      The y-position (in pixel) of the crop.
     * @return void
     */
    protected function doCrop($width, $height, $x, $y)
    {
        $option = '-crop';

        $geometry = $this->geometry();
        if ($geometry) {
            $params = [ $option.' "'.$geometry.'"' ];
        } else {
            $params = [ '-gravity "'.$this->gravity().'"' ];

            $size = $width.'x'.$height.($x >= 0 ? '+' : '').$x.($y >= 0 ? '+' : '').$y;
            $params[] = $option.' '.$size;
        }

        if ($this->repage()) {
            $params[] = '+repage';
        }

        $this->image()->applyCmd(implode(' ', $params));
    }
}
