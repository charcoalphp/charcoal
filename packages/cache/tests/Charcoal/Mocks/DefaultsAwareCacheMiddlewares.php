<?php

namespace Charcoal\Tests\Mocks;

use Charcoal\Cache\Middleware\CacheMiddleware;

/**
 * Mock object for {@see \Charcoal\Tests\Cache\Middleware\AbstractCacheMiddlewareTest}
 */
class DefaultsAwareCacheMiddlewares extends CacheMiddleware
{
    public function getCacheTtl()
    {
        return $this['cache_ttl'];
    }
}
