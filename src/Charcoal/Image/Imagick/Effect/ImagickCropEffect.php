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
        $gravity = $this->image()->imagickGravity($this->gravity());

        // This sets the gravity for the rest of the chain
        $this->image()->imagick()->setGravity($gravity);

        // Apply gravity to crop coordinates

        $imageWidth  = $this->image()->width();
        $imageHeight = $this->image()->height();

        switch ($this->image()->imagick()->getGravity()) {
            case Imagick::GRAVITY_NORTHWEST:
                break;
            case Imagick::GRAVITY_NORTH:
                $x = ($imageWidth / 2 - $width / 2);
                break;
            case Imagick::GRAVITY_NORTHEAST:
                $x = ($imageWidth - $width);
                break;
            case Imagick::GRAVITY_WEST:
                $y = ($imageHeight / 2 - $height / 2);
                break;
            case Imagick::GRAVITY_CENTER:
                $x = ($imageWidth / 2 - $width / 2);
                $y = ($imageHeight / 2 - $height / 2);
                break;
            case Imagick::GRAVITY_EAST:
                $x = ($imageWidth - $width);
                $y = ($imageHeight / 2 - $height / 2);
                break;
            case Imagick::GRAVITY_SOUTHWEST:
                $y = ($imageHeight - $height);
                break;
            case Imagick::GRAVITY_SOUTH:
                $x = ($imageWidth / 2 - $width / 2);
                $y = ($imageHeight - $height);
                break;
            case Imagick::GRAVITY_SOUTHEAST:
                $x = ($imageWidth - $width);
                $y = ($imageHeight - $height);
                break;
        }

        $this->image()->imagick()->cropImage($width, $height, $x, $y);
    }
}
