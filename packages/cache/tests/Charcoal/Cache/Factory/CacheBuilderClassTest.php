<?php

namespace Charcoal\Tests\Cache\Factory;

use InvalidArgumentException;

// From PSR-3
use Psr\Log\NullLogger;

// From 'tedivm/stash'
use Stash\Interfaces\ItemInterface;
use Stash\Interfaces\PoolInterface;
use Stash\Pool;

/**
 * Test constructor and class attributes from the CacheBuilder.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Charcoal\Cache\CacheBuilder::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheBuilder::class, '__construct')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheBuilder::class, 'setDrivers')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheBuilder::class, 'isAccessible')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheBuilder::class, 'setLogger')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheBuilder::class, 'setNamespace')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheBuilder::class, 'setItemClass')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\CacheBuilder::class, 'setPoolClass')]
class CacheBuilderClassTest extends AbstractCacheBuilderTest
{
    public function testSetDriversWithInvalidType(): void
    {
        $this->expectExceptionMessage('Driver list must be an accessible array');
        $this->expectException(InvalidArgumentException::class);
        $this->createBuilder([
            'drivers' => false,
        ]);
    }

    public function testSetLoggerWithInvalidType(): void
    {
        $this->expectExceptionMessage('Expected an instance of Psr\Log\LoggerInterface');
        $this->expectException(InvalidArgumentException::class);
        $this->createBuilder([
            'logger' => new \stdClass(),
        ]);
    }

    public function testSetNamespace(): void
    {
        $driver  = $this->createDriver('BlackHole');
        $builder = $this->createBuilder([
            'namespace' => 'qux',
        ]);

        /** 1. Pool's Namespace */
        $pool = $builder($driver);
        $this->assertEquals('qux', $pool->getNamespace());

        /** 2. Overridden namespace */
        $pool = $builder($driver, 'foo');
        $this->assertEquals('foo', $pool->getNamespace());
    }

    public function testSetInvalidNamespace(): void
    {
        $this->expectExceptionMessage('Namespace must be alphanumeric');
        $this->expectException(InvalidArgumentException::class);
        $this->createBuilder([
            'namespace' => '!@#$%^&*(',
        ]);
    }

    public function testSetItemClass(): void
    {
        $mockItem      = $this->createStub(ItemInterface::class);
        $mockClassName = $mockItem::class;

        $driver  = $this->createDriver('BlackHole');
        $builder = $this->createBuilder([
            'item_class' => $mockClassName,
        ]);

        /** 1. Pool's Item Class */
        $pool = $builder($driver);
        $item = $pool->getItem('test');
        $this->assertInstanceOf($mockClassName, $item);
    }

    public function testSetFakeItemClass(): void
    {
        $this->expectExceptionMessage('Item class FakeClassName does not exist');
        $this->expectException(InvalidArgumentException::class);
        $this->createBuilder([
            'item_class' => 'FakeClassName',
        ]);
    }

    public function testSetInvalidItemClass(): void
    {
        $this->expectExceptionMessage('Item class stdClass must inherit from Stash\Interfaces\ItemInterface');
        $this->expectException(InvalidArgumentException::class);
        $this->createBuilder([
            'item_class' => 'stdClass',
        ]);
    }

    public function testSetPoolClass(): void
    {
        $mockPool      = $this->createStub(PoolInterface::class);
        $mockClassName = $mockPool::class;

        $driver  = $this->createDriver('BlackHole');
        $builder = $this->createBuilder([
            'pool_class' => $mockClassName,
        ]);

        // Predefined pool class
        $pool = $builder($driver);
        $this->assertInstanceOf($mockClassName, $pool);
    }

    public function testSetFakePoolClass(): void
    {
        $this->expectExceptionMessage('Pool class FakeClassName does not exist');
        $this->expectException(InvalidArgumentException::class);
        $this->createBuilder([
            'pool_class' => 'FakeClassName',
        ]);
    }

    public function testSetInvalidPoolClass(): void
    {
        $this->expectExceptionMessage('Pool class stdClass must inherit from Stash\Interfaces\PoolInterface');
        $this->expectException(InvalidArgumentException::class);
        $this->createBuilder([
            'pool_class' => 'stdClass',
        ]);
    }
}
