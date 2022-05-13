<?php

namespace Charcoal\Image;

use Charcoal\Image\Imagemagick\ImagemagickImage;
use Charcoal\Image\Imagick\ImagickImage;
use \Exception;

use \Charcoal\Image\ImageInterface;

/**
 * Base Effect
 */
abstract class AbstractEffect implements EffectInterface
{
    /**
     * @var ImageInterface $image
     */
    private $image;

    /**
     * @param ImageInterface $image The parent image.
     * @return AbstractEffect Chainable
     */
    public function setImage(ImageInterface $image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @throws Exception If the parent image was not set before being accessed.
     * @return ImageInterface|ImagickImage|ImagemagickImage
     */
    public function image()
    {
        if ($this->image === null) {
            throw new Exception(
                'Can not get effect\'s image: Trying to access an unset image'
            );
        }
        return $this->image;
    }

    /**
     * @param array $data The effect data.
     * @return AbstractEffect Chainable
     */
    public function setData(array $data)
    {
        foreach ($data as $key => $val) {
            $method = [ $this, $this->setter($key) ];
            if (is_callable($method)) {
                call_user_func($method, $val);
            }
        }
        return $this;
    }

    /**
     * @param array $data Optional effect data. If null, use the currently set properties.
     * @return AbstractEffect Chainable
     */
    abstract public function process(array $data = null);

    /**
     * Allow an object to define how the key setter are called.
     *
     * @param string $key The key to get the setter from.
     * @return string The setter method name, for a given key.
     */
    protected function setter($key)
    {
        $setter = 'set_'.$key;
        return $this->camelize($setter);
    }

    /**
     * Transform a snake_case string to camelCase.
     *
     * @param string $str The snake_case string to camelize.
     * @return string The camelcase'd string.
     */
    protected function camelize($str)
    {
        return lcfirst(implode('', array_map('ucfirst', explode('_', $str))));
    }
}
