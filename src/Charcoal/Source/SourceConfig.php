<?php

namespace Charcoal\Source;

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
            'type'=>null
        ];
    }

    /**
    * @param array $data
    * @throws \InvalidArgumentException if parameter is not an array
    * @return SourceConfig Chainable
    */
    public function set_data($data)
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Data must be an array');
        }
        if (isset($data['type']) && $data['type'] !== null) {
            $this->set_type($data['type']);
        }
        return $this;
    }

    /**
    * @param string
    * @throws \InvalidArgumentException if parameter is not a string
    * @return SourceConfig Chainable
    */
    public function set_type($type)
    {
        if (!is_string($type)) {
            throw new \InvalidArgumentException('Type needs to be a string');
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
