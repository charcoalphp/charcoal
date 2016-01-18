<?php

namespace Charcoal\Encoder;

// Moule `charcoal-factory` dependencies
use \Charcoal\Factory\MapFactory as MapFactory;

/**
*
*/
class EncoderFactory extends MapFactory
{

    /**
    * @return string
    */
    public function baseClass()
    {
        return '\Charcoal\Encoder\EncoderInterface';
    }

    /**
    * @return array
    */
    public function map()
    {
        return [
            'base64' => '\Charcoal\Encoder\Base64\Base64Encoder'
        ];
    }
}
