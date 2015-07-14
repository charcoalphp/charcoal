<?php

namespace Charcoal\Image\Imagick\Effect;

use \Exception as Exception;

use \Charcoal\Image\Effect\AbstractTintEffect as AbstractTintEffect;

class ImagickTintEffect extends AbstractTintEffect
{
    /**
    * @param array $data
    * @return ImagickTintEffect Chainable
    */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }

        if ($this->midtone() === true) {
            $this->image()->imagick()->tintImage($this->color(), $this->opacity());
        } else {
            $this->image()->imagick()->colorizeImage($this->color(), $this->opacity());
        }

        return $this;
    }
}
