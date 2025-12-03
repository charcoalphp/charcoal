<?php

namespace Charcoal\Tests\Cache\Factory;

// From PSR-3
use Charcoal\Tests\Mocks\DefaultAwarePool;
use Psr\Log\NullLogger;
use Charcoal\Tests\Mocks\LoggerAwarePool;
// From 'tedivm/stash'
use Stash\Interfaces\ItemInterface;
use Stash\Interfaces\PoolInterface;
use Charcoal\Cache\CacheBuilder;
use PHPUnit\Framework\Attributes\CoversMethod;

/**
 * Test the cache pool creation and pool attributes from the CacheBuilder.
 */
#[CoversMethod(CacheBuilder::class, '__invoke')]
#[CoversMethod(CacheBuilder::class, 'parsePoolOptions')]
#[CoversMethod(CacheBuilder::class, 'applyPoolOptions')]
class CacheBuilderPoolTest extends AbstractCacheBuilderTestCase
{
    /**
     * Asserts that the CacheBuilder is invokable.
     */
    public function testBuildIsInvokable()
    {
        $builder = $this->createBuilder();
        $driver  = $this->createDriver('BlackHole');

        $pool = $builder($driver, []);
        $this->assertInstanceOf(PoolInterface::class, $pool);
    }

    /**
     * Asserts that the Pool logger can be assigned from build options.
     */
    public function testBuildWithLoggerOnOptions()
    {
        $builder = $this->createBuilder();
        $driver  = $this->createDriver('BlackHole');
        $logger  = new NullLogger();

        $pool = $builder($driver, [
            'pool_class' => LoggerAwarePool::class,
            'logger' => $logger,
        ]);
        $this->assertSame($logger, $pool->getLogger());
    }

    /**
     * Asserts that the Pool namespace can be customized from build options.
     */
    public function testBuildWithNamespaceOnOptions()
    {
        $builder = $this->createBuilder();
        $driver  = $this->createDriver('BlackHole');

        // Accepts namespace as a shortcut
        $pool = $builder($driver, 'foo');
        $this->assertEquals('foo', $pool->getNamespace());

        // Accepts namespace from a dataset
        $pool = $builder($driver, [
            'namespace' => 'baz',
        ]);
        $this->assertEquals('baz', $pool->getNamespace());
    }

    /**
     * Asserts that the Item class can be customized from build options.
     */
    public function testBuildWithItemClassOnOptions()
    {
        $builder = $this->createBuilder();
        $driver  = $this->createDriver('BlackHole');

        $itemClass = \Stash\Item::class;

        $pool = $builder($driver, [
            'item_class' => $itemClass,
        ]);
        $item = $pool->getItem('test');

        $this->assertInstanceOf($itemClass, $item);
    }

    /**
     * Asserts that the Pool class can be customized from build options.
     */
    public function testBuildWithPoolClassOnOptions()
    {
        $builder = $this->createBuilder();
        $driver  = $this->createDriver('BlackHole');

        $mockPool      = $this->createMock(PoolInterface::class);
        $mockClassName = get_class($mockPool);

        // Custom Pool Class
        $pool = $builder($driver, [
            'pool_class' => $mockClassName,
        ]);
        $this->assertInstanceOf($mockClassName, $pool);
    }

    /**
     * Asserts that the CacheBuilder uses default options when given NULL.
     */
    public function testBuildWithNullOnOptions()
    {
        $builder = $this->createBuilder();
        $driver  = $this->createDriver('BlackHole');

        $pool = $builder($driver, [
            'pool_class' => DefaultAwarePool::class,
        ]);
        $this->assertCachePoolHasDefaultAttributes($pool);
    }

    /**
     * Asserts that the CacheBuilder uses default options when given NULL.
     */
    public function testBuildWithInvalidTypeOnOptions()
    {
        $builder = $this->createBuilder();
        $driver  = $this->createDriver('BlackHole');

        $pool = $builder($driver, [
            'pool_class' => DefaultAwarePool::class,
            42
        ]);
        $this->assertCachePoolHasDefaultAttributes($pool);
    }
}
