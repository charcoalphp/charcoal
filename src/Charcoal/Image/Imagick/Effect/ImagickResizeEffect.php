<?php

namespace Charcoal\Image\Imagick\Effect;

use \Imagick;

use \Charcoal\Image\Effect\AbstractResizeEffect;

/**
 * Resize Effect for the Imagick driver
 */
class ImagickResizeEffect extends AbstractResizeEffect
{
    /**
     * @param integer $width   The target width.
     * @param integer $height  The target height.
     * @param boolean $bestFit The "best_fit" flag.
     * @return void
     */
    protected function doResize($width, $height, $bestFit = false)
    {
        if ($this->adaptive()) {
            $this->image()->imagick()->adaptiveResizeImage($width, $height, $bestFit);
        } else {
            $this->image()->imagick()->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1, $bestFit);
        }
    }
}
