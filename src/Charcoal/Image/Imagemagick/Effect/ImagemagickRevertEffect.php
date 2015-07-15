<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Exception as Exception;

use \Charcoal\Image\Effect\AbstractRevertEffect as AbstractRevertEffect;

class ImagemagickRevertEffect extends AbstractRevertEffect
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

        $channel = $this->image()->convert_channel($this->channel());
        $cmd = '-channel '.$channel.' -negate';
        $this->image()->apply_cmd($cmd);
        return $this;
    }
}
