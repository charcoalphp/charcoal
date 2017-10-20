<?php

namespace Charcoal\Image\Imagick\Effect;

use \Exception;
use \Imagick;

use \Charcoal\Image\Effect\AbstractWatermarkEffect;
use \Charcoal\Image\ImageInterface;

/**
 * Watermark Effect for the Imagick driver.
 */
class ImagickWatermarkEffect extends AbstractWatermarkEffect
{
    /**
     * @param array $data The effect data, if available.
     * @throws Exception If the image data is invalid.
     * @return self
     */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }

        $img = $this->image();
        $imageWidth = $img->width();
        $imageHeight = $img->height();

        if ($this->watermark() instanceof ImageInterface) {
            $watermark = $this->watermark();
        } else {
            $imgClass = get_class($img);
            $watermark = new $imgClass;
            $watermark->open($this->watermark());
        }

        if (($watermark->width() > $imageWidth) || ($watermark->height() > $imageHeight)) {
            // Scale-down watermark image, if necessary
            $watermark->resize([
                'mode'=>'best_fit',
                'width'=>$imageWidth,
                'height'=>$imageHeight
            ]);
        }

        $watermarkWidth = $watermark->width();
        $watermarkHeight = $watermark->height();

        $gravity = $this->gravity();
        if ($gravity == 'nw') {
            $x = $this->x();
            $y = $this->y();
        } elseif ($gravity == 'n') {
            $x = ($imageWidth/2 - ($watermarkWidth/2) + $this->x());
            $y = $this->y();
        } elseif ($gravity == 'ne') {
            $x = ($imageWidth - $watermarkHeight - $this->x());
            $y = $this->y();
        } elseif ($gravity == 'w') {
            $x = $this->x();
            $y = ($imageHeight/2 - ($watermarkHeight/2) + $this->y());
        } elseif ($gravity == 'center') {
            $x = ($imageWidth/2 - ($watermarkWidth/2) + $this->x());
            $y = ($imageHeight/2 - ($watermarkHeight/2) + $this->y());
        } elseif ($gravity == 'e') {
            $x = ($imageWidth - $watermarkWidth - $this->x());
            $y = ($imageHeight/2 - ($watermarkHeight/2) + $this->y());
        } elseif ($gravity == 'sw') {
            $x = $this->x();
            $y = ($imageHeight - $watermarkHeight - $this->y());
        } elseif ($gravity == 's') {
            $x = ($imageWidth/2 - ($watermarkWidth/2) + $this->x());
            $y = ($imageHeight - $watermarkHeight - $this->y());
        } elseif ($gravity == 'se') {
            $x = ($imageWidth - $watermarkWidth - $this->x());
            $y = ($imageHeight - $watermarkHeight - $this->y());
        } else {
            throw new Exception(
                'Invalid gravity'
            );
        }

        $x = max(0, $x);
        $x = min(($imageWidth - $watermarkWidth), $x);
        $y = max(0, $y);
        $y = min(($imageHeight - $watermarkHeight), $y);

        $compositeMode = Imagick::COMPOSITE_MULTIPLY;
        $this->image()->imagick()->compositeImage($watermark->imagick(), $compositeMode, $x, $y);

        return $this;
    }
}
