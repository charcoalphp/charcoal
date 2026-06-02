<?php

namespace Charcoal\Image;

// From 'charcoal-factory'
use Charcoal\Factory\AbstractFactory;

/**
 * Create image class from image processor type.
 */
class ImageFactory extends AbstractFactory
{
    /**
     * @param array $data Constructor dependencies.
     */
    public function __construct(?array $data = null)
    {
        $data['map'] = isset($data['map']) ? array_merge($this->defaultMap(), $data['map']) : $this->defaultMap();

        parent::__construct($data);
    }

    protected function defaultMap(): array
    {
        return [
            'imagick'     => \Charcoal\Image\Imagick\ImagickImage::class,
            'imagemagick' => \Charcoal\Image\Imagemagick\ImagemagickImage::class
        ];
    }
}
