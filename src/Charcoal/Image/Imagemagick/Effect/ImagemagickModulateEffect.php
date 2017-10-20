<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Charcoal\Image\Effect\AbstractModulateEffect;

/**
 * Modulate Effect for the Imagemagick driver.
 */
class ImagemagickModulateEffect extends AbstractModulateEffect
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

        $h = ($this->hue() + 100);
        $s = ($this->saturation() + 100);
        $l = ($this->luminance() + 100);

        $cmd = '-modulate '.$l.','.$s.','.$h;
        $this->image()->applyCmd($cmd);
        return $this;
    }
}
