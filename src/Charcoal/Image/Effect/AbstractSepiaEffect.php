<?php

namespace Charcoal\Image\Effect;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Image\AbstractEffect as AbstractEffect;

/**
* Convert an image to grayscale colorspace
*/
abstract class AbstractSepiaEffect extends AbstractEffect
{

    /**
    * @var float $_threshold
    */
    private $_threshold = 75;

    /**
    * @param array $data
    * @return AbstractSepiaEffect Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['threshold']) && $data['threshold'] !== null) {
            $this->set_threshold($data['threshold']);
        }
        return $this;
    }

    /**
    * @param float $threshold
    * @throws InvalidArgumentException
    * @return AbstractSepiaEffect Chainable
    */
    public function set_threshold($threshold)
    {
        $max = 255; // @todo: QuantumRange
        if (!is_numeric($threshold) || ($threshold < 0) || ($threshold > $max)) {
            throw new InvalidArgumentException(sprintf('Threshold must be a number between 0 and %s', $max));
        }
        $this->_threshold = (float)$threshold;
        return $this;
    }

    /**
    * @return float
    */
    public function threshold()
    {
        return $this->_threshold;
    }
}
