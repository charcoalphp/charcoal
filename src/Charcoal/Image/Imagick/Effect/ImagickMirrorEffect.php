<?php

namespace Charcoal\Image\Imagick\Effect;

use Charcoal\Image\Effect\AbstractMirrorEffect;

/**
 * Mirror Effect for the Imagick driver.
 */
class ImagickMirrorEffect extends AbstractMirrorEffect
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
            // Vertical mirror
            $this->image()->imagick()->flipImage();
        } else {
            $this->image()->imagick()->flopImage();
        }
        return $this;
    }
}
