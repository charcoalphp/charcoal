<?php

namespace Charcoal\Image\Imagick\Effect;

use Charcoal\Image\Effect\AbstractThresholdEffect;

/**
 * Threshold Effect for the Imagick driver.
 */
class ImagickThresholdEffect extends AbstractThresholdEffect
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

        $max = $this->image()->imagick()->getQuantumRange();
        $max = $max['quantumRangeLong'];
        $threshold = ($this->threshold() * $max);
        $this->image()->imagick()->thresholdImage($threshold);

        return $this;
    }
}
