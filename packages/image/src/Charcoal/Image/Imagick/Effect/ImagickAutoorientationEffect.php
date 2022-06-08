<?php

namespace Charcoal\Image\Imagick\Effect;

use \Imagick;

use \Charcoal\Image\Effect\AbstractAutoorientationEffect;

/**
 * Auto-orientation Effect for the Imagick driver.
 */
class ImagickAutoorientationEffect extends AbstractAutoorientationEffect
{
    /**
     * @param array $data The effect data, if available.
     * @return ImagickAutoorientationEffect Chainable
     */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }

        switch ($this->image()->imagick()->getImageOrientation()) {
            case Imagick::ORIENTATION_TOPLEFT:
                break;
            case Imagick::ORIENTATION_TOPRIGHT:
                $this->image()->imagick()->flopImage();
                break;
            case Imagick::ORIENTATION_BOTTOMRIGHT:
                $this->image()->imagick()->rotateImage('#000', 180);
                break;
            case Imagick::ORIENTATION_BOTTOMLEFT:
                $this->image()->imagick()->flopImage();
                $this->image()->imagick()->rotateImage('#000', 180);
                break;
            case Imagick::ORIENTATION_LEFTTOP:
                $this->image()->imagick()->flopImage();
                $this->image()->imagick()->rotateImage('#000', -90);
                break;
            case Imagick::ORIENTATION_RIGHTTOP:
                $this->image()->imagick()->rotateImage('#000', 90);
                break;
            case Imagick::ORIENTATION_RIGHTBOTTOM:
                $this->image()->imagick()->flopImage();
                $this->image()->imagick()->rotateImage('#000', 90);
                break;
            case Imagick::ORIENTATION_LEFTBOTTOM:
                $this->image()->imagick()->rotateImage('#000', -90);
                break;
            default:
                // Invalid orientation
                break;
        }

        $this->image()->imagick()->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);

        return $this;
    }
}
