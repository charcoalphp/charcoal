<?php

namespace Charcoal\Image\Imagick\Effect;

use \Charcoal\Image\Effect\AbstractRotateEffect;

class ImagickRotateEffect extends AbstractRotateEffect
{
    /**
    * @param array $data
    * @return ImagickRotateEffect Chainable
    */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }

        $this->image()->imagick()->rotateImage($this->background_color(), $this->angle());
        return $this;
    }
}
