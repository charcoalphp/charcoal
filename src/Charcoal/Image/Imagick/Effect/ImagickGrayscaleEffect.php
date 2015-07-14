<?php

namespace Charcoal\Image\Imagick\Effect;

use \Imagick as Imagick;

use \Charcoal\Image\Effect\AbstractGrayscaleEffect as AbstractGrayscaleEffect;

class ImagickGrayscaleEffect extends AbstractGrayscaleEffect
{

    /**
    * @param array $data
    * @return ImagickGrayscaleEffect Chainable
    */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }

        $this->image()->imagick()->transformImageColorSpace(Imagick::COLORSPACE_GRAY);

        return $this;
    }
}
