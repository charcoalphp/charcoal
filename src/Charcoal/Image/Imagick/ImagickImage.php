<?php

namespace Charcoal\Image\Imagick;

use \Exception as Exception;
use \InvalidArgumentException as InvalidArgumentException;
use \Imagick as Imagick;

use \Charcoal\Image\AbstractImage as AbstractImage;

/**
*
*/
class ImagickImage extends AbstractImage
{
    private $_imagick;

    /**
    * @throws Exception
    */
    public function __construct()
    {
        if (!extension_loaded('imagick') || !class_exists('Imagick')) {
            throw new Exception('The Imagick PHP extension is required.');
        }

        // @todo: also validate imagick version
    }

    /**
    * @return string
    */
    public function driver_type()
    {
        return 'imagick';
    }

    /**
    * @return Imagick
    */
    public function imagick()
    {
        if ($this->_imagick === null) {
            $this->_imagick = new Imagick();
        }
        return $this->_imagick;
    }

    /**
    * Create a blank canvas of a given size, with a given background color.
    *
    * @param integer $width  Image size, in pixels
    * @param integer $height Image height, in pixels
    * @param string  $color  Default to transparent.
    * @throws InvalidArgumentException
    * @return Image Chainable
    */
    public function create($width, $height, $color = 'rgb(100%, 100%, 100%, 0)')
    {
        if (!is_int($width) || $width < 1) {
            throw new InvalidArgumentException('Width must be an integer of at least 1 pixel');
        }
        if (!is_int($height) || $height < 1) {
            throw new InvalidArgumentException('Height must be an integer of at least 1 pixel');
        }
        $this->imagick()->newImage($width, $height, $color);
        return $this;
    }

    /**
    * Open an image file
    *
    * @param string $source The source path / filename
    * @throws InvalidArgumentException
    * @return Image Chainable
    */
    public function open($source = null)
    {
        if ($source !== null && !is_string($source)) {
            throw new InvalidArgumentException('Source must be a string');
        }
        $source = ($source) ? $source : $this->source();
        $this->imagick()->readImage($source);
        return $this;
    }

    /**
    * Save an image to a target.
    * If no target is set, the original source will be owerwritten
    *
    * @param string $target The target path / filename
    * @throws InvalidArgumentException
    * @return Image Chainable
    */
    public function save($target = null)
    {
        if ($target !== null && !is_string($target)) {
            throw new InvalidArgumentException('Target must be a string');
        }
        $target = ($target) ? $target : $this->target();

        $file_ext = pathinfo($target, PATHINFO_EXTENSION);

        $this->imagick()->setImageFormat($file_ext);

        $this->imagick()->writeImage($target);
        return $this;
    }

    /**
    * Get the image's width, in pixels
    *
    * @return integer
    */
    public function width()
    {
        return $this->imagick()->getImageWidth();
    }

    /**
    * Get the image's height, in pixels
    *
    * @return integer
    */
    public function height()
    {
        return $this->imagick()->getImageHeight();
    }

    /**
    * Convert a channel name (string) to an `Imagick::CHANNEL_*` constant (integer)
    *
    * @param string $channel
    * @throws InvalidArgumentException
    * @return integer
    * @todo Rename to `convert_channel()`
    */
    public function imagick_channel($channel)
    {
        $channel_map = [
            // RGB
            'red'       => Imagick::CHANNEL_RED,
            'green'     => Imagick::CHANNEL_GREEN,
            'blue'      => Imagick::CHANNEL_BLUE,
            // CMYK
            'cyan'      => Imagick::CHANNEL_CYAN,
            'magenta'   => Imagick::CHANNEL_MAGENTA,
            'yellow'    => Imagick::CHANNEL_YELLOW,
            'black'     => Imagick::CHANNEL_BLACK,
            // Others
            'all'       => Imagick::CHANNEL_ALL,
            'alpha'     => Imagick::CHANNEL_ALPHA,
            'opacity'   => Imagick::CHANNEL_RED,
            'gray'      => Imagick::CHANNEL_GRAY
        ];
        if (!isset($channel_map[$channel])) {
            throw new InvalidArgumentException('Invalid channel');
        }
        return $channel_map[$channel];
    }

    /**
    * Convert a gravity name (string) to an `Imagick::GRAVITY_*` constant (integer)
    *
    * @param string $gravity
    * @throws InvalidArgumentException
    * @return integer
    */
    public function imagick_gravity($gravity)
    {
        $gravity_map = [
            'center'    => Imagick::GRAVITY_CENTER,
            'n'         => Imagick::GRAVITY_NORTH,
            's'         => Imagick::GRAVITY_SOUTH,
            'e'         => Imagick::GRAVITY_EAST,
            'w'         => Imagick::GRAVITY_WEST,
            'ne'        => Imagick::GRAVITY_NORTHEAST,
            'nw'        => Imagick::GRAVITY_NORTHWEST,
            'se'        => Imagick::GRAVITY_SOUTHEAST,
            'sw'        => Imagick::GRAVITY_SOUTHWEST
        ];
        if (!isset($gravity_map[$gravity])) {
            throw new InvalidArgumentException('Invalid gravity');
        }
        return $gravity_map[$gravity];
    }
}
