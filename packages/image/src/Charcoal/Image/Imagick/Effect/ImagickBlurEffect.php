<?php

namespace Charcoal\Image\Imagick\Effect;

use Exception;
use Charcoal\Image\Effect\AbstractBlurEffect;

/**
 * Blur Effect for the Imagick driver.
 */
class ImagickBlurEffect extends AbstractBlurEffect
{
    /**
     * @return ImagickBlurEffect Chainable
     */
    public function processAdaptive(): static
    {
        $channel = $this->image()->imagickChannel($this->channel());
        $this->image()->imagick()->adaptiveBlurImage($this->radius(), $this->sigma(), $channel);
        return $this;
    }

    /**
     * @return ImagickBlurEffect Chainable
     */
    public function processGaussian(): static
    {
        $channel = $this->image()->imagickChannel($this->channel());
        $this->image()->imagick()->gaussianBlurImage($this->radius(), $this->sigma(), $channel);
        return $this;
    }

    /**
     * @return ImagickBlurEffect Chainable
     */
    public function processMotion(): static
    {
        $channel = $this->image()->imagickChannel($this->channel());
        $this->image()->imagick()->motionBlurImage($this->radius(), $this->sigma(), $this->angle(), $channel);
        return $this;
    }

    /**
     * @return ImagickBlurEffect Chainable
     */
    public function processRadial(): static
    {
        $angle = $this->angle();
        $channel = $this->image()->imagickChannel($this->channel());
        $this->image()->imagick()->rotationalBlurImage(($angle), $channel);
        return $this;
    }

    /**
     * @throws Exception This method is not yet supported on Imagick.
     */
    public function processSoft(): never
    {
        throw new Exception(
            'Soft blur is not (yet) supported with imagick driver.'
        );
    }

    /**
     * @return ImagickBlurEffect Chainable
     */
    public function processStandard(): static
    {
        $channel = $this->image()->imagickChannel($this->channel());
        $this->image()->imagick()->blurImage($this->radius(), $this->sigma(), $channel);
        return $this;
    }
}
