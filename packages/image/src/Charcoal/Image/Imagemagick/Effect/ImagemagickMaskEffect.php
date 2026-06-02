<?php

declare(strict_types=1);

namespace Charcoal\Image\Imagemagick\Effect;

use Exception;
use Charcoal\Image\Effect\AbstractMaskEffect;

/**
 * Mask Effect for the Imagemagick driver.
 */
class ImagemagickMaskEffect extends AbstractMaskEffect
{
    /**
     * @param array $data The effect data, if available.
     * @throws Exception This effect is not yet supported by Imagemagick driver.
     */
    public function process(?array $data = null): void
    {
        if ($data !== null) {
            $this->setData($data);
        }

        throw new Exception(
            'Mask Effect is not (yet) supported with imagemagick driver.'
        );
    }
}
