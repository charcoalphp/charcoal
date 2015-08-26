<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Charcoal\Image\Effect\AbstractRotateEffect;

class ImagemagickRotateEffect extends AbstractRotateEffect
{
    /**
    * @param array $data
    * @return ImagickRotateEffect Chainable
    */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }
        
        $cmd = '-background "'.$this->background_color().'" -rotate '.$this->angle();
        $this->image()->apply_cmd($cmd);
        return $this;
    }
}
