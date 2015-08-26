<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Charcoal\Image\Effect\AbstractModulateEffect;

class ImagemagickModulateEffect extends AbstractModulateEffect
{
    /**
    * @param array $data
    * @return ImagemagickModulateEffect Chainable
    */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }

        $h = ($this->hue() + 100);
        $s = ($this->saturation() + 100);
        $l = ($this->luminance() + 100);

        $cmd = '-modulate '.$l.','.$s.','.$h;
        $this->image()->apply_cmd($cmd);
        return $this;
    }
}
