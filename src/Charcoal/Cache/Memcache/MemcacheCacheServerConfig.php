<?php

namespace Charcoal\Cache\Memcache;

// Local parent namespace dependencies
use \Charcoal\Config\AbstractConfig as AbstractConfig;

/**
* Memcache Cache Server Config
*
* Defines a memcache server configuration.
*/
class MemcacheCacheServerConfig extends AbstractConfig
{
    /**
    * @var string $host
    */
    public $host;
    /**
    * @var integer $port
    */
    public $port;
    /**
    * @var boolean $persistent
    */
    public $persistent;
    /**
    * @var integer $weight
    */
    public $weight;

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
        $this->host = $host;
        return $this;
    }

    /**
    * @return string
    */
    public function host()
    {
        return $this->host;
    }

    /**
    * @param integer $port
    * @return MemcacheCacheServerConfig Chainable
    */
    public function set_port($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
    * @return integer
    */
    public function port()
    {
        return $this->port;
    }

    /**
    * @param boolean $persistent
    * @return MemcacheCacheServerConfig Chainable
    */
    public function set_persistent($persistent)
    {
        $this->persistent = $persistent;
        return $this;
    }

    /**
    * @return boolean
    */
    public function persistent()
    {
        return $this->persistent;
    }

    /**
    * @param integer $weight
    * @return MemcacheCacheServerConfig Chainable
    */
    public function set_weight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    /**
    * @return integer
    */
    public function weight()
    {
        return $this->weight;
    }
}
