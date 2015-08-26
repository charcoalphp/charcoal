<?php

namespace Charcoal\Image\Effect;

use \Exception;
use \InvalidArgumentException;

use \Charcoal\Image\AbstractEffect;

/**
* Resize an image to given dimensions
*/
abstract class AbstractResizeEffect extends AbstractEffect
{
    /**
    * @var string $mode
    */
    private $mode = 'auto';

    /**
    * @var integer $width
    */
    private $width = 0;
    /**
    * @var integer $height
    */
    private $height = 0;

    /**
    * @var integer $min_width
    */
    private $min_width = 0;

    /**
    * @var integer $min_height
    */
    private $min_height = 0;

    /**
    * @var integer $max_width
    */
    private $max_width = 0;

    /**
    * @var integer $max_height
    */
    private $max_height = 0;

    /**
    * @var string $gravity
    */
    private $gravity = 'center';

    /**
    * @var string $background_color
    */
    private $background_color = 'rgba(100%, 100%, 100%, 0)';

    /**
    * @var string
    */
    private $adaptive = false;

    /**
    * @param string $mode
    * @throws InvalidArgumentException
    * @return AbstractResizeEffect Chainable
    */
    public function set_mode($mode)
    {
        $allowed_modes = [
            'auto',
            'exact',
            'width',
            'height',
            'best_fit',
            'crop',
            'fill',
            'none'
        ];
        if (!is_string($mode) || (!in_array($mode, $allowed_modes))) {
            throw new InvalidArgumentException(
                'Mode is not valid'
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
    * @param int $width
    * @throws InvalidArgumentException
    * @return Rotate Chainable
    */
    public function set_width($width)
    {
        if (!is_int($width) || ($width < 0)) {
            throw new InvalidArgumentException(
                'Width must be a a positive integer'
            );
        }
        $this->width = $width;
        return $this;
    }

    /**
    * @return float
    */
    public function width()
    {
        return $this->width;
    }

    /**
    * @param integer $height
    * @throws InvalidArgumentException
    * @return Rotate Chainable
    */
    public function set_height($height)
    {
        if (!is_int($height) || ($height < 0)) {
            throw new InvalidArgumentException(
                'Height must be a positive integer'
            );
        }
        $this->height = $height;
        return $this;
    }

    /**
    * @return float
    */
    public function height()
    {
        return $this->height;
    }

    /**
    * @param int $min_width
    * @throws InvalidArgumentException
    * @return Rotate Chainable
    */
    public function set_min_width($min_width)
    {
        if (!is_int($min_width) || ($min_width < 0)) {
            throw new InvalidArgumentException(
                'Min Width must be a a positive integer'
            );
        }
        $this->min_width = $min_width;
        return $this;
    }

    /**
    * @return float
    */
    public function min_width()
    {
        return $this->min_width;
    }

    /**
    * @param integer $min_height
    * @throws InvalidArgumentException
    * @return Rotate Chainable
    */
    public function set_min_height($min_height)
    {
        if (!is_int($min_height) || ($min_height < 0)) {
            throw new InvalidArgumentException(
                'Min Height must be a positive integer'
            );
        }
        $this->min_height = $min_height;
        return $this;
    }

    /**
    * @return float
    */
    public function min_height()
    {
        return $this->min_height;
    }

    /**
    * @param int $max_width
    * @throws InvalidArgumentException
    * @return Rotate Chainable
    */
    public function set_max_width($max_width)
    {
        if (!is_int($max_width) || ($max_width < 0)) {
            throw new InvalidArgumentException(
                'Max Width must be a a positive integer'
            );
        }
        $this->max_width = $max_width;
        return $this;
    }

    /**
    * @return float
    */
    public function max_width()
    {
        return $this->max_width;
    }

    /**
    * @param integer $max_height
    * @throws InvalidArgumentException
    * @return Rotate Chainable
    */
    public function set_max_height($max_height)
    {
        if (!is_int($max_height) || ($max_height < 0)) {
            throw new InvalidArgumentException(
                'Height must be a positive integer'
            );
        }
        $this->max_height = $max_height;
        return $this;
    }

    /**
    * @return float
    */
    public function max_height()
    {
        return $this->max_height;
    }

    /**
    * @param string $gravity
    * @throws InvalidArgumentException
    * @return AbstractWatermarkEffect Chainable
    */
    public function set_gravity($gravity)
    {
        if (!in_array($gravity, $this->image()->available_gravities())) {
            throw new InvalidArgumentException(
                'Gravity is not valid'
            );
        }
        $this->gravity = $gravity;
        return $this;
    }

    /**
    * @return string
    */
    public function gravity()
    {
        return $this->gravity;
    }

    /**
    * @param string $color
    * @throws InvalidArgumentException
    * @return AbstractRotateEffect Chainable
    */
    public function set_background_color($color)
    {
        if (!is_string($color)) {
            throw new InvalidArgumentException(
                'Color must be a string'
            );
        }
        $this->background_color = $color;
        return $this;
    }

    /**
    * @return string
    */
    public function background_color()
    {
        return $this->background_color;
    }


    /**
    * @param boolean $adaptive
    * @throws InvalidArgumentException
    * @return AbstractRotateEffect Chainable
    */
    public function set_adaptive($adaptive)
    {
        if (!is_bool($adaptive)) {
            throw new InvalidArgumentException(
                'Adaptive flag must be a boolean'
            );
        }
        $this->adaptive = $adaptive;
        return $this;
    }

    /**
    * @return boolean
    */
    public function adaptive()
    {
        return $this->adaptive;
    }

    /**
    * @return string
    */
    public function auto_mode()
    {
        $width = $this->width();
        $height = $this->height();

        if ($width > 0 && $height > 0) {
            return 'exact';
        } elseif ($width > 0) {
            return 'width';
        } elseif ($height > 0) {
            return 'height';
        } else {
            if ($this->min_width() || $this->min_height() || $this->max_width() || $this->max_height()) {
                return 'constraints';
            } else {
                return 'none';
            }
        }
    }

    /**
    * @param array $data
    * @throws Exception
    * @return AbstractResizeEffect Chainable
    */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->set_data($data);
        }

        $mode = $this->mode();
        if ($mode == 'auto') {
            $mode = $this->auto_mode();
        }

        if ($mode == 'none') {
            return;
        }

        $img_w = $this->image()->width();
        $img_h = $this->image()->height();

        switch ($mode) {
            case 'exact':
                if (($this->width() <= 0) || ($this->height() <= 0)) {
                    throw new Exception(
                        'Missing parameters to perform exact resize'
                    );
                }
                if ($img_w != $this->width() || $img_h != $this->height()) {
                    $this->do_resize($this->width(), $this->height(), false);
                }
                break;

            case 'width':
                if ($this->width() <= 0) {
                    throw new Exception(
                        'Missing parameters to perform exact width resize'
                    );
                }
                if ($img_w != $this->width()) {
                    $this->do_resize($this->width(), 0, false);
                }
                break;

            case 'height':
                if ($this->height() <= 0) {
                    throw new Exception(
                        'Missing parameters to perform exact height resize'
                    );
                }
                if ($img_h != $this->height()) {
                    $this->do_resize(0, $this->height(), false);
                }
                break;

            case 'best_fit':
                if (($this->width() <= 0) || ($this->height() <= 0)) {
                    throw new Exception(
                        'Missing parameters to perform "best fit" resize'
                    );
                }
                if ($img_w != $this->width() || $img_h != $this->height()) {
                    $this->do_resize($this->width(), $this->height(), true);
                }
                break;

            case 'constraints':
                $min_w = $this->min_width();
                $min_h = $this->min_height();
                $max_w = $this->max_width();
                $max_h = $this->max_height();

                if (array_sum([$min_w, $min_h, $max_w, $max_h]) == 0) {
                    throw new Exception(
                        'Missing parameter(s) to perform "constraints" resize'
                    );
                }

                if (($min_w && ($min_w > $img_w)) || ($min_h && ($min_h > $img_h))) {
                    // Must scale up, keeping ratio
                    $this->do_resize($min_w, $min_h, true);
                    break;
                }

                if (($max_w && ($max_w < $img_w)) || ($max_h && ($max_h < $img_h))) {
                    // Must scale down. keeping ratio
                    $this->do_resize($max_w, $max_h, true);
                    break;
                }
                break;

            case 'crop':
                $ratio = $this->image()->ratio();

                throw new Exception(
                    'Crop resize mode is not (yet) supported'
                );
                //break;

            case 'fill':
                $img_class = get_class($this->image());
                $canvas = new $img_class;
                $canvas->create($this->width(), $this->width(), $this->background_color());
                throw new Exception(
                    'Crop resize mode is not (yet) supported'
                );
                //break;
        }

        return $this;
    }

    /**
    * @param integer $width
    * @param integer $height
    * @param boolean $best_fit
    * @return void
    */
    abstract protected function do_resize($width, $height, $best_fit = false);
}
