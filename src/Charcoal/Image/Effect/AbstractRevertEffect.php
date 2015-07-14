<?php

namespace Charcoal\Image\Effect;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Image\AbstractEffect as AbstractEffect;

/**
* Revert (negate) the image's colors.
*/
abstract class AbstractRevertEffect extends AbstractEffect
{
    /**
    * @var string $_channel
    */
    private $_channel = 'all';


    /**
    * @param array $data
    * @return AbstractRevertEffect Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['channel']) && $data['channel'] !== null) {
            $this->set_channel($data['channel']);
        }
        return $this;
    }

    /**
    * @param string $channel
    * @throws InvalidArgumentException
    * @return AbstractBlurEffect Chainable
    */
    public function set_channel($channel)
    {
        if (!in_array($channel, $this->image()->available_channels())) {
            throw new InvalidArgumentException('Channel is not valid');
        }
        $this->_channel = $channel;
        return $this;
    }
    
    /**
    * @return string
    */
    public function channel()
    {
        return $this->_channel;
    }
}
