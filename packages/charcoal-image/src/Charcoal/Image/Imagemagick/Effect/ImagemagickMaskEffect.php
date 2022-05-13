<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Exception;

use \Charcoal\Image\Effect\AbstractMaskEffect;

/**
 * Mask Effect for the Imagemagick driver.
 */
class ImagemagickMaskEffect extends AbstractMaskEffect
{
    /**
     * @param array $data The effect data, if available.
     * @throws Exception This effect is not yet supported by Imagemagick driver.
     * @return void
     */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }

        throw new Exception(
            'Mask Effect is not (yet) supported with imagemagick driver.'
        );
    }
}
