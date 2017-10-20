<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Charcoal\Image\Effect\AbstractGrayscaleEffect;

/**
 * Grayscale Effect for the Imagemagick driver.
 */
class ImagemagickGrayscaleEffect extends AbstractGrayscaleEffect
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

        $cmd = '-colorspace Gray';
        return $this->image()->applyCmd($cmd);
    }
}
