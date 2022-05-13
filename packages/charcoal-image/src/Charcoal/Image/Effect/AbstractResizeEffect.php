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
     * @var mixed $size
     */
    private $size;

    /**
     * @var integer $width
     */
    private $width = 0;

    /**
     * @var integer $height
     */
    private $height = 0;

    /**
     * @var integer $minWidth
     */
    private $minWidth = 0;

    /**
     * @var integer $minHeight
     */
    private $minHeight = 0;

    /**
     * @var integer $maxWidth
     */
    private $maxWidth = 0;

    /**
     * @var integer $maxHeight
     */
    private $maxHeight = 0;

    /**
     * @var string $gravity
     */
    private $gravity = 'center';

    /**
     * @var string $backgroundColor
     */
    private $backgroundColor = 'rgba(100%, 100%, 100%, 0)';

    /**
     * @var boolean $adaptive
     */
    private $adaptive = false;

    /**
     * @param string $mode The resize mode.
     * @throws InvalidArgumentException If the mode argument is not a valid resize mode.
     * @return self
     */
    public function setMode($mode)
    {
        $allowedModes = [
            'auto',
            'exact',
            'width',
            'height',
            'best_fit',
            'constraints',
            'crop',
            'fill',
            'none'
        ];
        if (!is_string($mode) || (!in_array($mode, $allowedModes))) {
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
     * Set a complex resize value.
     *
     * @param  mixed $size The size.
     * @throws InvalidArgumentException If the size argument is not valid.
     * @return self
     */
    public function setSize($size)
    {
        if ($size !== null && !is_string($size) && (!is_numeric($size) || ($size < 0))) {
            throw new InvalidArgumentException(
                'Size must be a valid scale'
            );
        }

        $this->size = $size;

        return $this;
    }

    /**
     * Retrieve the complex resize value.
     *
     * @return mixed
     */
    public function size()
    {
        return $this->size;
    }

    /**
     * @param integer $width The target resize width.
     * @throws InvalidArgumentException If the width argument is not numeric or lower than 0.
     * @return self
     */
    public function setWidth($width)
    {
        if (!is_numeric($width) || ($width < 0)) {
            throw new InvalidArgumentException(
                'Width must be a positive integer'
            );
        }
        $this->width = (int)$width;
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
     * @param integer $height The target resize height.
     * @throws InvalidArgumentException If the height argument is not numeric or lower than 0.
     * @return self
     */
    public function setHeight($height)
    {
        if (!is_int($height) || ($height < 0)) {
            throw new InvalidArgumentException(
                'Height must be a positive integer'
            );
        }
        $this->height = (int)$height;
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
     * @param integer $minWidth The resize minimal width.
     * @throws InvalidArgumentException If the argument is not numeric or lower than 0.
     * @return self
     */
    public function setMinWidth($minWidth)
    {
        if (!is_numeric($minWidth) || ($minWidth < 0)) {
            throw new InvalidArgumentException(
                'Min Width must be a positive integer'
            );
        }
        $this->minWidth = (int)$minWidth;
        return $this;
    }

    /**
     * @return float
     */
    public function minWidth()
    {
        return $this->minWidth;
    }

    /**
     * @param integer $minHeight The resize minimal height.
     * @throws InvalidArgumentException If the argument is not numeric or lower than 0.
     * @return self
     */
    public function setMinHeight($minHeight)
    {
        if (!is_numeric($minHeight) || ($minHeight < 0)) {
            throw new InvalidArgumentException(
                'Min Height must be a positive integer'
            );
        }
        $this->minHeight = (int)$minHeight;
        return $this;
    }

    /**
     * @return float
     */
    public function minHeight()
    {
        return $this->minHeight;
    }

    /**
     * @param integer $maxWidth The resize max width.
     * @throws InvalidArgumentException If the argument is not numeric or lower than 0.
     * @return self
     */
    public function setMaxWidth($maxWidth)
    {
        if (!is_numeric($maxWidth) || ($maxWidth < 0)) {
            throw new InvalidArgumentException(
                'Max Width must be a positive integer'
            );
        }
        $this->maxWidth = (int)$maxWidth;
        return $this;
    }

    /**
     * @return float
     */
    public function maxWidth()
    {
        return $this->maxWidth;
    }

    /**
     * @param integer $maxHeight The resize max height.
     * @throws InvalidArgumentException If the argument is not numeric or lower than 0.
     * @return self
     */
    public function setMaxHeight($maxHeight)
    {
        if (!is_numeric($maxHeight) || ($maxHeight < 0)) {
            throw new InvalidArgumentException(
                'Height must be a positive integer'
            );
        }
        $this->maxHeight = (int)$maxHeight;
        return $this;
    }

    /**
     * @return float
     */
    public function maxHeight()
    {
        return $this->maxHeight;
    }

    /**
     * @param string $gravity The resize gravity.
     * @throws InvalidArgumentException If the argument is not a valid gravity name.
     * @return self
     */
    public function setGravity($gravity)
    {
        if (!in_array($gravity, $this->image()->availableGravities())) {
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
     * @param string $color The resize background color.
     * @throws InvalidArgumentException If the color argument is not a string.
     * @return self
     */
    public function setBackgroundColor($color)
    {
        if (!is_string($color)) {
            throw new InvalidArgumentException(
                'Color must be a string'
            );
        }
        $this->backgroundColor = $color;
        return $this;
    }

    /**
     * @return string
     */
    public function backgroundColor()
    {
        return $this->backgroundColor;
    }

    /**
     * @param boolean $adaptive The adaptative resize flag.
     * @return self
     */
    public function setAdaptive($adaptive)
    {
        $this->adaptive = !!$adaptive;
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
    public function autoMode()
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
            if ($this->minWidth() || $this->minHeight() || $this->maxWidth() || $this->maxHeight()) {
                return 'constraints';
            } else {
                return 'none';
            }
        }
    }

    /**
     * @param array $data The effect data, if available.
     * @throws Exception If the effect data is invalid for its resize mode.
     * @return self
     */
    public function process(array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }

        $mode = $this->mode();
        if ($mode == 'auto') {
            $mode = $this->autoMode();
        }

        $size = $this->size();
        if ($size) {
            $this->doResize(0, 0, false);
            return $this;
        }

        if ($mode == 'none') {
            // Noting to do.
            return $this;
        }

        $imageWidth  = $this->image()->width();
        $imageHeight = $this->image()->height();

        if ($imageWidth == 0 || $imageHeight == 0) {
            throw new Exception(
                'Can not process image; invalid image (0 width or height)'
            );
        }

        switch ($mode) {
            case 'exact':
                if (($this->width() <= 0) || ($this->height() <= 0)) {
                    throw new Exception(
                        'Missing parameters to perform exact resize'
                    );
                }
                if ($imageWidth != $this->width() || $imageHeight != $this->height()) {
                    $this->doResize($this->width(), $this->height(), false);
                }
                break;

            case 'width':
                if ($this->width() <= 0) {
                    throw new Exception(
                        'Missing parameters to perform exact width resize'
                    );
                }
                if ($imageWidth != $this->width()) {
                    $this->doResize($this->width(), 0, false);
                }
                break;

            case 'height':
                if ($this->height() <= 0) {
                    throw new Exception(
                        'Missing parameters to perform exact height resize'
                    );
                }
                if ($imageHeight != $this->height()) {
                    $this->doResize(0, $this->height(), false);
                }
                break;

            case 'best_fit':
                if (($this->width() <= 0) || ($this->height() <= 0)) {
                    throw new Exception(
                        'Missing parameters to perform "best fit" resize'
                    );
                }
                if ($imageWidth != $this->width() || $imageHeight != $this->height()) {
                    $this->doResize($this->width(), $this->height(), true);
                }
                break;

            case 'constraints':
                $minW = $this->minWidth();
                $minH = $this->minHeight();
                $maxW = $this->maxWidth();
                $maxH = $this->maxHeight();

                if (array_sum([$minW, $minH, $maxW, $maxH]) == 0) {
                    throw new Exception(
                        'Missing parameter(s) to perform "constraints" resize'
                    );
                }

                if (($minW && ($minW > $imageWidth)) || ($minH && ($minH > $imageHeight))) {
                    // Must scale up, keeping ratio
                    $this->doResize($minW, $minH, true);
                    break;
                }

                if (($maxW && ($maxW < $imageWidth)) || ($maxH && ($maxH < $imageHeight))) {
                    // Must scale down. keeping ratio
                    $this->doResize($maxW, $maxH, true);
                    break;
                }
                break;

            case 'crop':
                throw new Exception(
                    'Crop resize mode is not (yet) supported'
                );

            case 'fill':
                $newWidth = $this->width();
                $newHeight = $this->height();

                $oldRatio = $imageHeight ? ($imageWidth / $imageHeight) : 0;
                $newRatio = $newHeight ? ($newWidth / $newHeight) : 0;

                if ($newRatio > $oldRatio) {
                    $newHeight = ($imageHeight * $this->width() / $imageWidth);
                } else {
                    $newWidth = ($imageWidth * $this->height() / $imageHeight);
                }

                $this->doResize($newWidth, $newHeight);

                // $imgClass = get_class($this->image());
                // $canvas = new $imgClass;
                // $canvas->create($this->width(), $this->width(), $this->backgroundColor());
                // throw new Exception(
                //     'Crop resize mode is not (yet) supported'
                // );
        }

        return $this;
    }

    /**
     * @param integer $width   The target width.
     * @param integer $height  The target height.
     * @param boolean $bestFit The "best_fit" flag.
     * @return void
     */
    abstract protected function doResize($width, $height, $bestFit = false);
}
