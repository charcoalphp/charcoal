<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Exception;

use \Charcoal\Image\Effect\AbstractResizeEffect;

/**
 * Reisze Effect for the Imagemagick driver.
 */
class ImagemagickResizeEffect extends AbstractResizeEffect
{
    /**
     * @param integer $width   The target width.
     * @param integer $height  The target height.
     * @param boolean $bestFit The "best_fit" flag.
     * @throws Exception This effect is not yet supported with the imagemagick driver.
     * @return void
     */
    protected function doResize($width, $height, $bestFit = false)
    {
        throw new Exception(
            'Resize Effect not valid'
        );
    }
}
