<?php

namespace Charcoal\Image;

// Dependencies from `charcoal-factory` module
use \Charcoal\Factory\MapFactory;

/**
 * Create image class from identifier.
 */
class ImageFactory extends MapFactory
{
    /**
     * @return array
     */
    public function map()
    {
        return [
            'imagick'       => '\Charcoal\Image\Imagick\ImagickImage',
            'imagemagick'   => '\Charcoal\Image\Imagemagick\ImagemagickImage'
        ];
    }
}
