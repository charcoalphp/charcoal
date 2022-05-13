<?php

namespace Charcoal\Image\Effect;

use \InvalidArgumentException;

use \Charcoal\Image\AbstractEffect;
use \Charcoal\Image\ImageInterface;
use \Charcoal\Image\Effect\LayerEffectInterface;
use \Charcoal\Image\Effect\LayerEffectTrait;

/**
 * Composite an opacity mask on top of the image
 */
abstract class AbstractMaskEffect extends AbstractEffect implements LayerEffectInterface
{
    use LayerEffectTrait;

    /**
     * The mask image source
     * @var string $mask
     */
    private $mask;

    /**
     * @param string $mask The mask image source.
     * @throws InvalidArgumentException If the mask source is not a string.
     * @return AbstractMaskEffect Chainable
     */
    public function setMask($mask)
    {
        if (!is_string($mask) || ($mask instanceof ImageInterface)) {
            throw new InvalidArgumentException(
                'Mask must be a string'
            );
        }
        $this->mask = $mask;
        return $this;
    }

    /**
     * @return string
     */
    public function mask()
    {
        return $this->mask;
    }
}
