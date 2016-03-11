<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Exception;

use \Charcoal\Image\Effect\AbstractDitherEffect;

/**
 * Dither Effect for the Imagemagick driver.
 */
class ImagemagickDitherEffect extends AbstractDitherEffect
{
    /**
     * @param array $data The effect data, if available.
     * @throws Exception This effect is not supported for Imagemagick driver.
     * @return void
     */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }

        throw new Exception(
            'Dither Effect is not (yet) supported with imagemagick driver.'
        );
    }
}
