<?php

namespace Charcoal\Image\Effect;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Image\AbstractEffect as AbstractEffect;

/**
* Convert an image to grayscale colorspace
*/
abstract class AbstractGrayscaleEffect extends AbstractEffect
{

    /**
    * @param array $data
    * @return AbstractGrayscaleEffect Chainable
    */
    public function set_data(array $data)
    {
        return $this;
    }

    /**
    * @param array $data
    * @return AbstractGrayscaleEffect Chainable
    */
    //abstract public function process(array $data = null);

}
