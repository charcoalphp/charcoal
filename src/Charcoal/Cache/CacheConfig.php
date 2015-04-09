<?php

namespace Charcoal\Cache;

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
    * @throws \InvalidArgumentException if data is not an array
    * @return CacheConfig Chainable
    */
    public function set_data($data=nul)
    {
        //parent::set_data($data);
        if(!is_array($data)) {
            throw new \InvalidArgumentException('Data must be an array');
        }

        if(isset($data['active']) && $data['active'] !== null) {
            $this->set_active($data['active']);
        }
        if(isset($data['type']) && $data['type'] !== null) {
            $this->set_type($data['type']);
        }
        if(isset($data['default_ttl']) && $data['default_ttl'] !== null) {
            $this->set_default_ttl($data['default_ttl']);
        }
        if(isset($data['prefix']) && $data['prefix'] !== null) {
            $this->set_prefix($data['prefix']);
        }
        return $this;
    }

    public function set_active($active)
    {
        if(!is_bool($active)) {
            throw new \InvalidArgumentException('Active must be boolean');
            
        }
        $this->active = $active;
        return $this;
    }

    public function active()
    {
        return $this->_active;
    }

    public function set_type($type)
    {
        if(!is_string($type)) {
            throw new \InvalidArgumentException('Active must be boolean');
        }
        $this->_type = $type;
        return $this;
    }

    public function type()
    {
        return $this->_type;
    }

    public function set_default_ttl($ttl)
    {
        $this->_default_ttl = $ttl;
        return $this;
    }

    public function default_ttl()
    {
        return $this->_default_ttl;
    }

    public function set_prefix($prefix)
    {
        $this->_prefix = $prefix;
        return $this;
    }

    public function prefix()
    {
        return $this->_prefix;
    }
}
