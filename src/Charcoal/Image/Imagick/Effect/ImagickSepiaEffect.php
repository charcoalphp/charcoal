<?php

namespace Charcoal\Image\Imagick\Effect;

use \Charcoal\Image\Effect\AbstractSepiaEffect;

class ImagickSepiaEffect extends AbstractSepiaEffect
{
    /**
    * @param array $data
    * @return ImagickSepiaEffect Chainable
    */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }

        $this->image()->imagick()->sepiaToneImage($this->threshold());

        return $this;
    }
}
