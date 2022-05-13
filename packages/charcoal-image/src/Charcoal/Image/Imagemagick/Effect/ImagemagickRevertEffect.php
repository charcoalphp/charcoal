<?php

namespace Charcoal\Image\Imagemagick\Effect;

use Charcoal\Image\Effect\AbstractRevertEffect;

/**
 * Rotate Effect for the Imagemagick driver.
 */
class ImagemagickRevertEffect extends AbstractRevertEffect
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

        $channel = $this->image()->convertChannel($this->channel());
        $cmd = '-channel '.$channel.' -negate';
        $this->image()->applyCmd($cmd);
        return $this;
    }
}
