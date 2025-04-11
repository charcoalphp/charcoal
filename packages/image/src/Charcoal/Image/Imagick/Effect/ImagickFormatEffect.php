<?php

namespace Charcoal\Image\Imagick\Effect;

use Exception;
use Charcoal\Image\Effect\AbstractFormatEffect;

/**
 * Format Effect for the Imagick driver.
 */
class ImagickFormatEffect extends AbstractFormatEffect
{
    /**
     * @param array $data The effect data, if available.
     * @return ImageFormatEffect Chainable
     */
    public function process(array $data = null)
    {
        if ($this->format()) {
            $this->image()->imagick()->setimageFormat($this->format());
        }

        return $this;
    }
}
