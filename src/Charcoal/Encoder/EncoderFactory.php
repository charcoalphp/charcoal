<?php

namespace Charcoal\Encoder;

use \Charcoal\Core\AbstractFactory as AbstractFactory;

class EncoderFactory extends AbstractFactory
{
    /**
    * @return array
    */
    static public function types()
    {
        return array_merge(
            parent::types(), [
            'base64'       => '\Charcoal\Encoder\Base64\Base64Encoder'
            ]
        );
    }
}
