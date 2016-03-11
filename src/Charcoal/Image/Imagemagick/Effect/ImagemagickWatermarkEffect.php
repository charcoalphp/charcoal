<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Charcoal\Image\Effect\AbstractWatermarkEffect;
use \Charcoal\Image\ImageInterface;

/**
 * Watermark Effect for the Imagemagick driver.
 */
class ImagemagickWatermarkEffect extends AbstractWatermarkEffect
{
    /**
     * @param array $data The effect data, if available.
     * @return ImagickWatermarkEffect Chainable
     */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
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

        $gravity = $this->image()->imagemagickGravity($this->gravity());
        $cmd =  '-gravity '.$gravity.' ';
        $cmd .= '-geometry +'.$this->x().'+'.$this->y().' ';
        $cmd .= '-draw "image Multiply 0,0 '.$width.','.$height.' ';
        $cmd .= '\''.$watermark.'\'"';
        $this->image()->applyCmd($cmd);
        return $this;
    }
}
