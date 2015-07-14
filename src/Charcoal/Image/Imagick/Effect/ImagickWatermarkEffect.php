<?php

namespace Charcoal\Image\Imagick\Effect;

use \Exception as Exception;

use \Charcoal\Image\Effect\AbstractWatermarkEffect as AbstractWatermarkEffect;

class ImagickWatermarkEffect extends AbstractWatermarkEffect
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

        $img = $this->image();
        
        $img_class = get_class($img);
        $watermark = new $img_class;
        $watermark->open($this->watermark());
        if (($watermark->width() > $img->width()) || ($watermark->height() > $img->height())) {
            // Scale-down watermark image, if necessary
            $watermark->resize(['mode'=>'best_fit', 'width'=>$img->width(), 'height'=>$img->height()]);
        }
        
        $gravity = $this->image()->imagick_gravity($this->gravity());
        $this->image()->imagick()->setGravity($gravity);
        $this->image()->imagick()->compositeImage($watermark->imagick(), \Imagick::COMPOSITE_MULTIPLY, $this->x(), $this->y());

        //throw new Exception('Watermark effect is not (yet) supported with imagick driver.');

        return $this;
    }
}
