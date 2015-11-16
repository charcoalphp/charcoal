<?php

namespace Charcoal\Image;

// Dependencies from `charcoal-factory` module
use \Charcoal\Factory\MapFactory;

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
            //'gd'            => '\Charcoal\Image\Gd\GdImage'
            // gmagick'       => '\Charcoal\Image\Gmagick\GmagickIimage'
        ];
    }
}
