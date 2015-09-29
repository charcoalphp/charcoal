<?php

namespace Charcoal\Cache;

// Dependencies from `PHP`
use \InvalidArgumentException as InvalidArgumentException;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Config\AbstractConfig as AbstractConfig;

/**
* Cache Configuration
*/
class CacheConfig extends AbstractConfig
{
    /**
    * @var boolean $active
    */
    private $active;
    /**
    * @var string $type
    */
    private $type;
    /**
    * @var integer $default_ttl
    */
    private $default_ttl;
    /**
    * @var string $prefix
    */
    private $prefix;

    /**
    * @return array
    */
    public function default_data()
    {
        return [
            'active'      => true,
            'type'        => 'noop',
            'default_ttl' => 0,
            'prefix'      => ''
        ];
    }

    /**
    * @param boolean $active
    * @throws InvalidArgumentException
    * @return CacheConfig Chainable
    */
    public function set_active($active)
    {
        if (!is_bool($active)) {
            throw new InvalidArgumentException('Active must be a boolean.');
        }
        $this->active = $active;
        return $this;
    }

    /**
    * @return boolean
    */
    public function active()
    {
        return $this->active;
    }

    /**
    * @param string $type
    * @throws InvalidArgumentException
    * @return CacheConfig Chainable
    */
    public function set_type($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException('Active must be a boolean.');
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

    /**
    * @param integer $ttl The time-to-live, in seconds
    * @throws InvalidArgumentException
    * @return CacheConfig Chainable
    */
    public function set_default_ttl($ttl)
    {
        if (!is_integer($ttl)) {
            throw new InvalidArgumentException('TTL must be an integer (seconds).');
        }
        $this->default_ttl = $ttl;
        return $this;
    }

    /**
    * @return integer
    */
    public function default_ttl()
    {
        return $this->default_ttl;
    }

    /**
    * @param string $prefix
    * @throws InvalidArgumentException
    * @return CacheConfig Chainable
    */
    public function set_prefix($prefix)
    {
        if (!is_string($prefix)) {
            throw new InvalidArgumentException('Prefix must be a string.');
        }
        $this->prefix = $prefix;
        return $this;
    }

    /**
    * @return string
    */
    public function prefix()
    {
        return $this->prefix;
    }
}
