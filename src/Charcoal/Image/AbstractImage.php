<?php

namespace Charcoal\Image;

use \Exception;
use \InvalidArgumentException;

use \Charcoal\Image\ImageInterface;
use \Charcoal\Image\EffectInterface;
use \Charcoal\Image\EffectFactory;

/**
 * Base Image class
 */
abstract class AbstractImage implements ImageInterface
{
    /**
     * @var string $source
     */
    protected $source;

    /**
     * @var string $target
     */
    protected $target;

    /**
     * @var array $Effects
     */
    protected $effects = [];

    /**
     * @var EffectFactory $effectFactory
     */
    private $effectFactory;


    /**
     * Magic: Attempt to load the effect of the called method name.
     *
     * Example, `$img->blur([ 'sigma' => 15 ]);` would create a "Blur" effect.
     *
     * @param  string $fxType The effect type.
     * @param  array  $data   The effect options.
     * @return ImageInterface Chainable
     */
    public function __call($fxType, array $data)
    {
        $data['type'] = $fxType;

        $fx = $this->createEffect($data);
        $fx->process();

        return $this;
    }

    /**
     * Safe effect factory getter.
     * If the factory doesn't exist, create it.
     *
     * @return EffectFactory
     */
    protected function effectFactory()
    {
        if ($this->effectFactory === null) {
            $this->effectFactory = new EffectFactory();
        }
        return $this->effectFactory;
    }

    /**
     * @return string
     */
    abstract public function driverType();


    /**
     * @param array $data The image data (source, target and effects).
     * @return ImageInterface Chainable
     */
    public function setData(array $data)
    {
        if (isset($data['source']) && $data['source'] !== null) {
            $this->setSource($data['source']);
        }
        if (isset($data['target']) && $data['target'] !== null) {
            $this->setTarget($data['target']);
        }
        if (isset($data['effects']) && $data['effects'] !== null) {
            $this->setEffects($data['effects']);
        }
        return $this;
    }

    /**
     * @param string $source The image source.
     * @throws InvalidArgumentException If the source argument is not a string.
     * @return ImageInterface Chainable
     */
    public function setSource($source)
    {
        if (!is_string($source)) {
            throw new InvalidArgumentException(
                'Source must be a string'
            );
        }
        $this->source = $source;
        return $this;
    }

    /**
     * @return string
     */
    public function source()
    {
        return $this->source;
    }

    /**
     * @param string $target The image target.
     * @throws InvalidArgumentException If the target argument is not a string.
     * @return ImageInterface Chainable
     */
    public function setTarget($target)
    {
        if (!is_string($target)) {
            throw new InvalidArgumentException(
                'Target must be a string'
            );
        }
        $this->target = $target;
        return $this;
    }

    /**
     * @return string
     */
    public function target()
    {
        return $this->target;
    }

    /**
     * @param array $effects The effects to apply.
     * @return ImageInterface Chainable
     */
    public function setEffects(array $effects)
    {
        $this->effects = [];
        foreach ($effects as $effect) {
            $this->addEffect($effect);
        }
        return $this;
    }

    /**
     * @return EffectInterface[] The array of `EffectInterface` effects.
     */
    public function effects()
    {
        return $this->effects;
    }

    /**
     * @param array|EffectInterface $effect The effect to add.
     * @return ImageInterface Chainable
     */
    public function addEffect($effect)
    {
        $fx = $this->createEffect($effect);
        $this->effects[] = $fx;
        return $this;
    }

    /**
     * @param array $effects Optional. The effects to process. If null, use in-memory's.
     * @return ImageInterface Chainable
     */
    public function process(array $effects = null)
    {
        if ($effects !== null) {
            $this->setEffects($effects);
        }

        $effects = $this->effects();
        foreach ($effects as $fx) {
            $fx->process();
        }
        return $this;
    }

    /**
     * @param array|EffectInterface $effect The effect to process.
     * @return ImageInterface Chainable
     */
    public function processEffect($effect)
    {
        $fx = $this->createEffect($effect);
        $fx->process();
        return $this;
    }

    /**
     * Create a blank canvas of a given size, with a given background color.
     *
     * @param integer $width  Image width, in pixels.
     * @param integer $height Image height, in pixels.
     * @param string  $color  Default to transparent.
     * @return ImageInterface Chainable
     */
    abstract public function create($width, $height, $color = 'rgb(100%, 100%, 100%, 0)');

    /**
     * Open an image file
     *
     * @param string $source The source path / filename.
     * @return ImageInterface Chainable
     */
    abstract public function open($source = null);

    /**
     * Save an image to a target.
     * If no target is set, the original source will be owerwritten
     *
     * @param string $target The target path / filename.
     * @return ImageInterface Chainable
     */
    abstract public function save($target = null);

    /**
     * Get the image's width, in pixels
     *
     * @return integer
     */
    abstract public function width();

    /**
     * Get the image's height, in pixels
     *
     * @return integer
     */
    abstract public function height();

    /**
     * Get the image's ratio (width / height)
     *
     * @return float
     * @throws Exception If the width or height has not been set.
     */
    public function ratio()
    {
        $width = $this->width();
        $height = $this->height();
        if (!$width || !$height) {
            throw new Exception(
                'Ratio can not be calculated. Invalid image dimensions'
            );
        }

        $ratio = ($width / $height);
        return $ratio;
    }

    /**
     * Orientation can be "horizontal", "vertical" or "square"
     *
     * @return string
     */
    public function orientation()
    {
        $ratio = $this->ratio();
        if ($ratio > 1) {
            return 'horizontal';
        } elseif ($ratio < 1) {
            return 'vertical';
        } else {
            return 'square';
        }
    }

    /**
     * @return boolean
     */
    public function isHorizontal()
    {
        return ($this->orientation() == 'horizontal');
    }

    /**
     * @return boolean
     */
    public function isVertical()
    {
        return ($this->orientation() == 'vertical');
    }

    /**
     * @return boolean
     */
    public function isSquare()
    {
        return ($this->orientation() == 'square');
    }

    /**
     * Ensure an EffectInterface object
     *
     * @param array|EffectInterface $effect The effect to create.
     * @throws InvalidArgumentException If the argument is not an array or Effect.
     * @return EffectInterface
     */
    protected function createEffect($effect)
    {
        if ($effect instanceof EffectInterface) {
            $effect->setImage($this);
            return $effect;
        } elseif (is_array($effect)) {
            if (!isset($effect['type'])) {
                throw new InvalidArgumentException(
                    'Effect parameter must define effect type'
                );
            }
            $fxType = $effect['type'];
            if (strstr($fxType, '/') === false) {
                // Core effects do not need to be namespaced
                $driver = $this->driverType();
                $fxType = 'charcoal/image/'.$driver.'/effect/'.$driver.'-'.$fxType.'-effect';
            }
            $imageEffect = $this->effectFactory()->create($fxType);
            $imageEffect->setImage($this);
            $imageEffect->setData($effect);
            return $imageEffect;
        } else {
            throw new InvalidArgumentException(
                'Effect must be an array or effect object'
            );
        }
    }

    /**
     * @return array
     */
    public function availableChannels()
    {
        return [
            // RGB
            'red',
            'green',
            'blue',
            // CMYK
            'cyan',
            'magenta',
            'yellow',
            'black',
            // Others
            'all',
            'alpha',
            'opacity',
            'gray'
        ];
    }

    /**
     * @return array
     */
    public function availableGravities()
    {
        return [
            'center',
            'n',
            's',
            'e',
            'w',
            'ne',
            'nw',
            'se',
            'sw'
        ];
    }

    /**
     * @return array
     */
    public function availableFilters()
    {
        // Unsupported: bartlett, bohman, kaiser, parzen and welsh.
        return [
            'blackman',
            'box',
            'catrom',
            'cubic',
            'hamming',
            'hanning',
            'hermite',
            'gaussian',
            'lanczos',
            'mitchell',
            'point',
            'quadratic',
            'triangle'
        ];
    }
}
