<?php

namespace Charcoal\Image\Imagick;

use Exception;
use InvalidArgumentException;
use Imagick;
use Charcoal\Image\AbstractImage;

/**
 *
 */
class ImagickImage extends AbstractImage
{
    private readonly \Imagick $imagick;

    /**
     * @throws Exception If imagick driver can not be loaded.
     */
    public function __construct()
    {
        if (!extension_loaded('imagick') || !class_exists('Imagick')) {
            throw new Exception(
                'The Imagick PHP extension is required.'
            );
        }
        $this->imagick = new Imagick();
    }

    public function driverType(): string
    {
        return 'imagick';
    }

    public function imagick(): \Imagick
    {
        return $this->imagick;
    }

    /**
     * Create a blank canvas of a given size, with a given background color.
     *
     * @param integer $width  Image size, in pixels.
     * @param integer $height Image height, in pixels.
     * @param string  $color  Default to transparent.
     * @throws InvalidArgumentException If the size arguments are not valid, positive integers.
     */
    public function create($width, $height, $color = 'rgb(100%, 100%, 100%, 0)'): static
    {
        if (!is_numeric($width) || $width < 1) {
            throw new InvalidArgumentException(
                'Width must be an integer of at least 1 pixel'
            );
        }
        if (!is_numeric($height) || $height < 1) {
            throw new InvalidArgumentException(
                'Height must be an integer of at least 1 pixel'
            );
        }
        $this->imagick()->newImage((int)$width, (int)$height, $color);
        return $this;
    }

    /**
     * Open an image file
     *
     * @param string $source The source path / filename.
     * @throws InvalidArgumentException If the source argument is not a string.
     */
    public function open($source = null): static
    {
        if ($source !== null && !is_string($source)) {
            throw new InvalidArgumentException(
                'Source must be a string'
            );
        }

        $source = $source ?: $this->source();
        if (parse_url($source, PHP_URL_HOST)) {
            $handle = fopen($source, 'rb');
            $this->imagick()->readImageFile($handle);
        } else {
            $this->imagick()->readImage($source);
        }

        return $this;
    }

    /**
     * Save an image to a target.
     * If no target is set, the original source will be owerwritten
     *
     * @param string $target The target path / filename.
     * @throws InvalidArgumentException If the target argument is not a string.
     */
    public function save($target = null): static
    {
        if ($target !== null && !is_string($target)) {
            throw new InvalidArgumentException(
                'Target must be a string (file path)'
            );
        }

        $target  = $target ?: $this->target();
        $fileExt = pathinfo($target, PATHINFO_EXTENSION);

        $this->imagick()->setImageFormat($fileExt);
        $this->imagick()->writeImage($target);

        return $this;
    }

    /**
     * Get the image's width, in pixels
     */
    public function width(): int
    {
        return $this->imagick()->getImageWidth();
    }

    /**
     * Get the image's height, in pixels
     */
    public function height(): int
    {
        return $this->imagick()->getImageHeight();
    }

    /**
     * Convert a channel name (string) to an `Imagick::CHANNEL_*` constant (integer)
     *
     * @param string $channel The standard "channel" string.
     * @throws InvalidArgumentException If the channel argument is not a valid channel.
     */
    public function imagickChannel($channel): int
    {
        $channelMap = [
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
        if (!isset($channelMap[$channel])) {
            throw new InvalidArgumentException(
                'Invalid channel'
            );
        }
        return $channelMap[$channel];
    }

    /**
     * Convert a gravity name (string) to an `Imagick::GRAVITY_*` constant (integer)
     *
     * @param string $gravity The standard gravity name.
     * @throws InvalidArgumentException If the gravity argument is not a valid gravity type.
     */
    public function imagickGravity($gravity): int
    {
        $gravityMap = [
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
        if (!isset($gravityMap[$gravity])) {
            throw new InvalidArgumentException(
                'Invalid gravity'
            );
        }
        return $gravityMap[$gravity];
    }
}
