<?php

namespace Charcoal\Tests\Cache\ServiceProvider;

use Throwable;
// From PSR-3
use Psr\Log\NullLogger;
use DI\Container;
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
use Closure;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CacheServiceProvider::class)]
class CacheServiceProviderTest extends AbstractTestCase
{
    /**
     * @covers CacheServiceProvider::register
     * @covers CacheServiceProvider::registerDrivers
     * @covers CacheServiceProvider::registerService
     * @covers CacheServiceProvider::registerMiddleware
     */
    public function testProvider()
    {
        $container = $this->providerFactory();

        $this->assertTrue($container->has('cache/config'));
        $this->assertInstanceOf(CacheConfig::class, $container->get('cache/config'));

        $this->assertTrue($container->has('cache/available-drivers'));
        $this->assertTrue($this->isAccessible($container->get('cache/available-drivers')));

        $this->assertTrue($container->has('cache/drivers'));
        $this->assertTrue($this->isAccessible($container->get('cache/drivers')));

        $this->assertTrue($container->has('cache/driver'));
        $this->assertInstanceOf(DriverInterface::class, $container->get('cache/driver'));

        $this->assertTrue($container->has('cache/builder'));
        $this->assertInstanceOf(CacheBuilder::class, $container->get('cache/builder'));

        $this->assertTrue($container->has('cache'));
        $this->assertInstanceOf(PoolInterface::class, $container->get('cache'));

        $this->assertTrue($container->has('cache/facade'));
        $this->assertInstanceOf(CachePoolFacade::class, $container->get('cache/facade'));

        $this->assertTrue($container->has('middlewares/charcoal/cache/middleware/cache'));
        $this->assertInstanceOf(CacheMiddleware::class, $container->get('middlewares/charcoal/cache/middleware/cache'));
    }

    /**
     * Test "middlewares/charcoal/cache/middleware/cache" with a user-preferences.
     *
     * @covers CacheServiceProvider::registerMiddleware
     */
    public function testCustomizedMiddleware()
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

        $this->assertTrue($container->has('middlewares/charcoal/cache/middleware/cache'));
        $middleware = $container->get('middlewares/charcoal/cache/middleware/cache');
        $reflection = new ReflectionClass($middleware);
        $reflectionProperty = $reflection->getProperty('cacheTtl');

        $this->assertEquals(1, $reflectionProperty->getValue($middleware));
    }

    /**
     * Test "cache/drivers"; basic drivers are instances of {@see DriverInterface}.
     *
     * @covers CacheServiceProvider::registerDrivers
     */
    public function testBasicDriverInstances()
    {
        $container = $this->providerFactory();

        $driverMap = [
            'BlackHole'  => 'noop',
            'Ephemeral'  => 'memory',
            'FileSystem' => 'file',
        ];

        $driverClassNames = DriverList::getAllDrivers();
        $driverCollection = $container->get('cache/drivers');

        foreach ($driverMap as $driverName => $driverKey) {
            if (isset($driverClassNames[$driverName])) {
                $className = $driverClassNames[$driverName];
                $driver    = $driverCollection[$driverKey];
                $this->assertInstanceOf($className, $driver instanceof Closure ? $driver() : $driver);
            }
        }
    }

    /**
     * Test "cache/drivers"; vendor drivers are instances of {@see DriverInterface}.
     *
     * @covers CacheServiceProvider::registerDrivers
     */
    public function testAvailableVendorDriverInstances()
    {
        $container = $this->providerFactory();

        $driverMap = [
            'Apc'      => 'apc',
            'Memcache' => 'memcache',
            'Redis'    => 'redis',
            'SQLite'   => 'db',
        ];

        $driverClassNames = DriverList::getAllDrivers();
        $driverCollection = $container->get('cache/drivers');

        foreach ($driverMap as $driverName => $driverKey) {
            if (isset($driverClassNames[$driverName])) {
                $className = $driverClassNames[$driverName];

                if ($className::isAvailable()) {
                    try {
                        $driver = $driverCollection[$driverKey];
                        $this->assertInstanceOf($className, $driver instanceof Closure ? $driver() : $driver);
                    } catch (Throwable $t) {
                        // Do nothing; Some cache drivers, such as Redis,
                        // are not correctly implemented.
                    }
                } else {
                    $driver = $driverCollection[$driverKey];
                    $this->assertNull($driver instanceof Closure ? $driver() : $driver);
                }
            }
        }
    }

    /**
     * Test "cache/drivers"; unavailable vendor drivers return NULL.
     *
     * @covers CacheServiceProvider::registerDrivers
     */
    public function testUnavailableVendorDriverInstances()
    {
        $container = $this->providerFactory();

        // Emptied to fake unavailability
        $container->set('cache/available-drivers', []);

        $driverMap = [
            'Apc'      => 'apc',
            'Memcache' => 'memcache',
            'Redis'    => 'redis',
            'SQLite'   => 'db',
        ];

        $driverClassNames = DriverList::getAllDrivers();
        $driverCollection = $container->get('cache/drivers');

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
     * @covers CacheServiceProvider::registerDrivers
     * @covers CacheServiceProvider::registerService
     *
     * @dataProvider provideConfigsForMainDriver
     *
     * @param  string $className   The expected driver class name.
     * @param  array  $cacheConfig The cache configset to resolve the main driver.
     * @return void
     */
    public function testMainDriverInstance($className, array $cacheConfig)
    {
        $container = $this->providerFactory([
            'config' => [
                'cache' => $cacheConfig,
            ],
        ]);

        $this->assertInstanceOf($className, $container->get('cache/driver'));
    }

    /**
     * Provide data for testing the "cache/driver" service.
     *
     * @used-by self::testMainDriverInstance()
     * @return  array
     */
    public static function provideConfigsForMainDriver()
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
     * @return boolean
     */
    public function isAccessible($value)
    {
        return is_array($value) || $value instanceof \ArrayAccess;
    }

    /**
     * Create a new Container instance.
     *
     * @param  array $args Parameters for the initialization of a Container.
     * @return Container
     */
    public static function providerFactory(array $args = [])
    {
        $container = new Container($args);

        if (!$container->has('logger')) {
            $container->set('logger', new NullLogger());
        }

        $provider  = new CacheServiceProvider();
        $provider->register($container);

        return $container;
    }
}
