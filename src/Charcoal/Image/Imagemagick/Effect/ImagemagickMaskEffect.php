<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Exception;

use \Charcoal\Image\Effect\AbstractMaskEffect;

class ImagemagickMaskEffect extends AbstractMaskEffect
{
    /**
    * @param array $data
    * @throws Exception
    * @return ImagickMaskEffect Chainable
    */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }
        
        throw new Exception(
            'Mask Effect is not (yet) supported with imagemagick driver.'
        );
    }
}
