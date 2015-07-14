<?php

namespace Charcoal\Image\Imagick\Effect;

use \Exception as Exception;

use \Charcoal\Image\Effect\AbstractThresholdEffect as AbstractThresholdEffect;

class ImagickThresholdEffect extends AbstractThresholdEffect
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

        $this->image()->imagick()->thresholdImage($this->threshold());

        return $this;
    }
}
