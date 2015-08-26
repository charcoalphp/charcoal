<?php

namespace Charcoal\Image\Imagemagick\Effect;

use \Charcoal\Image\Effect\AbstractBlurEffect;

class ImagemagickBlurEffect extends AbstractBlurEffect
{
    /**
    * @return ImagemagickBlurEffect Chainable
    */
    public function process_adaptive()
    {
        $channel = $this->image()->convert_channel($this->channel());
        $cmd = '-channel '.$channel.' -adaptive-blur '.$this->radius().'x'.$this->sigma();
        $this->image()->apply_cmd($cmd);
        return $this;
    }
    
    /**
    * @return ImagemagickBlurEffect Chainable
    */
    public function process_gaussian()
    {
        $channel = $this->image()->convert_channel($this->channel());
        $cmd = '-channel '.$channel.' -gaussian-blur '.$this->radius().'x'.$this->sigma();
        $this->image()->apply_cmd($cmd);
        return $this;
    }
    
    /**
    * @return ImagemagickBlurEffect Chainable
    */
    public function process_motion()
    {
        $channel = $this->image()->convert_channel($this->channel());
        $cmd = '-channel '.$channel.' -motion-blur '.$this->radius().'x'.$this->sigma().'+'.$this->angle();
        $this->image()->apply_cmd($cmd);
        return $this;
    }
    
    /**
    * @return ImagemagickBlurEffect Chainable
    */
    public function process_radial()
    {
        $channel = $this->image()->convert_channel($this->channel());
        $cmd = '-channel '.$channel.' -radial-blur '.$this->angle();
        $this->image()->apply_cmd($cmd);
        return $this;
    }
    
    /**
    * @return ImagemagickBlurEffect Chainable
    */
    public function process_soft()
    {
        // Todo: Support channel
        $channel = $this->image()->convert_channel($this->channel());
        $cmd = '-define convolve:scale=60,40% -morphology Convolve \'Gaussian:'.$this->radius().'x'.$this->sigma().'\'';
        $this->image()->apply_cmd($cmd);
        return $this;
    }
    
    /**
    * @return ImagemagickBlurEffect Chainable
    */
    public function process_standard()
    {
        $channel = $this->image()->convert_channel($this->channel());
        $cmd = '-channel '.$channel.' -blur '.$this->radius().'x'.$this->sigma();
        $this->image()->apply_cmd($cmd);
        return $this;
    }
}
