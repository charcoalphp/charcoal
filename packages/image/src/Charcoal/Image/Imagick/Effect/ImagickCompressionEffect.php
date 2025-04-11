<?php

namespace Charcoal\Image\Imagick\Effect;

use Charcoal\Image\Effect\AbstractCompressionEffect;

/**
 * Compression Effect for the Imagick driver.
 */
class ImagickCompressionEffect extends AbstractCompressionEffect
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

        $target = $this->image()->target();
        $extension = strtolower($this->image()->imagick()->getImageFormat());

        $invalidExtensions = [ 'gif', 'bmp' ];


        // Abort when facing invalid extensions
        if (in_array($extension, $invalidExtensions)) {
            return $this;
        }

        // PNG compression is... different.
        // 0 is for the best quality, 9 for the worst quality
        // So we calculate the opposite quality (100 - quality) and extra that percent from 9
        // If quality = 100, changes to 0
        // Quality is rounded (ceil) to avoid any compression problems
        if ($extension === 'png') {
            $reverse = 100 - $this->quality();
            if ($reverse > 0) {
                $reverse = min(ceil(($reverse/100)*9), 9); // Make sure it doesn't get further than 9
            }

            if ($reverse <= 9) {
                $this->image()->imagick()->setOption('png:compression-level', $reverse);
                $this->image()->imagick()->setOption('png:compression-filter', '5');
            }
        }

        // Default image compression applies to jpg, jpeg, webp and tiff
        if (in_array($extension, ['jpg', 'jpeg', 'webp', 'tiff'])) {
            $this->image()->imagick()->setImageCompressionQuality($this->quality());
        }

        return $this;
    }
}
