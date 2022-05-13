<?php

namespace Charcoal\Image\Imagick\Effect;

use ImagickPixel;

use Charcoal\Image\Effect\AbstractTintEffect;

/**
 * Tint Effect for the Imagick driver.
 */
class ImagickTintEffect extends AbstractTintEffect
{
    /**
     * @param array $data The effect data, if available.
     * @return self
     */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }
        $color = new ImagickPixel($this->color());
        $opacity = new ImagickPixel(sprintf('rgba(255,255,255,%f)', $this->opacity()));

        if ($this->midtone() === true) {
            $this->image()->imagick()->tintImage($color, $opacity);
        } else {
            $this->image()->imagick()->colorizeImage($color, $opacity);
        }

        return $this;
    }
}
