<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Exception as Exception;

use \Charcoal\Image\Effect\AbstractTintEffect as AbstractTintEffect;

class ImagemagickTintEffect extends AbstractTintEffect
{
    /**
    * @param array $data
    * @return ImagickTintEffect Chainable
    */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }
        
        if ($this->midtone() === true) {
            $tint_cmd = '-tint';
        } else {
            $tint_cmd = '-colorize';
        }
        $color = $this->color();
        $value = ($this->opacity()*100).'%';
        $cmd = '-fill "'.$color.'" '.$tint_cmd.' '.$value;
        $this->image()->apply_cmd($cmd);
        return $this;
    }
}
