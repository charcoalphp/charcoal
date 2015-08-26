<?php

namespace Charcoal\Image;

use \Exception;

use \Charcoal\Image\ImageInterface;

abstract class AbstractEffect implements EffectInterface
{
    /**
    * @var ImageInterface $_image
    */
    private $_image;

    /**
    * @param ImageInterface $image
    * @return AbstractEffect Chainable
    */
    public function set_image(ImageInterface $image)
    {
        $this->_image = $image;
        return $this;
    }

    /**
    * @throws Exception
    * @return ImageInterface
    */
    public function image()
    {
        if ($this->_image === null) {
            throw new Exception(
                'Trying to access an unset image'
            );
        }
        return $this->_image;
    }

    /**
    * @param array $data
    * @return AbstractEffect Chainable
    */
    public function set_data(array $data)
    {
        foreach ($data as $key => $val) {
            $f = [$this, 'set_'.$key];
            if (is_callable($f)) {
                call_user_func($f, $val);
            }
        }
        return $this;
    }

    /**
    * @param array $data
    * @return AbstractEffect Chainable
    */
    abstract public function process(array $data = null);
}
