<?php

namespace Charcoal\Tests\Cache\Factory;

// From PSR-3
use Psr\Log\NullLogger;

// From 'tedivm/stash'
use Stash\Interfaces\ItemInterface;
use Stash\Interfaces\PoolInterface;

// From 'charcoal-cache'
use Charcoal\Cache\CacheBuilder;

/**
 * Test CacheBuilder with custom pool options.
 *
 * @coversDefaultClass \Charcoal\Cache\CacheBuilder
 */
class CacheBuilderOptionsTest extends AbstractCacheBuilderTest
{
    /**
     * @covers ::parsePoolOptions
     * @covers ::applyPoolOptions
     */
    public function testBuildWithLogger()
    {
        $builder = $this->builderFactory();
        $logger  = new NullLogger;

        $pool = $builder('Ephemeral', [
            'logger' => $logger,
        ]);
        $this->assertAttributeSame($logger, 'logger', $pool);
    }

    /**
     * @covers ::parsePoolOptions
     * @covers ::applyPoolOptions
     */
    public function testBuildWithNamespace()
    {
        $builder = $this->builderFactory();

        // Accepts namespace as a shortcut
        $pool = $builder('Ephemeral', 'foo');
        $this->assertEquals('foo', $pool->getNamespace());

        // Accepts namespace from a dataset
        $pool = $builder('Ephemeral', [
            'namespace' => 'baz',
        ]);
        $this->assertEquals('baz', $pool->getNamespace());
    }

    /**
     * @covers ::parsePoolOptions
     * @covers ::applyPoolOptions
     */
    public function testBuildWithItemClass()
    {
        $builder = $this->builderFactory();

        $mockItem      = $this->createMock(ItemInterface::class);
        $mockClassName = get_class($mockItem);

        $pool = $builder('Ephemeral', [
            'item_class' => $mockClassName,
        ]);
        $this->assertAttributeEquals($mockClassName, 'itemClass', $pool);
    }

    /**
     * @covers ::parsePoolOptions
     * @covers ::applyPoolOptions
     */
    public function testBuildWithPoolClass()
    {
        $builder = $this->builderFactory();

        $mockPool      = $this->createMock(PoolInterface::class);
        $mockClassName = get_class($mockPool);

        // Custom Pool Class
        $pool = $builder('Ephemeral', [
            'pool_class' => $mockClassName,
        ]);
        $this->assertInstanceOf($mockClassName, $pool);
    }
}
