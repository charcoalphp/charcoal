<?php

namespace Charcoal\Image\Imagemagick\Effect;

use Charcoal\Image\Effect\AbstractResizeEffect;

/**
 * Reisze Effect for the Imagemagick driver.
 *
 * See {@link http://www.imagemagick.org/script/command-line-processing.php#geometry Image Geometry}
 * for complete details about the geometry argument.
 */
class ImagemagickResizeEffect extends AbstractResizeEffect
{
    /**
     * @param  integer $width   The target width.
     * @param  integer $height  The target height.
     * @param  boolean $bestFit The "best_fit" flag.
     * @return void
     */
    protected function doResize($width, $height, $bestFit = false)
    {
        if ($this->adaptive()) {
            $option = '-adaptive-resize';
        } else {
            $option = '-resize';
        }

        $size = $this->size();
        if ($size) {
            $params = [ $option.' "'.$size.'"' ];
        } else {
            if ($width === 0 && $height === 0) {
                return;
            }

            $params = [
                '-gravity "'.$this->gravity().'"',
                '-background "'.$this->backgroundColor().'"'
            ];

            if ($width === 0) {
                $width = '';
            }

            if ($height === 0) {
                $height = '';
            }

            $size = $width.'x'.$height;
            if ($bestFit) {
                $params[] = $option.' "'.$size.'^"';
                $params[] = '-extent '.$size;
            } else {
                $params[] = $option.' '.$size;
            }
        }

        $this->image()->applyCmd(implode(' ', $params));
    }
}
