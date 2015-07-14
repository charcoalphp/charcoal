<?php

namespace Charcoal\Image\Imagick\Effect;

use \Imagick as Imagick;

use \Charcoal\Image\Effect\AbstractResizeEffect as AbstractResizeEffect;

class ImagickResizeEffect extends AbstractResizeEffect
{
    /**
    * @param integer $width
    * @param integer $height
    * @param boolean $best_fit
    * @return void
    */
    protected function do_resize($width, $height, $best_fit = false)
    {
        if ($this->adaptive()) {
            $this->image()->imagick()->adaptiveResizeImage($width, $height, $best_fit);
        } else {
            $this->image()->imagick()->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1, $best_fit);
        }
    }
}
