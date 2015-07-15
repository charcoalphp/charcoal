<?php

namespace Charcoal\Image\Effect;

use \InvalidArgumentException;

use \Charcoal\Image\AbstractEffect as AbstractEffect;

/**
* Convert the image to monochrome (black and white)
*/
abstract class AbstractThresholdEffect extends AbstractEffect
{
    
    /**
    * @var float $_threshold
    */
    private $_threshold = 0.5;

    /**
    * @param array $data
    * @return AbstractThresholdEffect Chainable
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
    * @return AbstractThresholdEffect Chainable
    */
    public function set_threshold($threshold)
    {
        if (!is_numeric($threshold) || ($threshold < 0) || ($threshold > 1)) {
            throw new InvalidArgumentException('Threshold must be a float between 0 and 1.');
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
