<?php

namespace Charcoal\Image\Effect;

use \InvalidArgumentException;

use \Charcoal\Image\AbstractEffect;

/**
 * Convert an image to grayscale colorspace
 */
abstract class AbstractSepiaEffect extends AbstractEffect
{

    /**
     * @var float $threshold
     */
    private $threshold = 75;


    /**
     * @param float $threshold The sepia threshold value.
     * @throws InvalidArgumentException If the threshold argument is not numeric, lower than 0 or greater than max.
     * @return AbstractSepiaEffect Chainable
     */
    public function setThreshold($threshold)
    {
        $max = 255;
        if (!is_numeric($threshold) || ($threshold < 0) || ($threshold > $max)) {
            throw new InvalidArgumentException(
                sprintf('Threshold must be a number between 0 and %s', $max)
            );
        }
        $this->threshold = (float)$threshold;
        return $this;
    }

    /**
     * @return float
     */
    public function threshold()
    {
        return $this->threshold;
    }
}
