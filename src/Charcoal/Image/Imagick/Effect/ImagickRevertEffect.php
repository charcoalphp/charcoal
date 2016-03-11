<?php

namespace Charcoal\Image\Imagick\Effect;

use \Charcoal\Image\Effect\AbstractRevertEffect;

/**
 * Revert Effect for the Imagick driver.
 */
class ImagickRevertEffect extends AbstractRevertEffect
{
     /**
      * @param array $data The effect data, if available.
      * @return ImagickRevertEffect Chainable
      */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }

        $channel = $this->image()->imagickChannel($this->channel());
        $this->image()->imagick()->negateImage(false, $channel);

        return $this;
    }
}
