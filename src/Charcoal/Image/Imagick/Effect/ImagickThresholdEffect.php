<?php

namespace Charcoal\Image\Imagick\Effect;

use \Charcoal\Image\Effect\AbstractThresholdEffect;

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

        $max = $this->image()->imagick()->getQuantumRange();
        $max = $max["quantumRangeLong"];
        $threshold = ($this->threshold() * $max);
        $this->image()->imagick()->thresholdImage($threshold);

        return $this;
    }
}
