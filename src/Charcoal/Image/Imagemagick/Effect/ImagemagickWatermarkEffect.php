<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Exception;

use \Charcoal\Image\Effect\AbstractWatermarkEffect;
use \Charcoal\Image\ImageInterface;

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

        if ($this->watermark() instanceof ImageInterface) {
            $out = '/tmp/_'.uniqid().'.png';
            $this->watermark()->save($out);
            $width = $this->watermark()->width();
            $height = $this->watermark()->height();
            $watermark = $out;
        } else {
            $watermark = $this->watermark();
            $c = get_class($this->image());
            $w = new $c;
            $w->open($watermark);
            $width = $w->width();
            $height = $w->height();
        }

        $gravity = $this->image()->imagemagick_gravity($this->gravity());
        $cmd =  '-gravity '.$gravity.' ';
        $cmd .= '-geometry +'.$this->x().'+'.$this->y().' ';
        $cmd .= '-draw "image Multiply 0,0 '.$width.','.$height.' ';
        $cmd .= '\''.$watermark.'\'"';
        $this->image()->apply_cmd($cmd);
        return $this;
    }
}
