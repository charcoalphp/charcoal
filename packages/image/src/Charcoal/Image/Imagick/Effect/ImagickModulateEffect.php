<?php

namespace Charcoal\Image\Imagick\Effect;

use \Charcoal\Image\Effect\AbstractModulateEffect;

/**
 * Module Effect for the Imagick driver.
 */
class ImagickModulateEffect extends AbstractModulateEffect
{
    /**
     * @param array $data The effect data, if available.
     * @return AbstractModulateEffect Chainable
     */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }

        $h = ($this->hue() + 100);
        $s = ($this->saturation() + 100);
        $l = ($this->luminance() + 100);

        $this->image()->imagick()->modulateImage($l, $s, $h);

        return $this;
    }
}
