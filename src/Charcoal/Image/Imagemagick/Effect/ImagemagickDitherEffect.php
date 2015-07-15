<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Exception as Exception;

use \Charcoal\Image\Effect\AbstractDitherEffect as AbstractDitherEffect;

class ImagemagickDitherEffect extends AbstractDitherEffect
{
    /**
    * @param array $data
    * @throws Exception
    * @return ImagickDitherEffect Chainable
    */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }

        throw new Exception('Dither Effect is not (yet) supported with imagemagick driver.');
    }
}
