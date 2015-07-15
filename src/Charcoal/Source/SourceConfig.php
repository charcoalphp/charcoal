<?php

namespace Charcoal\Source;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Config\AbstractConfig as AbstractConfig;

/**
* Source Config
*/
class SourceConfig extends AbstractConfig
{
    /**
    * @var string $_type
    */
    private $_type;

    /**
    * @return array
    */
    public function default_data()
    {
        return [
            'type' => null
        ];
    }

    /**
    * @param array $data
    * @return SourceConfig Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['type']) && $data['type'] !== null) {
            $this->set_type($data['type']);
        }
        return $this;
    }

    /**
    * @param string $type
    * @throws InvalidArgumentException if parameter is not a string
    * @return SourceConfig Chainable
    */
    public function set_type($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException('Type must be a string.');
        }
        $this->_type = $type;
        return $this;
    }

    /**
    * @return string
    */
    public function type()
    {
        return $this->_type;
    }
}
