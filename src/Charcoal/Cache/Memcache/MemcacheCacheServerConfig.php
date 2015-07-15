<?php

namespace Charcoal\Cache\Memcache;

use \Charcoal\Config\AbstractConfig as AbstractConfig;

class MemcacheCacheServerConfig extends AbstractConfig
{
    public $_host;
    public $_port;
    public $_persistent;
    public $_weight;

    /**
    * @return array
    */
    public function default_data()
    {
        $default = [
            'host'       => 'localhost',
            'port'       => 11211,
            'persistent' => true,
            'weight'     => 1
        ];

        $default_data = array_merge(parent::default_data(), $default);
        return $default_data;
    }

    /**
    * @param array $data
    * @return MemcacheCacheServerConfig Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['host']) && $data['host'] !== null) {
            $this->set_host($data['host']);
        }
        if (isset($data['port']) && $data['port'] !== null) {
            $this->set_port($data['port']);
        }
        if (isset($data['persistent']) && $data['persistent'] !== null) {
            $this->set_persistent($data['persistent']);
        }
        if (isset($data['weight']) && $data['weight'] !== null) {
            $this->set_weight($data['weight']);
        }

        return $this;
    }

    /**
    * @param string $host
    * @return MemcacheCacheServerConfig Chainable
    */
    public function set_host($host)
    {
        $this->_host = $host;
        return $this;
    }

    /**
    * @return string
    */
    public function host()
    {
        return $this->_host;
    }

    /**
    * @param integer $port
    * @return MemcacheCacheServerConfig Chainable
    */
    public function set_port($port)
    {
        $this->_port = $port;
        return $this;
    }

    /**
    * @return integer
    */
    public function port()
    {
        return $this->_port;
    }

    /**
    * @param boolean $persistent
    * @return MemcacheCacheServerConfig Chainable
    */
    public function set_persistent($persistent)
    {
        $this->_persistent = $persistent;
        return $this;
    }

    /**
    * @return boolean
    */
    public function persistent()
    {
        return $this->_persistent;
    }

    /**
    * @param integer $weight
    * @return MemcacheCacheServerConfig Chainable
    */
    public function set_weight($weight)
    {
        $this->_weight = $weight;
        return $this;
    }

    /**
    * @return integer
    */
    public function weight()
    {
        return $this->_weight;
    }
}
