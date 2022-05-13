<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Charcoal\Image\Effect\AbstractRotateEffect;

/**
 * Rotate Effect for the Imagemagick driver.
 */
class ImagemagickRotateEffect extends AbstractRotateEffect
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

        $cmd = '-background "'.$this->backgroundColor().'" -rotate '.$this->angle();
        $this->image()->applyCmd($cmd);
        return $this;
    }
}
