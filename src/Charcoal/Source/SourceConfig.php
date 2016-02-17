<?php

namespace Charcoal\Source;

// Dependencies from `PHP`
use \InvalidArgumentException as InvalidArgumentException;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Config\AbstractConfig as AbstractConfig;

/**
* Source Config
*/
class SourceConfig extends AbstractConfig
{
    /**
    * @var string $type
    */
    private $type;

    /**
    * @return array
    */
    public function defaults()
    {
        return [
            'type' => ''
        ];
    }

    /**
    * @param string $type
    * @throws InvalidArgumentException if parameter is not a string
    * @return SourceConfig Chainable
    */
    public function setType($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Source type must be a string.'
            );
        }
        $this->type = $type;
        return $this;
    }

    /**
    * @return string
    */
    public function type()
    {
        return $this->type;
    }
}
