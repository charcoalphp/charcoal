<?php

namespace Charcoal\Image\Effect;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Image\AbstractEffect as AbstractEffect;

/**
* Blur the image
*/
abstract class AbstractBlurEffect extends AbstractEffect
{
    
    /**
    * @var float $_radius
    */
    private $_radius = 0;
    /**
    * @var float $_sigma
    */
    private $_sigma = 1;
    /**
    * @var boolean $_mode
    */
    private $_mode = 'standard';

    /**
    * @var integer $_channel
    */
    private $_channel = 'all';

    /**
    * The angle is only used for "motion" and "radial" modes
    * @var float $_angle
    */
    private $_angle = 0;

    /**
    * @param array $data
    * @return AbstractBlurEffect Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['radius']) && $data['radius'] !== null) {
            $this->set_radius($data['radius']);
        }
        if (isset($data['sigma']) && $data['sigma'] !== null) {
            $this->set_sigma($data['sigma']);
        }
        if (isset($data['mode']) && $data['mode'] !== null) {
            $this->set_mode($data['mode']);
        }
        if (isset($data['channel']) && $data['channel'] !== null) {
            $this->set_channel($data['channel']);
        }
        if (isset($data['angle']) && $data['angle'] !== null) {
            $this->set_angle($data['angle']);
        }
        return $this;
    }

    /**
    * @param float $radius
    * @throws InvalidArgumentException
    * @return AbstractBlurEffect Chainable
    */
    public function set_radius($radius)
    {
        if (!is_numeric($radius) || ($radius < 0)) {
            throw new InvalidArgumentException('Radius must be a float (greater than 0)');
        }
         $this->_radius = (float)$radius;
         return $this;
    }

    /**
    * @return float
    */
    public function radius()
    {
        return $this->_radius;
    }

    /**
    * @param float $sigma
    * @throws InvalidArgumentException
    * @return AbstractBlurEffect Chainable
    */
    public function set_sigma($sigma)
    {
        if (!is_numeric($sigma) || ($sigma < 0)) {
            throw new InvalidArgumentException('Sigma value must be a float (greater than 0)');
        }
        $this->_sigma = $sigma;
        return $this;
    }

    /**
    * @return float
    */
    public function sigma()
    {
        return $this->_sigma;
    }

    /**
    * @param string $mode
    * @throws InvalidArgumentException
    * @return AbstractBlurEffect Chainable
    */
    public function set_mode($mode)
    {
        $allowed_modes = ['standard', 'adaptive', 'gaussian', 'motion', 'radial', 'soft'];
        if (!in_array($mode, $allowed_modes)) {
            throw new InvalidArgumentException(sprintf('Mode %s is not an allowed blur mode', $mode));
        }
        $this->_mode = $mode;
        return $this;
    }

    /**
    * @return string
    */
    public function mode()
    {
        return $this->_mode;
    }

    /**
    * @param string $channel
    * @throws InvalidArgumentException
    * @return AbstractBlurEffect Chainable
    */
    public function set_channel($channel)
    {
        if (!in_array($channel, $this->image()->available_channels())) {
            throw new InvalidArgumentException('Channel is not valid');
        }
        $this->_channel = $channel;
        return $this;
    }

    /**
    * @return string
    */
    public function channel()
    {
        return $this->_channel;
    }

    /**
    * @param float $angle
    * @throws InvalidArgumentException
    * @return AbstractBlurEffect Chainable
    */
    public function set_angle($angle)
    {
        if (!is_numeric($angle)) {
            throw new InvalidArgumentException('Angle must be a numeric value, in degrees');
        }
        $this->_angle = (float)$angle;
        return $this;
    }

    /**
    * @return float
    */
    public function angle()
    {
        return $this->_angle;
    }

    /**
    * @param array $data
    * @return AbstractBlurEffect Chainable
    */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }

        $mode = $this->mode();
        switch ($mode) {
            case 'adaptive':
                return $this->process_adaptive();
            //break;

            case 'gaussian':
                return $this->process_gaussian();
            //break;

            case 'motion':
                return $this->process_motion();
            //break;

            case 'radial':
                return $this->process_radial();
            //break;

            case 'soft':
                return $this->process_soft();
            //break;

            case 'standard':
            default:
                return $this->process_standard();
            //break;
        }
    }

    /**
    * @return AbstractBlurEffectChainable
    */
    abstract public function process_adaptive();
    
    /**
    * @return AbstractBlurEffectChainable
    */
    abstract public function process_gaussian();
    
    /**
    * @return AbstractBlurEffectChainable
    */
    abstract public function process_motion();
    
    /**
    * @return AbstractBlurEffectChainable
    */
    abstract public function process_radial();
    
    /**
    * @return AbstractBlurEffectChainable
    */
    abstract public function process_soft();
    
    /**
    * @return AbstractBlurEffectChainable
    */
    abstract public function process_standard();
}
