<?php

namespace Charcoal\Image\Imagemagick\Effect;

use Charcoal\Image\Effect\AbstractCompressionEffect;

/**
 * Compression Effect for the Imagemagick driver.
 */
class ImagemagickCompressionEffect extends AbstractCompressionEffect
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

        $cmd = sprintf('-quality %s%', $this->quality());
        $this->image()->applyCmd($cmd);
        return $this;
    }
}
