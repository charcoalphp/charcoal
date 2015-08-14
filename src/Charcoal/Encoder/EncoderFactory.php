<?php

namespace Charcoal\Encoder;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Core\ClassMapFactory as ClassMapFactory;

/**
*
*/
class EncoderFactory extends ClassMapFactory
{
    /**
    * @param array|null $data
    */
    public function __construct(array $data = null)
    {
        $this->set_base_class('\Charcoal\Encoder\EncoderInterface');
        $this->set_class_map([
            'base64' => '\Charcoal\Encoder\Base64\Base64Encoder'
        ]);

        if ($data !== null) {
            $this->set_data($data);
        }
    }
}
