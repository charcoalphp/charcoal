<?php

namespace Charcoal\Image\Effect;

use InvalidArgumentException;
use Charcoal\Image\AbstractEffect;

/**
 * Format (or colorize) the image with a certain color.
 */
abstract class AbstractFormatEffect extends AbstractEffect
{
    protected $format;

    public const ACCEPTED_FORMAT = [ 'webp', 'jpg', 'jpeg' ];

    /**
     * Must be one of the accepted format
     *
     * @var string $format
     * @throws InvalidArgumentException If the format is not supported
     */
    public function setFormat(string $format)
    {
        if (!in_array($format, static::ACCEPTED_FORMAT)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid image format provided. Must be one of %s. %s provided.',
                    implode(',', static::ACCEPTED_FORMAT),
                    $format
                )
            );
        }
        $this->format = $format;
        return $this;
    }

    public function format()
    {
        return $this->format;
    }
}
