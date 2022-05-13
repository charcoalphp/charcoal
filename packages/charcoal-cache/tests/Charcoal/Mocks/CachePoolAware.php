<?php

namespace Charcoal\Tests\Mocks;

// From 'charcoal-cache'
use Charcoal\Cache\CachePoolAwareTrait;

/**
 * Mock object for {@see \Charcoal\Tests\Cache\CachePoolAwareTraitTest}
 */
class CachePoolAware
{
    use CachePoolAwareTrait {
        CachePoolAwareTrait::setCachePool as public;
        CachePoolAwareTrait::cachePool as public;
    }
}
