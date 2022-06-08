<?php

namespace Charcoal\Image\Effect;

use InvalidArgumentException;

use Charcoal\Image\AbstractEffect;

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
     * @var float $threshold
     */
    private $threshold = 0.05;

    /**
     * @var string $mode
     */
    private $mode = 'standard';

    /**
     * @var string $channel
     */
    private $channel = 'all';


    /**
     * @param float $radius The sharpen radius value.
     * @throws InvalidArgumentException If the radius argument is not numeric or lower than 0.
     * @return self
     */
    public function setRadius($radius)
    {
        if (!is_numeric($radius) || ($radius < 0)) {
            throw new InvalidArgumentException(
                'Radius must be a float (greater than 0)'
            );
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
     * @param float $sigma The sharpen sigma value.
     * @throws InvalidArgumentException If the ssigma value is not numeric or lower than 0.
     * @return self
     */
    public function setSigma($sigma)
    {
        if (!is_numeric($sigma) || ($sigma < 0)) {
            throw new InvalidArgumentException(
                'Sigma value must be a float (greater than 0)'
            );
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
     * @param float $amount The sharpen amount.
     * @throws InvalidArgumentException If the amount argument is not numeric or lower than 0.
     * @return self
     */
    public function setAmount($amount)
    {
        if (!is_numeric($amount) || ($amount < 0)) {
            throw new InvalidArgumentException(
                'Threshold must be a float (greater than 0)'
            );
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
     * @param float $threshold The sharpen threshold value.
     * @throws InvalidArgumentException If the threshold argumnet is not numeric or lower than 0.
     * @return self
     */
    public function setThreshold($threshold)
    {
        if (!is_numeric($threshold) || ($threshold < 0)) {
            throw new InvalidArgumentException(
                'Threshold must be a float (greater than 0)'
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

    /**
     * @param string $mode The sharpen mode.
     * @throws InvalidArgumentException If the mode argument is not a valid sharpen mode.
     * @return self
     */
    public function setMode($mode)
    {
        $allowedModes = ['standard', 'adaptive', 'unsharp'];
        if (!in_array($mode, $allowedModes)) {
            throw new InvalidArgumentException(
                sprintf('Mode %s is not an allowed blur mode', $mode)
            );
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
     * @param string $channel The sharpen channel.
     * @throws InvalidArgumentException If the channel argument is not a valid channel.
     * @return self
     */
    public function setChannel($channel)
    {
        if (!in_array($channel, $this->image()->availableChannels())) {
            throw new InvalidArgumentException(
                'Channel is not valid'
            );
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
     * @param array $data The effect data, if available.
     * @return self
     */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }

        $mode = $this->mode();
        switch ($mode) {
            case 'adaptive':
                return $this->processAdaptive();

            case 'unsharp':
                return $this->processUnsharp();

            case 'standard':
            default:
                return $this->processStandard();
        }
    }

    /**
     * @return self
     */
    abstract public function processAdaptive();

    /**
     * @return self
     */
    abstract public function processUnsharp();

    /**
     * @return self
     */
    abstract public function processStandard();
}
