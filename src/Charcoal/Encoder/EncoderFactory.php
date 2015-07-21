<?php

namespace Charcoal\Encoder;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Core\AbstractFactory as AbstractFactory;

/**
*
*/
class EncoderFactory extends AbstractFactory
{
    /**
    * Force base types. (Only base64 for now)
    *
    * @return array
    */
    public function types()
    {
        return array_merge(
            parent::types(),
            [
                'base64' => '\Charcoal\Encoder\Base64\Base64Encoder'
            ]
        );
    }
}
