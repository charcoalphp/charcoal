<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Charcoal\Image\Effect\AbstractMirrorEffect;

class ImagemagickMirrorEffect extends AbstractMirrorEffect
{
    /**
    * @param array $data
    * @return ImagickMirrorEffect Chainable
    */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }
        
        $axis = $this->axis();
        if ($axis == 'x') {
            $cmd = '-flip';
        } else {
            $cmd = '-flop';
        }
        $this->image()->apply_cmd($cmd);
        return $this;
    }
}
