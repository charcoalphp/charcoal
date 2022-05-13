<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Charcoal\Image\Effect\AbstractThresholdEffect;

/**
 * Threshold Effect for the Imagemagick driver.
 */
class ImagemagickThresholdEffect extends AbstractThresholdEffect
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

        $value = ($this->threshold()*100).'%';
        $cmd = '-threshold '.$value;
        $this->image()->applyCmd($cmd);
        return $this;
    }
}
