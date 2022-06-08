<?php

namespace Charcoal\Image\Effect;

use \InvalidArgumentException;

use \Charcoal\Image\AbstractEffect;

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
     * @param float $radius The blur radius value.
     * @throws InvalidArgumentException If the argument is not a valid number.
     * @return AbstractBlurEffect Chainable
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
     * @param float $sigma The blur sigma value.
     * @throws InvalidArgumentException If the argument is not a valid number.
     * @return AbstractBlurEffect Chainable
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
     * @param string $mode The blur mode.
     * @throws InvalidArgumentException If the argument is not a valid blur mode.
     * @return AbstractBlurEffect Chainable
     */
    public function setMode($mode)
    {
        $allowedModes = [
            'standard',
            'adaptive',
            'gaussian',
            'motion',
            'radial',
            'soft'
        ];
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
     * @param string $channel The blur channel.
     * @throws InvalidArgumentException If the argument is not a valid image channel.
     * @return AbstractBlurEffect Chainable
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
     * @param float $angle The blur angle.
     * @throws InvalidArgumentException If the argument is not a valid number.
     * @return AbstractBlurEffect Chainable
     */
    public function setAngle($angle)
    {
        if (!is_numeric($angle)) {
            throw new InvalidArgumentException(
                'Angle must be a numeric value, in degrees'
            );
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
     * @param array $data The effect data, if available.
     * @return AbstractBlurEffect Chainable
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

            case 'gaussian':
                return $this->processGaussian();

            case 'motion':
                return $this->processMotion();

            case 'radial':
                return $this->processRadial();

            case 'soft':
                return $this->processSoft();

            case 'standard':
            default:
                return $this->processStandard();
        }
    }

    /**
     * @return AbstractBlurEffect Chainable
     */
    abstract public function processAdaptive();

    /**
     * @return AbstractBlurEffect Chainable
     */
    abstract public function processGaussian();

    /**
     * @return AbstractBlurEffect Chainable
     */
    abstract public function processMotion();

    /**
     * @return AbstractBlurEffect Chainable
     */
    abstract public function processRadial();

    /**
     * @return AbstractBlurEffect Chainable
     */
    abstract public function processSoft();

    /**
     * @return AbstractBlurEffect Chainable
     */
    abstract public function processStandard();
}
