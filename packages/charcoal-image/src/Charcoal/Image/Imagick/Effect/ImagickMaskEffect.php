<?php

namespace Charcoal\Image\Imagick\Effect;

use \Exception;

use \Charcoal\Image\Effect\AbstractMaskEffect;

/**
 * Mask Effect for the Imagick driver.
 */
class ImagickMaskEffect extends AbstractMaskEffect
{
    /**
     * @param array $data The effect data, if available.
     * @throws Exception This effect is not yet supported for Imagick driver.
     * @return void
     */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }

        throw new Exception(
            'Mask effect is not (yet) supported with imagick driver.'
        );
    }
}
