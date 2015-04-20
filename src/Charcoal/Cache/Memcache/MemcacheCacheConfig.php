<?php

namespace Charcoal\Cache\Memcache;

use \Charcoal\Cache\CacheConfig as CacheConfig;
use \Charcoal\Cache\Memcache\MemcacheCacheServerConfig;

class MemcacheCacheConfig extends CacheConfig
{
    private $_servers = [];

    /**
    * Default memcache configuration
    *
    * @return array
    */
    public function default_data()
    {
        return array_merge(parent::default_data(), [
            'servers'=>[[
                'host'          => 'localhost',
                'port'          => 11211,
                'persistent'    => false,
                'weight'        => 1
            ]]
        ]);
    }

    public function set_data($data)
    {
        parent::set_data($data);

        if (isset($data['servers']) && $data['servers'] !== null) {
            $this->set_servers($data['servers']);
        }

        return $this;
    }

    /**
    * @param array $servers
    * @throws \InvalidArgumentException if $servers is not an array
    * @return MemcacheCacheConfig Chainable
    */
    public function set_servers($servers)
    {
        if (!is_array($servers)) {
            throw new \InvalidArgumentException('Servers must be an array');
        }
        foreach ($servers as $server) {
            $this->add_server($server);
        }
        return $this;
    }

    /**
    * Get the aavilables servers.
    *
    * @return array An array of `MemcacheCacheServerConfig` objects
    */
    public function servers()
    {
        return $this->_servers;
    }

    /**
    * Add a server, from config or array, to the memcache available server pool.
    *
    * @param array|MemcacheCacheServerConfig $server
    * @throws \InvalidArgumentException if $server is not a proper array or object
    * @return MemcacheCacheConfig Chainable
    */
    public function add_server($server)
    {
        if (is_array($server)) {
            $server = new MemcacheCacheServerConfig($server);
            $this->_servers[] = $server;
        } else if (($server instanceof MemcacheCacheServerConfig)) {
            $this->_servers[] = $server;
        } else {
            throw new \InvalidArgumentException('Server must be an array or an object');
        }
        return $this;
    }
}
