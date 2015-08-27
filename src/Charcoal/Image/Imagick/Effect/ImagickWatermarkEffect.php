<?php

namespace Charcoal\Image\Imagick\Effect;

use \Exception;
use \Imagick;

use \Charcoal\Image\Effect\AbstractWatermarkEffect;
use \Charcoal\Image\ImageInterface;

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
        $img_w = $img->width();
        $img_h = $img->height();
        
        if ($this->watermark() instanceof ImageInterface) {
            $watermark = $this->watermark();
        } else {
            $img_class = get_class($img);
            $watermark = new $img_class;
            $watermark->open($this->watermark());
        }
        
        if (($watermark->width() > $img_w) || ($watermark->height() > $img_h)) {
            // Scale-down watermark image, if necessary
            $watermark->resize(['mode'=>'best_fit', 'width'=>$img_w, 'height'=>$img_h]);
        }

        $mark_w = $watermark->width();
        $mark_h = $watermark->height();

        $gravity = $this->gravity();
        if ($gravity == 'nw') {
            $x = $this->x();
            $y = $this->y();
        } elseif ($gravity == 'n') {
            $x = ($img_w/2 - ($mark_w/2) + $this->x());
            $y = $this->y();
        } elseif ($gravity == 'ne') {
            $x = ($img_w - $mark_h - $this->x());
            $y = $this->y();
        } elseif ($gravity == 'w') {
            $x = $this->x();
            $y = ($img_h/2 - ($mark_h/2) + $this->y());
        } elseif ($gravity == 'center') {
            $x = ($img_w/2 - ($mark_w/2) + $this->x());
            $y = ($img_h/2 - ($mark_h/2) + $this->y());
        } elseif ($gravity == 'e') {
            $x = ($img_w - $mark_w - $this->x());
            $y = ($img_h/2 - ($mark_h/2) + $this->y());
        } elseif ($gravity == 'sw') {
            $x = $this->x();
            $y = ($img_h - $mark_h - $this->y());
        } elseif ($gravity == 's') {
            $x = ($img_w/2 - ($mark_w/2) + $this->x());
            $y = ($img_h - $mark_h - $this->y());
        } elseif ($gravity == 'se') {
            $x = ($img_w - $mark_w - $this->x());
            $y = ($img_h - $mark_h - $this->y());
        }
        
        $x = max(0, $x);
        $x = min(($img_w - $mark_w), $x);
        $y = max(0, $y);
        $y = min(($img_h - $mark_h), $y);

        $composite_mode = Imagick::COMPOSITE_MULTIPLY;
        $this->image()->imagick()->compositeImage($watermark->imagick(), $composite_mode, $x, $y);

        return $this;
    }
}
