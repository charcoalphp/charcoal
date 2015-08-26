<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Exception as Exception;

use \Charcoal\Image\Effect\AbstractWatermarkEffect as AbstractWatermarkEffect;

class ImagemagickWatermarkEffect extends AbstractWatermarkEffect
{
    /**
    * @param array $data
    * @throws Exception
    * @return ImagickWatermarkEffect Chainable
    */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }
        
        throw new Exception(
            'Watermark Effect is not (yet) supported with imagemagick driver.'
        );
    }
}
