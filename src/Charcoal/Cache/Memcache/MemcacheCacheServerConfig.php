<?php

namespace Charcoal\Cache\Memcache;

use \Charcoal\Config\AbstractConfig as AbstractConfig;

class MemcacheCacheServerConfig extends AbstractConfig
{
    public $_host;
    public $_port;
    public $_persistent;
    public $_weight;

    public function default_data()
    {
        $default = [
            'host'=>'localhost',
            'port'=>11211,
            'persistent'=>true,
            'weight'=>1
        ];

        $default_data = array_merge(parent::default_data(), $default);
        return $default_data;
    }

    public function set_data($data)
    {
        if(isset($data['host']) && $data['host'] !== null) {
            $this->set_host($data['host']);
        }
        if(isset($data['port']) && $data['port'] !== null) {
            $this->set_port($data['port']);
        }
        if(isset($data['persistent']) && $data['persistent'] !== null) {
            $this->set_persistent($data['persistent']);
        }
        if(isset($data['weight']) && $data['weight'] !== null) {
            $this->set_weight($data['weight']);
        }

        return $this;
    }

    public function set_host($host)
    {
        $this->_host = $host;
        return $this;
    }

    public function host()
    {
        return $this->_host;
    }

    public function set_port($port)
    {
        $this->_port = $port;
        return $this;
    }

    public function port()
    {
        return $this->_port;
    }

    public function set_persistent($persistent)
    {
        $this->_persistent = $persistent;
        return $this;
    }

    public function persistent()
    {
        return $this->_persistent;
    }

    public function set_weight($weight)
    {
        $this->_weight = $weight;
        return $this;
    }

    public function weight()
    {
        return $this->_weight;
    }
}
