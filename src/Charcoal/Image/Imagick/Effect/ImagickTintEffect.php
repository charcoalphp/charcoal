<?php

namespace Charcoal\Image\Imagick\Effect;

use \Charcoal\Image\Effect\AbstractTintEffect;

/**
 * Tint Effect for the Imagick driver.
 */
class ImagickTintEffect extends AbstractTintEffect
{
    /**
     * @param array $data The effect data, if available.
     * @return ImagickTintEffect Chainable
     */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }

        if ($this->midtone() === true) {
            $this->image()->imagick()->tintImage($this->color(), $this->opacity());
        } else {
            $this->image()->imagick()->colorizeImage($this->color(), $this->opacity());
        }

        return $this;
    }
}
