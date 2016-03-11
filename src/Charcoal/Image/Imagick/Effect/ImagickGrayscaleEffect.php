<?php

namespace Charcoal\Image\Imagick\Effect;

use \Imagick as Imagick;

use \Charcoal\Image\Effect\AbstractGrayscaleEffect;

/**
 * Grayscale Effect for the Imagick driver.
 */
class ImagickGrayscaleEffect extends AbstractGrayscaleEffect
{

    /**
     * @param array $data The effect data, if available.
     * @return ImagickGrayscaleEffect Chainable
     */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }

        $this->image()->imagick()->transformImageColorSpace(Imagick::COLORSPACE_GRAY);

        return $this;
    }
}
