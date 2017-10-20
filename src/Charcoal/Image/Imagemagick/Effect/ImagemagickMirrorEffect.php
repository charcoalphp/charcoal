<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Charcoal\Image\Effect\AbstractMirrorEffect;

/**
 * Mirror Effect for the Imagemagick driver.
 */
class ImagemagickMirrorEffect extends AbstractMirrorEffect
{
    /**
     * @param array $data The effect data, if available.
     * @return self
     */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }

        $axis = $this->axis();
        if ($axis == 'x') {
            $cmd = '-flip';
        } else {
            $cmd = '-flop';
        }
        $this->image()->applyCmd($cmd);
        return $this;
    }
}
