<?php

namespace Charcoal\Tests\Cache\Factory;

use InvalidArgumentException;
// From 'tedivm/stash'
use Stash\Interfaces\ItemInterface;
use Stash\Interfaces\PoolInterface;
use Charcoal\Cache\CacheBuilder;
use PHPUnit\Framework\Attributes\CoversMethod;

/**
 * Test constructor and class attributes from the CacheBuilder.
 */
#[CoversMethod(CacheBuilder::class, '__construct')]
#[CoversMethod(CacheBuilder::class, 'setDrivers')]
#[CoversMethod(CacheBuilder::class, 'isAccessible')]
#[CoversMethod(CacheBuilder::class, 'setLogger')]
#[CoversMethod(CacheBuilder::class, 'setNamespace')]
#[CoversMethod(CacheBuilder::class, 'setItemClass')]
#[CoversMethod(CacheBuilder::class, 'setPoolClass')]
class CacheBuilderClassTest extends AbstractCacheBuilderTestCase
{
    public function testSetDriversWithInvalidType()
    {
        $this->expectExceptionMessage('Driver list must be an accessible array');
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->createBuilder([
            'drivers' => false,
        ]);
    }

    public function testSetLoggerWithInvalidType()
    {
        $this->expectExceptionMessage('Expected an instance of Psr\Log\LoggerInterface');
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->createBuilder([
            'logger' => new \stdClass(),
        ]);
    }

    public function testSetNamespace()
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

    public function testSetInvalidNamespace()
    {
        $this->expectExceptionMessage('Namespace must be alphanumeric');
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->createBuilder([
            'namespace' => '!@#$%^&*(',
        ]);
    }

    public function testSetItemClass()
    {
        $itemClass = \Stash\Item::class;

        $driver  = $this->createDriver('BlackHole');
        $builder = $this->createBuilder([
            'item_class' => $itemClass,
        ]);

        /** 1. Pool's Item Class */
        $pool = $builder($driver);
        $item = $pool->getItem('test');
        $this->assertInstanceOf($itemClass, $item);
    }

    public function testSetFakeItemClass()
    {
        $this->expectExceptionMessage('Item class FakeClassName does not exist');
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->createBuilder([
            'item_class' => 'FakeClassName',
        ]);
    }

    public function testSetInvalidItemClass()
    {
        $this->expectExceptionMessage('Item class stdClass must inherit from Stash\Interfaces\ItemInterface');
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->createBuilder([
            'item_class' => 'stdClass',
        ]);
    }

    public function testSetPoolClass()
    {
        $mockPool      = $this->createMock(PoolInterface::class);
        $mockClassName = get_class($mockPool);

        $driver  = $this->createDriver('BlackHole');
        $builder = $this->createBuilder([
            'pool_class' => $mockClassName,
        ]);

        // Predefined pool class
        $pool = $builder($driver);
        $this->assertInstanceOf($mockClassName, $pool);
    }

    public function testSetFakePoolClass()
    {
        $this->expectExceptionMessage('Pool class FakeClassName does not exist');
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->createBuilder([
            'pool_class' => 'FakeClassName',
        ]);
    }

    public function testSetInvalidPoolClass()
    {
        $this->expectExceptionMessage('Pool class stdClass must inherit from Stash\Interfaces\PoolInterface');
        $this->expectException(InvalidArgumentException::class);
        $builder = $this->createBuilder([
            'pool_class' => 'stdClass',
        ]);
    }
}
