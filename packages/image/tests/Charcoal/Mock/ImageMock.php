<?php

namespace Charcoal\Tests\Mock;

use Charcoal\Image\AbstractImage;

class ImageMock extends AbstractImage
{

    /**
     * @inheritDoc
     */
    public function driverType()
    {
        return 'imagick';
    }

    /**
     * @inheritDoc
     */
    public function create($width, $height, $color = 'rgb(100%, 100%, 100%, 0)')
    {
        // TODO: Implement create() method.
    }

    /**
     * @inheritDoc
     */
    public function open($source = null)
    {
        // TODO: Implement open() method.
    }

    /**
     * @inheritDoc
     */
    public function save($target = null)
    {
        // TODO: Implement save() method.
    }

    /**
     * @inheritDoc
     */
    public function width()
    {
        // TODO: Implement width() method.
    }

    /**
     * @inheritDoc
     */
    public function height()
    {
        // TODO: Implement height() method.
    }
}
