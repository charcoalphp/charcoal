<?php

declare(strict_types=1);

namespace Charcoal\Image\Effect;

use Charcoal\Image\AbstractEffect;

/**
 * Image compression effect to adapt for web.
 * Defaults to 100% quality
 */
abstract class AbstractCompressionEffect extends AbstractEffect
{
    private int $quality = 100;

    /**
     * @param int $quality Image quality from 1 to 100
     * @return AbstractEffect Chainable
     */
    public function setQuality(int $quality)
    {
        $this->quality = $quality;
        return $this;
    }

    /**
     * @return int
     */
    public function quality()
    {
        return $this->quality;
    }
}
