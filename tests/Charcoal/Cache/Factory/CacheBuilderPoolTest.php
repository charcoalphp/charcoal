<?php

namespace Charcoal\Tests\Cache\Factory;

// From PSR-3
use Psr\Log\NullLogger;

// From 'tedivm/stash'
use Stash\Interfaces\ItemInterface;
use Stash\Interfaces\PoolInterface;

/**
 * Test pool creation and pool attributes from the CacheBuilder.
 *
 * @coversDefaultClass \Charcoal\Cache\CacheBuilder
 */
class CacheBuilderPoolTest extends AbstractCacheBuilderTest
{
    /**
     * Asserts that the Pool logger can be assigned from build options.
     *
     * @covers ::parsePoolOptions
     * @covers ::applyPoolOptions
     */
    public function testBuildWithLoggerOnOptions()
    {
        $builder = $this->createBuilder();
        $logger  = new NullLogger;

        $pool = $builder('Ephemeral', [
            'logger' => $logger,
        ]);
        $this->assertAttributeSame($logger, 'logger', $pool);
    }

    /**
     * Asserts that the Pool namespace can be customized from build options.
     *
     * @covers ::parsePoolOptions
     * @covers ::applyPoolOptions
     */
    public function testBuildWithNamespaceOnOptions()
    {
        $builder = $this->createBuilder();

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
     * Asserts that the Item class can be customized from build options.
     *
     * @covers ::parsePoolOptions
     * @covers ::applyPoolOptions
     */
    public function testBuildWithItemClassOnOptions()
    {
        $builder = $this->createBuilder();

        $mockItem      = $this->createMock(ItemInterface::class);
        $mockClassName = get_class($mockItem);

        $pool = $builder('Ephemeral', [
            'item_class' => $mockClassName,
        ]);
        $this->assertAttributeEquals($mockClassName, 'itemClass', $pool);
    }

    /**
     * Asserts that the Pool class can be customized from build options.
     *
     * @covers ::parsePoolOptions
     * @covers ::applyPoolOptions
     */
    public function testBuildWithPoolClassOnOptions()
    {
        $builder = $this->createBuilder();

        $mockPool      = $this->createMock(PoolInterface::class);
        $mockClassName = get_class($mockPool);

        // Custom Pool Class
        $pool = $builder('Ephemeral', [
            'pool_class' => $mockClassName,
        ]);
        $this->assertInstanceOf($mockClassName, $pool);
    }
}
