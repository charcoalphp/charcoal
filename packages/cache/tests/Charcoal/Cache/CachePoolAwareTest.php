<?php

namespace Charcoal\Tests\Cache;

// From 'tedivm/stash'
use Stash\Pool;
// From 'charcoal-cache'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Mocks\CachePoolAware;
use Charcoal\Cache\CachePoolAwareTrait;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CachePoolAwareTrait::class)]
class CachePoolAwareTest extends AbstractTestCase
{
    /**
     * @covers ::setCachePool
     * @covers ::cachePool
     */
    public function testCachePool()
    {
        $obj  = new CachePoolAware();
        $pool = new Pool();

        $obj->setCachePool($pool);
        $this->assertSame($pool, $obj->cachePool());
    }

    /**
     * testSetPrefixOnInvalidValue
     * @covers ::cachePool
     */
    public function testMissingPool()
    {
        $this->expectExceptionMessage('Cache Pool is not defined for "Charcoal\Tests\Mocks\CachePoolAware"');
        $this->expectException(\RuntimeException::class);
        $obj = new CachePoolAware();
        $obj->cachePool();
    }
}
