<?php

namespace Charcoal\Tests\Cache\ServiceProvider;

use Throwable;

// From PSR-3
use Psr\Log\NullLogger;

// From Pimple
use Pimple\Container;

// From 'tedivm/stash'
use ReflectionClass;
use Stash\DriverList;
use Stash\Interfaces\DriverInterface;
use Stash\Interfaces\PoolInterface;

// From 'charcoal-cache'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Cache\CacheBuilder;
use Charcoal\Cache\CacheConfig;
use Charcoal\Cache\Facade\CachePoolFacade;
use Charcoal\Cache\Middleware\CacheMiddleware;
use Charcoal\Cache\ServiceProvider\CacheServiceProvider;

/**
 * Test CacheServiceProvider
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Charcoal\Cache\ServiceProvider\CacheServiceProvider::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\ServiceProvider\CacheServiceProvider::class, 'register')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\ServiceProvider\CacheServiceProvider::class, 'registerDrivers')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\ServiceProvider\CacheServiceProvider::class, 'registerService')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Cache\ServiceProvider\CacheServiceProvider::class, 'registerMiddleware')]
class CacheServiceProviderTest extends AbstractTestCase
{
    public function testProvider(): void
    {
        $container = $this->providerFactory();

        $this->assertArrayHasKey('cache/config', $container);
        $this->assertInstanceOf(CacheConfig::class, $container['cache/config']);

        $this->assertArrayHasKey('cache/available-drivers', $container);
        $this->assertTrue($this->isAccessible($container['cache/available-drivers']));

        $this->assertArrayHasKey('cache/drivers', $container);
        $this->assertTrue($this->isAccessible($container['cache/drivers']));

        $this->assertArrayHasKey('cache/driver', $container);
        $this->assertInstanceOf(DriverInterface::class, $container['cache/driver']);

        $this->assertArrayHasKey('cache/builder', $container);
        $this->assertInstanceOf(CacheBuilder::class, $container['cache/builder']);

        $this->assertArrayHasKey('cache', $container);
        $this->assertInstanceOf(PoolInterface::class, $container['cache']);

        $this->assertArrayHasKey('cache/facade', $container);
        $this->assertInstanceOf(CachePoolFacade::class, $container['cache/facade']);

        $this->assertArrayHasKey('middlewares/charcoal/cache/middleware/cache', $container);
        $this->assertInstanceOf(CacheMiddleware::class, $container['middlewares/charcoal/cache/middleware/cache']);
    }

    /**
     * Test "middlewares/charcoal/cache/middleware/cache" with a user-preferences.
     */
    public function testCustomizedMiddleware(): void
    {
        $container = $this->providerFactory([
            'config' => [
                'middlewares' => [
                    'charcoal/cache/middleware/cache' => [
                        'ttl' => 1,
                    ],
                ],
            ],
        ]);

        $this->assertArrayHasKey('middlewares/charcoal/cache/middleware/cache', $container);
        $middleware = $container['middlewares/charcoal/cache/middleware/cache'];
        $reflection = new ReflectionClass($middleware);
        $reflectionProperty = $reflection->getProperty('cacheTtl');

        $this->assertEquals(1, $reflectionProperty->getValue($middleware));
    }

    /**
     * Test "cache/drivers"; basic drivers are instances of {@see DriverInterface}.
     */
    public function testBasicDriverInstances(): void
    {
        $container = $this->providerFactory();

        $driverMap = [
            'BlackHole'  => 'noop',
            'Ephemeral'  => 'memory',
            'FileSystem' => 'file',
        ];

        $driverClassNames = DriverList::getAllDrivers();
        $driverCollection = $container['cache/drivers'];

        foreach ($driverMap as $driverName => $driverKey) {
            if (isset($driverClassNames[$driverName])) {
                $className = $driverClassNames[$driverName];
                $driver    = $driverCollection[$driverKey];
                $this->assertInstanceOf($className, $driver);
            }
        }
    }

    /**
     * Test "cache/drivers"; vendor drivers are instances of {@see DriverInterface}.
     */
    public function testAvailableVendorDriverInstances(): void
    {
        $container = $this->providerFactory();

        $driverMap = [
            'Apc'      => 'apc',
            'Memcache' => 'memcache',
            'Redis'    => 'redis',
            'SQLite'   => 'db',
        ];

        $driverClassNames = DriverList::getAllDrivers();
        $driverCollection = $container['cache/drivers'];

        foreach ($driverMap as $driverName => $driverKey) {
            if (isset($driverClassNames[$driverName])) {
                $className = $driverClassNames[$driverName];

                if ($className::isAvailable()) {
                    try {
                        $driver = $driverCollection[$driverKey];
                        $this->assertInstanceOf($className, $driver);
                    } catch (Throwable) {
                        // Do nothing; Some cache drivers, such as Redis,
                        // are not correctly implemented.
                    }
                } else {
                    $driver = $driverCollection[$driverKey];
                    $this->assertNull($driver);
                }
            }
        }
    }

    /**
     * Test "cache/drivers"; unavailable vendor drivers return NULL.
     */
    public function testUnavailableVendorDriverInstances(): void
    {
        $container = $this->providerFactory();

        // Emptied to fake unavailability
        $container['cache/available-drivers'] = [];

        $driverMap = [
            'Apc'      => 'apc',
            'Memcache' => 'memcache',
            'Redis'    => 'redis',
            'SQLite'   => 'db',
        ];

        $driverClassNames = DriverList::getAllDrivers();
        $driverCollection = $container['cache/drivers'];

        foreach ($driverMap as $driverName => $driverKey) {
            if (isset($driverClassNames[$driverName])) {
                $driver = $driverCollection[$driverKey];
                $this->assertNull($driver);
            }
        }
    }

    /**
     * Assert "cache/driver" resolves as expected.
     *
     *
     *
     * @param  string $className   The expected driver class name.
     * @param  array  $cacheConfig The cache configset to resolve the main driver.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideConfigsForMainDriver')]
    public function testMainDriverInstance(string $className, array $cacheConfig): void
    {
        $container = $this->providerFactory([
            'config' => [
                'cache' => $cacheConfig,
            ],
        ]);

        $this->assertInstanceOf($className, $container['cache/driver']);
    }

    /**
     * Provide data for testing the "cache/driver" service.
     *
     * @used-by self::testMainDriverInstance()
     */
    public static function provideConfigsForMainDriver(): array
    {
        $driverClassNames = DriverList::getAvailableDrivers();

        return [
            'Cache: Enabled' => [
                $driverClassNames['BlackHole'],
                [
                    'active' => true,
                    'types'  => [ 'noop' ],
                ],
            ],
            'Cache: Disabled' => [
                $driverClassNames['Ephemeral'],
                [
                    'active' => false,
                    'types'  => [ 'noop' ],
                ],
            ],
        ];
    }

    /**
     * Return all the keys or a subset of the keys of an array.
     *
     * @param  mixed $value The variable containing keys to return.
     * @return array
     */
    public function getKeys($value)
    {
        if (is_array($value)) {
            return array_keys($value);
        } elseif (is_callable([ $value, 'keys' ])) {
            return $value->keys();
        }

        return [];
    }

    /**
     * Determine whether the given value is array accessible.
     *
     * @param  mixed $value The variable being evaluated.
     */
    public function isAccessible($value): bool
    {
        return is_array($value) || $value instanceof \ArrayAccess;
    }

    /**
     * Create a new Container instance.
     *
     * @param  array $args Parameters for the initialization of a Container.
     */
    public function providerFactory(array $args = []): \Pimple\Container
    {
        $container = new Container($args);

        if (!isset($container['logger'])) {
            $container['logger'] = new NullLogger();
        }

        $provider  = new CacheServiceProvider();
        $provider->register($container);

        return $container;
    }
}
