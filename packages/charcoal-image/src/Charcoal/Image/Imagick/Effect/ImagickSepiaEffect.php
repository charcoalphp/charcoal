<?php

namespace Charcoal\Image\Imagick\Effect;

use \Charcoal\Image\Effect\AbstractSepiaEffect;

/**
 * Sepia Effect for the Imagick driver.
 */
class ImagickSepiaEffect extends AbstractSepiaEffect
{
    /**
     * @param array $data The effect data, if available.
     * @return ImagickSepiaEffect Chainable
     */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }

        $this->image()->imagick()->sepiaToneImage($this->threshold());

        return $this;
    }
}
