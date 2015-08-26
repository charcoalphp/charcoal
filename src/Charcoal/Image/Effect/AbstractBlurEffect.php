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
    * @var float $radius
    */
    private $radius = 0;
    /**
    * @var float $sigma
    */
    private $sigma = 1;
    /**
    * @var string $mode
    */
    private $mode = 'standard';

    /**
    * @var string $channel
    */
    private $channel = 'all';

    /**
    * The angle is only used for "motion" and "radial" modes
    * @var float $angle
    */
    private $angle = 0;

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
         $this->radius = (float)$radius;
         return $this;
    }

    /**
    * @return float
    */
    public function radius()
    {
        return $this->radius;
    }

    /**
    * @param float] $sigma
    * @throws InvalidArgumentException
    * @return AbstractBlurEffect Chainable
    */
    public function set_sigma($sigma)
    {
        if (!is_numeric($sigma) || ($sigma < 0)) {
            throw new InvalidArgumentException('Sigma value must be a float (greater than 0)');
        }
        $this->sigma = (float)$sigma;
        return $this;
    }

    /**
    * @return float
    */
    public function sigma()
    {
        return $this->sigma;
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
        $this->mode = $mode;
        return $this;
    }

    /**
    * @return string
    */
    public function mode()
    {
        return $this->mode;
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
        $this->channel = $channel;
        return $this;
    }

    /**
    * @return string
    */
    public function channel()
    {
        return $this->channel;
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
        $this->angle = (float)$angle;
        return $this;
    }

    /**
    * @return float
    */
    public function angle()
    {
        return $this->angle;
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
    * @return AbstractBlurEffect Chainable
    */
    abstract public function process_adaptive();
    
    /**
    * @return AbstractBlurEffect Chainable
    */
    abstract public function process_gaussian();
    
    /**
    * @return AbstractBlurEffect Chainable
    */
    abstract public function process_motion();
    
    /**
    * @return AbstractBlurEffect Chainable
    */
    abstract public function process_radial();
    
    /**
    * @return AbstractBlurEffect Chainable
    */
    abstract public function process_soft();
    
    /**
    * @return AbstractBlurEffect Chainable
    */
    abstract public function process_standard();
}
