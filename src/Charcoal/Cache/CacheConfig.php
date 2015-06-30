<?php

namespace Charcoal\Cache;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Config\AbstractConfig as AbstractConfig;

/**
* Cache Configuration
*/
class CacheConfig extends AbstractConfig
{
    /**
    * @var boolean $_active
    */
    private $_active;
    /**
    * @var string $_type
    */
    private $_type;
    /**
    * @var integer $_default_ttl
    */
    private $_default_ttl;
    /**
    * @var string $_prefix
    */
    private $_prefix;

    /**
    * @return array
    */
    public function default_data()
    {
        return [
            'active'        => true,
            'type'          => 'noop',
            'default_ttl'   => 0,
            'prefix'        => ''
        ];
    }

    /**
    * @param array $data
    * @return CacheConfig Chainable
    */
    public function set_data(array $data)
    {
        //parent::set_data($data);
        if (isset($data['active']) && $data['active'] !== null) {
            $this->set_active($data['active']);
        }
        if (isset($data['type']) && $data['type'] !== null) {
            $this->set_type($data['type']);
        }
        if (isset($data['default_ttl']) && $data['default_ttl'] !== null) {
            $this->set_default_ttl($data['default_ttl']);
        }
        if (isset($data['prefix']) && $data['prefix'] !== null) {
            $this->set_prefix($data['prefix']);
        }
        return $this;
    }

    /**
    * @param bool $active
    * @throws InvalidArgumentException
    * @return CacheConfig Chainable
    */
    public function set_active($active)
    {
        if (!is_bool($active)) {
            throw new InvalidArgumentException('Active must be boolean');
            
        }
        $this->_active = $active;
        return $this;
    }

    /**
    * @return boolean
    */
    public function active()
    {
        return $this->_active;
    }

    /**
    * @param string $type
    * @throws InvalidArgumentException
    * @return CacheConfig Chainable
    */
    public function set_type($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException('Active must be boolean');
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

    /**
    * @param integer $ttl The time-to-live, in seconds
    * @throws InvalidArgumentException
    * @return CacheConfig Chainable
    */
    public function set_default_ttl($ttl)
    {
        if (!is_integer($ttl)) {
            throw new InvalidArgumentException('TTL must be an integer (seconds)');
        }
        $this->_default_ttl = $ttl;
        return $this;
    }

    /**
    * @return integer
    */
    public function default_ttl()
    {
        return $this->_default_ttl;
    }

    /**
    * @param string $prefix
    * @throws InvalidArgumentException
    * @return CacheConfig Chainable
    */
    public function set_prefix($prefix)
    {
        if (!is_string($prefix)) {
            throw new InvalidArgumentException('Prefix must be a string');
        }
        $this->_prefix = $prefix;
        return $this;
    }

    /**
    * @return string
    */
    public function prefix()
    {
        return $this->_prefix;
    }
}
