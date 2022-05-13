<?php

namespace Charcoal\Image;

// From 'charcoal-factory'
use \Charcoal\Factory\AbstractFactory;

/**
 * Create image class from image processor type.
 */
class ImageFactory extends AbstractFactory
{
    /**
     * @param array $data Constructor dependencies.
     */
    public function __construct(array $data = null)
    {
        if (isset($data['map'])) {
            $data['map'] = array_merge($this->defaultMap(), $data['map']);
        } else {
            $data['map'] = $this->defaultMap();
        }

        parent::__construct($data);
    }

    /**
     * @return array
     */
    protected function defaultMap()
    {
        return [
            'imagick'     => '\Charcoal\Image\Imagick\ImagickImage',
            'imagemagick' => '\Charcoal\Image\Imagemagick\ImagemagickImage'
        ];
    }
}
