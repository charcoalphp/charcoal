<?php

namespace Charcoal\Image\Imagick\Effect;

use \Charcoal\Image\Effect\AbstractRevertEffect as AbstractRevertEffect;

class ImagickRevertEffect extends AbstractRevertEffect
{
     /**
    * @param array $data
    * @return ImagickRevertEffect Chainable
    */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }

        $channel = $this->image()->imagick_channel($this->channel());
        $this->image()->imagick()->negateImage(false, $channel);

        return $this;
    }
}
