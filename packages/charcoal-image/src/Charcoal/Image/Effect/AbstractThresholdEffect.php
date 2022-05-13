<?php

namespace Charcoal\Image\Effect;

use \InvalidArgumentException;

use \Charcoal\Image\AbstractEffect;

/**
 * Convert the image to monochrome (black and white)
 */
abstract class AbstractThresholdEffect extends AbstractEffect
{

    /**
     * @var float $threshold
     */
    private $threshold = 0.5;

    /**
     * @param float $threshold The threshold value.
     * @throws InvalidArgumentException If the threshold is not numeric or lower than zero / greater than 1.
     * @return AbstractThresholdEffect Chainable
     */
    public function setThreshold($threshold)
    {
        if (!is_numeric($threshold) || ($threshold < 0) || ($threshold > 1)) {
            throw new InvalidArgumentException(
                'Threshold must be a float between 0 and 1.'
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
