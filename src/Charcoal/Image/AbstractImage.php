<?php

namespace Charcoal\Image;

use \Exception;
use \InvalidArgumentException;

use \Charcoal\Image\ImageInterface;
use \Charcoal\Image\EffectInterface;
use \Charcoal\Image\EffectFactory;

/**
*
*/
abstract class AbstractImage implements ImageInterface
{
    protected $source;
    protected $target;

    /**
    * @var array $_effects
    */
    protected $effects = [];


    /**
    * Magic call function
    * Tries to load the effect of the called method name.
    *
    * For example, $img->blur(['sigma'=>15]); would create a "Blur" effect.
    *
    * @param string $fx_type
    * @param array  $data
    * @return Image Chainable
    */
    public function __call($fx_type, array $data)
    {
        $fx_data = isset($data[0]) ? $data[0] : [];
        $fx_data['type'] = $fx_type;
        $fx = $this->create_effect($fx_data);
        $fx->process();
        return $this;
    }

    /**
    * @return string
    */
    abstract public function driver_type();


    /**
    * @param array $data
    * @return Image Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['source']) && $data['source'] !== null) {
            $this->set_source($data['source']);
        }
        if (isset($data['target']) && $data['target'] !==null) {
            $this->set_target($data['target']);
        }
        if (isset($data['effects']) && $data['effects'] !== null) {
            $this->set_effects($data['effects']);
        }
        return $this;
    }

    /**
    * @param string $source
    * @throws InvalidArgumentException
    * @return Image Chainable
    */
    public function set_source($source)
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
    * @param string $target
    * @throws InvalidArgumentException
    * @return Image Chainable
    */
    public function set_target($target)
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
    * @param array $effects
    * @return Image Chainable
    */
    public function set_effects(array $effects)
    {
        $this->effects = [];
        foreach ($effects as $effect) {
            $this->add_effect($effect);
        }
        return $this;
    }
    
    /**
    * @return array The array of `EffectInterface` effects
    */
    public function effects()
    {
        return $this->effects;
    }

    /**
    * @param array|EffectInterface $effect
    * @throws InvalidArgumentException
    * @return Image Chainable
    */
    public function add_effect($effect)
    {
        $fx = $this->create_effect($effect);
        $this->effects[] = $fx;
    }

    /**
    * @param array $effects
    * @return Image Chainable
    */
    public function process(array $effects = null)
    {
        if ($effects !== null) {
            $this->set_effects($effects);
        }

        $effects = $this->effects();
        foreach ($effects as $fx) {
            $fx->process();
        }
        return $this;
    }

    /**
    * @param array|EffectInterface $effect
    * @return Image Chainable
    */
    public function process_effect($effect)
    {
        $fx = $this->create_effect($effect);
        $fx->process();
        return $this;
    }

    /**
    * Create a blank canvas of a given size, with a given background color.
    *
    * @param integer $width  Image size, in pixels
    * @param integer $height Image height, in pixels
    * @param string  $color  Default to transparent.
    * @throws InvalidArgumentException
    * @return Image Chainable
    */
    abstract public function create($width, $height, $color = 'rgb(100%, 100%, 100%, 0)');

    /**
    * Open an image file
    *
    * @param string $source The source path / filename
    * @throws InvalidArgumentException
    * @return Image Chainable
    */
    abstract public function open($source = null);

    /**
    * Save an image to a target.
    * If no target is set, the original source will be owerwritten
    *
    * @param string $target The target path / filename
    * @throws InvalidArgumentException
    * @return Image Chainable
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
    * @throws Exception
    */
    public function ratio()
    {
        $width = $this->width();
        $height = $this->height();
        if (!$width || !$height) {
            throw new Exception('Ratio can not be calculated. Invalid image dimensions');
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
    public function is_horizontal()
    {
        return ($this->orientation() == 'horizontal');
    }

    /**
    * @return boolean
    */
    public function is_vertical()
    {
        return ($this->orientation() == 'vertical');
    }

    /**
    * @return boolean
    */
    public function is_square()
    {
        return ($this->orientation() == 'square');
    }

    /**
    * Ensure an EffectInterface object
    *
    * @param array|EffectInterface $effect
    * @throws InvalidArgumentException
    * @return EffectInterface
    */
    protected function create_effect($effect)
    {
        if ($effect instanceof EffectInterface) {
            $effect->set_image($this);
            return $effect;
        } elseif (is_array($effect)) {
            if (!isset($effect['type'])) {
                throw new InvalidArgumentException('Effect parameter must define effect type');
            }
            $fx_type = $effect['type'];
            if (strstr($fx_type, '/') === false) {
                // Core effects do not need to be namespaced
                $driver = $this->driver_type();
                $fx_type = 'charcoal/image/'.$driver.'/effect/'.$driver.'-'.$fx_type.'-effect';
            }
            $image_fx = EffectFactory::instance()->create($fx_type);
            $image_fx->set_image($this);
            $image_fx->set_data($effect);
            return $image_fx;
        } else {
            throw new InvalidArgumentException('Effect must be an array or effect object');
        }
    }

    /**
    * @return array
    */
    public function available_channels()
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
    public function available_gravities()
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
    public function available_filters()
    {
        return [
            //'bartlett',
            'blackman',
            //'bohman',
            'box',
            'catrom',
            'cubic',
            'hamming',
            'hanning',
            'hermite',
            //'kaiser',
            'gaussian',
            'lanczos',
            'mitchell',
            //'parzen',
            'point',
            'quadratic',
            'triangle',
            //'welsh'
        ];
    }
}
