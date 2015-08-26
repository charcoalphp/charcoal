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
    * @param float $threshold
    * @throws InvalidArgumentException
    * @return AbstractSepiaEffect Chainable
    */
    public function set_threshold($threshold)
    {
        $max = 255; // @todo: QuantumRange
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
