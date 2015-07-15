<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Exception as Exception;

use \Charcoal\Image\Effect\AbstractSepiaEffect as AbstractSepiaEffect;

class ImagemagickSepiaEffect extends AbstractSepiaEffect
{
    /**
    * @param array $data
    * @return ImagickSepiaEffect Chainable
    */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }

        $value = (($this->threshold()/255)*100).'%';
        $cmd = '-sepia-tone '.$value;
        $this->image()->apply_cmd($cmd);
        return $this;
    }
}
