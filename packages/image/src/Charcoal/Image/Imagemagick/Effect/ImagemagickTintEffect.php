<?php

namespace Charcoal\Image\Imagemagick\Effect;

use Charcoal\Image\Effect\AbstractTintEffect;

/**
 * Tint Effect for the Imagemagick driver.
 */
class ImagemagickTintEffect extends AbstractTintEffect
{
    /**
     * @param array $data The effect data, if available.
     */
    public function process(?array $data = null): static
    {
        if ($data !== null) {
            $this->setData($data);
        }

        $tintCmd = $this->midtone() === true ? '-tint' : '-colorize';
        $color = $this->color();
        $value = ($this->opacity() * 100) . '%';
        $cmd = '-fill "' . $color . '" ' . $tintCmd . ' ' . $value;
        $this->image()->applyCmd($cmd);
        return $this;
    }
}
