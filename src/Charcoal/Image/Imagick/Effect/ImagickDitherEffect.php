<?php

namespace Charcoal\Image\Imagick\Effect;

use \Imagick;

use \Charcoal\Image\Effect\AbstractDitherEffect;

class ImagickDitherEffect extends AbstractDitherEffect
{
    /**
    * @param array $data
    * @return ImagickDitherEffect Chainable
    */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }

        $mode = $this->mode();
        if ($mode === '') {
            $colorspace = $this->image()->imagick()->getColorSpace();
            $tree_depth = 0;
            $dither = true;
            $this->image()->imagick()->quantizeImage($this->colors(), $colorspace, $tree_depth, $dither, false);
        } else {
            $this->image()->imagick()->orderedPosterizeImage($mode);
        }

        $this->image()->imagick()->setImageColorSpace(Imagick::COLORSPACE_GRAY);

        return $this;
    }
}
