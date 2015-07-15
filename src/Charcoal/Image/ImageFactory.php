<?php

namespace Charcoal\Image;

// File copied from `charcoal-core`
use \Charcoal\Image\AbstractFactory as AbstractFactory;

class ImageFactory extends AbstractFactory
{
    /**
    * @return array
    */
    public static function types()
    {
        return array_merge(
            parent::types(),
            [
            'imagick'       => '\Charcoal\Image\Imagick\ImagickImage',
            'imagemagick'   => '\Charcoal\Image\Imagemagick\ImagemagickImage'
            //'gd'            => '\Charcoal\Image\Gd\GdImage'
            // gmagick'       => '\Charcoal\Image\Gmagick\GmagickIimage'
            ]
        );
    }
}
