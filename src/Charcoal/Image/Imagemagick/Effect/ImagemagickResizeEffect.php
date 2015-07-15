<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Exception as Exception;

use \Charcoal\Image\Effect\AbstractResizeEffect as AbstractResizeEffect;

class ImagemagickResizeEffect extends AbstractResizeEffect
{
    /**
    * @param array $data
    * @throws Exception
    * @return ImagickResizeEffect Chainable
    */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }
        
        throw new Exception('Resize Effect not valid');
    }
}
