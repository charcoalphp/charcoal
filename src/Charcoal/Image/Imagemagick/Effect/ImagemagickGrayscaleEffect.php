<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Exception as Exception;

use \Charcoal\Image\Effect\AbstractGrayscaleEffect as AbstractGrayscaleEffect;

class ImagemagickGrayscaleEffect extends AbstractGrayscaleEffect
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
        
        $cmd = '-colorspace Gray';
        return $this->image()->apply_cmd($cmd);
    }
}
