<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Exception;

use \Charcoal\Image\Effect\AbstractResizeEffect;

/**
 * Reisze Effect for the Imagemagick driver.
 */
class ImagemagickResizeEffect extends AbstractResizeEffect
{
    /**
     * @param array $data The effect data, if available.
     * @throws Exception This effect is not yet available for Imagemagick driver.
     * @return void
     */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }

        throw new Exception(
            'Resize Effect not valid'
        );
    }
}
