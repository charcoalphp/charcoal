<?php

namespace Charcoal\Image\Imagick\Effect;

use \Charcoal\Image\Effect\AbstractRotateEffect;

/**
 * Rotate Effect for the imagick driver.
 */
class ImagickRotateEffect extends AbstractRotateEffect
{
    /**
     * @param array $data The effect data, if available.
     * @return ImagickRotateEffect Chainable
     */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }

        $this->image()->imagick()->rotateImage($this->backgroundColor(), $this->angle());
        return $this;
    }
}
