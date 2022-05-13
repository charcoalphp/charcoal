<?php

namespace Charcoal\Image\Imagemagick\Effect;

use Charcoal\Image\Effect\AbstractSepiaEffect;

/**
 * Sepia Effect for the Imagemagick driver.
 */
class ImagemagickSepiaEffect extends AbstractSepiaEffect
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

        $value = (($this->threshold()/255)*100).'%';
        $cmd = '-sepia-tone '.$value;
        $this->image()->applyCmd($cmd);
        return $this;
    }
}
