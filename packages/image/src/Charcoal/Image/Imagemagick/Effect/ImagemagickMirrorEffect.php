<?php

namespace Charcoal\Image\Imagemagick\Effect;

use Charcoal\Image\Effect\AbstractMirrorEffect;

/**
 * Mirror Effect for the Imagemagick driver.
 */
class ImagemagickMirrorEffect extends AbstractMirrorEffect
{
    /**
     * @param array $data The effect data, if available.
     */
    public function process(?array $data = null): static
    {
        if ($data !== null) {
            $this->setData($data);
        }

        $axis = $this->axis();
        $cmd = $axis == 'x' ? '-flip' : '-flop';
        $this->image()->applyCmd($cmd);
        return $this;
    }
}
