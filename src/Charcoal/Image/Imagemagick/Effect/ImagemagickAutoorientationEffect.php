<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Charcoal\Image\Effect\AbstractAutoorientationEffect;

class ImagemagickAutoorientationEffect extends AbstractAutoorientationEffect
{
    /**
    * @param array $data
    * @return ImagemagickAutoorientationEffect Chainable
    */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }

        $cmd = '-auto-orient';
        return $this->image()->apply_cmd($cmd);
    }
}
