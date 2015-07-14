<?php

namespace Charcoal\Image\Effect;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Image\AbstractEffect as AbstractEffect;

/**
* Modifies an image's colors in the special HSL (hue-saturation-luminance) colorspace.
*/
abstract class AbstractModulateEffect extends AbstractEffect
{
    /**
    * The color tint (-100 to 100)
    * @var float $_hue
    */
    private $_hue = 0;

    /**
    * The color intensity (-100 to 100)
    * @var float $_saturation
    */
    private $_saturation = 0;

    /**
    * The brightness (-100 to 100)
    * @var float $_luminance
    */
    private $_luminance = 0;
    
    /**
    * @param array $data
    * @return AbstractModulateEffect Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['hue']) && $data['hue'] !== null) {
            $this->set_hue($data['hue']);
        }
        if (isset($data['saturation']) && $data['saturation'] !== null) {
            $this->set_saturation($data['saturation']);
        }
        if (isset($data['luminance']) && $data['luminance'] !== null) {
            $this->set_luminance($data['luminance']);
        }
        return $this;
    }

    /**
    * @param float $hue
    * @throws InvalidArgumentException
    * @return AbstractModulateEffect Chainable
    */
    public function set_hue($hue)
    {
        if (!is_numeric($hue) || ($hue < -100) || ($hue > 100)) {
            throw new InvalidArgumentException('Hue (color tint) must be a float between 0 and 200');
        }
        $this->_hue = (float)$hue;
        return $this;
    }

    /**
    * @return float
    */
    public function hue()
    {
        return $this->_hue;
    }

    /**
    * @param float $saturation
    * @throws InvalidArgumentException
    * @return AbstractModulateEffect Chainable
    */
    public function set_saturation($saturation)
    {
        if (!is_numeric($saturation) || ($saturation < -100) || ($saturation > 100)) {
            throw new InvalidArgumentException('Saturation (color intensity) must be a float between 0 and 200');
        }
        $this->_saturation = (float)$saturation;
        return $this;
    }

    /**
    * @return float
    */
    public function saturation()
    {
        return $this->_saturation;
    }

    /**
    * @param float $luminance
    * @throws InvalidArgumentException
    * @return AbstractModulateEffect Chainable
    */
    public function set_luminance($luminance)
    {
        if (!is_numeric($luminance) || ($luminance < -100) || ($luminance > 100)) {
            throw new InvalidArgumentException('Luminance (brightness) must be a float between 0 and 200');
        }
        $this->_luminance = (float)$luminance;
        return $this;
    }

    /**
    * @return float
    */
    public function luminance()
    {
        return $this->_luminance;
    }
}
