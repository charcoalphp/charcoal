<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Charcoal\Image\Effect\AbstractAutoorientationEffect;

/**
 * Auto-orientate Effect for the Imagemagick driver.
 */
class ImagemagickAutoorientationEffect extends AbstractAutoorientationEffect
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

        $cmd = '-auto-orient';
        return $this->image()->applyCmd($cmd);
    }
}
