<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Charcoal\Image\Effect\AbstractTintEffect;

/**
 * Tint Effect for the Imagemagick driver.
 */
class ImagemagickTintEffect extends AbstractTintEffect
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

        if ($this->midtone() === true) {
            $tintCmd = '-tint';
        } else {
            $tintCmd = '-colorize';
        }
        $color = $this->color();
        $value = ($this->opacity()*100).'%';
        $cmd = '-fill "'.$color.'" '.$tintCmd.' '.$value;
        $this->image()->applyCmd($cmd);
        return $this;
    }
}
