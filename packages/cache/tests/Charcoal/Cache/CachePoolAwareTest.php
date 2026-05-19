<?php

namespace Charcoal\Tests\Cache;

// From 'tedivm/stash'
use Stash\Pool;

// From 'charcoal-cache'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Mocks\CachePoolAware;

/**
 * Test CachePoolAwareTrait
 */
#[\PHPUnit\Framework\Attributes\CoversTrait(\Charcoal\Cache\CachePoolAwareTrait::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CachePoolAwareTrait::class, 'setCachePool')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CachePoolAwareTrait::class, 'cachePool')]
class CachePoolAwareTest extends AbstractTestCase
{
    public function testCachePool(): void
    {
        $obj  = new CachePoolAware();
        $pool = new Pool();

        $obj->setCachePool($pool);
        $this->assertSame($pool, $obj->cachePool());
    }

    /**
     * testSetPrefixOnInvalidValue
     */
    public function testMissingPool(): void
    {
        $this->expectExceptionMessage('Cache Pool is not defined for "Charcoal\Tests\Mocks\CachePoolAware"');
        $this->expectException(\RuntimeException::class);
        $obj = new CachePoolAware();
        $obj->cachePool();
    }
}
