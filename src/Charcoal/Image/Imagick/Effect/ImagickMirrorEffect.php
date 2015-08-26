<?php

namespace Charcoal\Image\Imagick\Effect;

use \Charcoal\Image\Effect\AbstractMirrorEffect;

class ImagickMirrorEffect extends AbstractMirrorEffect
{
    /**
    * @param array $data
    * @return ImagickBlurEffect Chainable
    */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }

        $axis = $this->axis();
        if ($axis == 'x') {
            // Vertical mirror
            $this->image()->imagick()->flipImage();
        } else {
            $this->image()->imagick()->flopImage();
        }
        return $this;
    }
}
