<?php

namespace Charcoal\Image\Imagick\Effect;

use \Exception as Exception;

use \Charcoal\Image\Effect\AbstractMaskEffect as AbstractMaskEffect;

class ImagickMaskEffect extends AbstractMaskEffect
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

        throw new Exception('Mask effect is not (yet) supported with imagick driver.');

        //return $this;
    }
}
