<?php

namespace Charcoal\Image\Imagemagick\Effect;

use Charcoal\Image\Effect\AbstractFormatEffect;

/**
 * Format Effect for the Imagemagick driver.
 */
class ImagemagickFormatEffect extends AbstractFormatEffect
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

        if ($this->format()) {
            // Currently not supported
        }
        return $this;
    }
}
