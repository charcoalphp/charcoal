<?php

namespace Charcoal\Image\Effect;

use \InvalidArgumentException;

use \Charcoal\Image\AbstractEffect as AbstractEffect;

/**
* Sharpen an image, with a simple sharpen algorithm or unsharp mask options
*/
abstract class AbstractSharpenEffect extends AbstractEffect
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
    * Amount (or _gain_) to unsharp. Only used in `unsharp` mode
    * @var float $amount
    */
    private $amount = 1;

    /**
    * Threshold. Ony used in `unsharp` mode
    */
    private $threshold = 0.05;

    /**
    * @var boolean $mode
    */
    private $mode = 'standard';

    /**
    * @var integer $channel
    */
    private $channel = 'all';


    /**
    * @param float $radius
    * @throws InvalidArgumentException
    * @return Blur Chainable
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
    * @param float $sigma
    * @throws InvalidArgumentException
    * @return Blur Chainable
    */
    public function set_sigma($sigma)
    {
        if (!is_numeric($sigma) || ($sigma < 0)) {
            throw new InvalidArgumentException('Sigma value must be a float (greater than 0)');
        }
        $this->sigma = $sigma;
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
    * @param float $amount
    * @throws InvalidArgumentException
    * @return AbstractThresholdEffect Chainable
    */
    public function set_amount($amount)
    {
        if (!is_numeric($amount) || ($amount < 0)) {
            throw new InvalidArgumentException('Threshold must be a float (greater than 0)');
        }
         $this->amount = (float)$amount;
         return $this;
    }

    /**
    * @return float
    */
    public function amount()
    {
        return $this->amount;
    }
    
    /**
    * @param float $threshold
    * @throws InvalidArgumentException
    * @return AbstractThresholdEffect Chainable
    */
    public function set_threshold($threshold)
    {
        if (!is_numeric($threshold) || ($threshold < 0)) {
            throw new InvalidArgumentException('Threshold must be a float (greater than 0)');
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

    /**
    * @param string $mode
    * @throws InvalidArgumentException
    * @return Blur Chainable
    */
    public function set_mode($mode)
    {
        $allowed_modes = ['standard', 'adaptive', 'unsharp'];
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
    * @param array $data
    * @return AbstractSharpenEffect Chainable
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

            case 'unsharp':
                return $this->process_unsharp();
            //break;

            case 'standard':
            default:
                return $this->process_standard();
            //break;
        }
    }

    /**
    * @return AbstractSharpenEffect Chainable
    */
    abstract public function process_adaptive();

    /**
    * @return AbstractSharpenEffect Chainable
    */
    abstract public function process_unsharp();

    /**
    * @return AbstractSharpenEffect Chainable
    */
    abstract public function process_standard();
}
