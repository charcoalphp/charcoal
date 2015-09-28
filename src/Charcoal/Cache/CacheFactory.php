<?php

namespace Charcoal\Cache;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Core\ClassMapFactory;

/**
* Cache factory
*/
class CacheFactory extends ClassMapFactory
{
    /**
    * @param array|null $data
    */
    public function __construct(array $data = null)
    {
        $this->set_base_class('\Charcoal\Cache\CacheInterface');
        $this->set_class_map([
            'apc'=>'\Charcoal\Cache\Apc\ApcCache',
            'memcache'=>'\Charcoal\Cache\Memcache\MemcacheCache',
            'noop'=>'\Charcoal\Cache\Noop\NoopCache'
        ]);

        if ($data !== null) {
            $this->set_data($data);
        }
    }
}
