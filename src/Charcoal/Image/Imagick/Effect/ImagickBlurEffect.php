<?php

namespace Charcoal\Image\Imagick\Effect;

use \Exception as Exception;

use \Charcoal\Image\Effect\AbstractBlurEffect as AbstractBlurEffect;

class ImagickBlurEffect extends AbstractBlurEffect
{
    /**
    * @return ImagickBlurEffect Chainable
    */
    public function process_adaptive()
    {
        $channel = $this->image()->imagick_channel($this->channel());
        $this->image()->imagick()->adaptiveBlurImage($this->radius(), $this->sigma());
        return $this;
    }
    
    /**
    * @return ImagickBlurEffect Chainable
    */
    public function process_gaussian()
    {
        $channel = $this->image()->imagick_channel($this->channel());
        $this->image()->imagick()->gaussianBlurImage($this->radius(), $this->sigma(), $channel);
        return $this;
    }
    
    /**
    * @return ImagickBlurEffect Chainable
    */
    public function process_motion()
    {
        $channel = $this->image()->imagick_channel($this->channel());
        $this->image()->imagick()->motionBlurImage($this->radius(), $this->sigma(), $this->angle(), $channel);
        return $this;
    }
    
    /**
    * @return ImagickBlurEffect Chainable
    */
    public function process_radial()
    {
        $channel = $this->image()->imagick_channel($this->channel());
        $this->image()->imagick()->radialBlurImage($this->angle(), $channel);
        return $this;
    }
    
    /**
    * @throws Exception
    * @return ImagickBlurEffect Chainable
    */
    public function process_soft()
    {
        // @todo
        //'-define convolve:scale=60,40% -morphology Convolve \'Gaussian:'.$this->radius().'x'.$this->radius().'\'';
        throw new Exception('Soft blur is not (yet) supported with imagick driver.');
    }
    
    /**
    * @return ImagickBlurEffect Chainable
    */
    public function process_standard()
    {
        $channel = $this->image()->imagick_channel($this->channel());
        $this->image()->imagick()->blurImage($this->radius(), $this->sigma(), $channel);
        return $this;
    }
}
