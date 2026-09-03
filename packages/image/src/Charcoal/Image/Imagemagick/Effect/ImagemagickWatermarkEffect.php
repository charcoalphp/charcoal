<?php

namespace Charcoal\Image\Imagemagick\Effect;

use Charcoal\Image\Effect\AbstractWatermarkEffect;
use Charcoal\Image\ImageInterface;

/**
 * Watermark Effect for the Imagemagick driver.
 */
class ImagemagickWatermarkEffect extends AbstractWatermarkEffect
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

        if ($this->watermark() instanceof ImageInterface) {
            $out = '/tmp/_' . uniqid() . '.png';
            $this->watermark()->save($out);
            $width = $this->watermark()->width();
            $height = $this->watermark()->height();
            $watermark = $out;
        } else {
            $watermark = $this->watermark();
            $c = get_class($this->image());
            $w = new $c();
            $w->open($watermark);
            $width = $w->width();
            $height = $w->height();
        }

        $gravity = $this->image()->imagemagickGravity($this->gravity());
        $params  = [ '-gravity ' . escapeshellarg($gravity) ];

        $cmd = null;
        if ($this->image()->compositeCmd()) {
            $cmd = 'composite';
            $params[] = '-watermark 100% ' . escapeshellarg($watermark) . ' ' . escapeshellarg($this->image()->tmp());
        } else {
            $params[] = '-geometry +' . (int)$this->x() . '+' . (int)$this->y();
            // The watermark path is nested inside ImageMagick's own MVG single-quote
            // syntax (`'...'`), which sits inside the shell-quoted `-draw` argument;
            // strip any single quotes so it can't break out of the MVG quoting.
            $mvgWatermark = str_replace("'", '', $watermark);
            $params[] = '-draw ' . escapeshellarg(
                'image Multiply 0,0 ' . (int)$width . ',' . (int)$height . " '" . $mvgWatermark . "'"
            );
        }

        $this->image()->applyCmd(implode(' ', $params), $cmd);

        return $this;
    }
}
