<?php

namespace Charcoal\Cache\ServiceProvider;

// From Pimple
use Charcoal\Cache\Facade\CachePoolFacade;
use Pimple\ServiceProviderInterface;
use Pimple\Container;
// From 'tedivm/stash'
use Stash\DriverList;
use Stash\Interfaces\DriverInterface;
use Stash\Pool;
// From 'charcoal-cache'
use Charcoal\Cache\CacheBuilder;
use Charcoal\Cache\CacheConfig;
use Charcoal\Cache\Middleware\CacheMiddleware;

/**
 * Cache Service Provider
 *
 * Provides a Stash cache pool (PSR-6 compatible).
 *
 * ## Dependencies
 *
 * - `config`: An {@link https://packagist.org/packages/charcoal/app application configset}.
 * - `logger` A {@link https://packagist.org/providers/psr/log-implementation PSR-3 logging client}.
 *
 * ## Services
 *
 * - `cache`: The default PSR-6 cache pool
 *
 * ## Helpers
 *
 * - `cache/config`: The cache configset.
 * - `cache/driver`: The cache driver of the default pool.
 * - `cache/builder`: An advacned cache pool factory.
 *
 * ## Middleware
 *
 * - `middlewares/charcoal/cache/middleware/cache`: For caching HTTP responses.
 *
 */
class CacheServiceProvider implements ServiceProviderInterface
{
    /**
     * @param  Container $container A container instance.
     * @return void
     */
    public function register(Container $container)
    {
        $this->registerDrivers($container);
        $this->registerService($container);
        $this->registerMiddleware($container);
    }

    /**
     * @param  Container $container A container instance.
     * @return void
     */
    public function registerDrivers(Container $container)
    {
        /**
         * The collection of cache drivers that are supported by this system.
         *
         * @var array An associative array structured as `"Driver Name" => "Class Name"`.
         */
        $container['cache/available-drivers'] = DriverList::getAvailableDrivers();

        /**
         * The collection of cache driver instances.
         *
         * @param  Container $container The service container.
         * @return Container Service container of cache drivers from Stash.
         */
        $container['cache/drivers'] = function (Container $container) {
            $drivers = new Container();

            /**
             * @param  Container $container The service container.
             * @return \Stash\Driver\Apc|null
             */
            $drivers['apc'] = function () use ($container) {
                $drivers = $container['cache/available-drivers'];
                if (!isset($drivers['Apc'])) {
                    // Apc is not available on system
                    return null;
                }

                $cacheConfig   = $container['cache/config'];
                $driverOptions = [
                    'ttl'       => $cacheConfig['default_ttl'],
                    'namespace' => $cacheConfig['prefix'],
                ];

                return new $drivers['Apc']($driverOptions);
            };

            /**
             * @param  Container $container A container instance.
             * @return \Stash\Driver\Sqlite|null
             */
            $drivers['db'] = function () use ($container) {
                $drivers = $container['cache/available-drivers'];
                if (!isset($drivers['SQLite'])) {
                    // SQLite is not available on system
                    return null;
                }

                return new $drivers['SQLite']();
            };

            /**
             * @param  Container $container A container instance.
             * @return \Stash\Driver\FileSystem
             */
            $drivers['file'] = function () use ($container) {
                $drivers = $container['cache/available-drivers'];
                return new $drivers['FileSystem']();
            };

            /**
             * @param  Container $container A container instance.
             * @return \Stash\Driver\Memcache|null
             */
            $drivers['memcache'] = function () use ($container) {
                $drivers = $container['cache/available-drivers'];
                if (!isset($drivers['Memcache'])) {
                    // Memcache is not available on system
                    return null;
                }

                $cacheConfig   = $container['cache/config'];
                $driverOptions = [
                    'servers' => [],
                ];

                if (isset($cacheConfig['servers'])) {
                    $servers = [];
                    foreach ($cacheConfig['servers'] as $server) {
                        $servers[] = array_values($server);
                    }
                    $driverOptions['servers'] = $servers;
                } else {
                    // Default Memcache options: locahost:11211
                    $driverOptions['servers'][] = [ '127.0.0.1', 11211 ];
                }

                return new $drivers['Memcache']($driverOptions);
            };

            /**
             * @param  Container $container A container instance.
             * @return \Stash\Driver\Ephemeral
             */
            $drivers['memory'] = function () use ($container) {
                $drivers = $container['cache/available-drivers'];
                return new $drivers['Ephemeral']();
            };

            /**
             * @param  Container $container A container instance.
             * @return \Stash\Driver\BlackHole
             */
            $drivers['noop'] = function () use ($container) {
                $drivers = $container['cache/available-drivers'];
                return new $drivers['BlackHole']();
            };

            /**
             * @param  Container $container A container instance.
             * @return \Stash\Driver\Redis|null
             */
            $drivers['redis'] = function () use ($container) {
                $drivers = $container['cache/available-drivers'];
                if (!isset($drivers['Redis'])) {
                    // Redis is not available on system
                    return null;
                }

                return new $drivers['Redis']();
            };

            return $drivers;
        };
    }

    /**
     * @param  Container $container A container instance.
     * @return void
     */
    public function registerService(Container $container)
    {
        /**
         * The cache configset.
         *
         * @param  Container $container The service container.
         * @return CacheConfig
         */
        $container['cache/config'] = function (Container $container) {
            $appConfig   = isset($container['config']) ? $container['config'] : [];
            $cacheConfig = isset($appConfig['cache']) ? $appConfig['cache'] : null;
            return new CacheConfig($cacheConfig);
        };

        /**
         * A cache pool builder, using Stash.
         *
         * @param  Container $container A Pimple DI container.
         * @return CacheBuilder
         */
        $container['cache/builder'] = function (Container $container) {
            $cacheConfig = $container['cache/config'];

            return new CacheBuilder([
                'logger'     => $container['logger'],
                'drivers'    => $container['cache/drivers'],
                'namespace'  => $cacheConfig['prefix'],
            ]);
        };

        /**
         * The driver of the main cache pool "cache".
         *
         * @param  Container $container The service container.
         * @return DriverInterface Primary cache driver from Stash.
         */
        $container['cache/driver'] = $container->factory(function (Container $container) {
            return $container['cache']->getDriver();
        });

        /**
         * The main cache item pool.
         *
         * @param  Container $container The service container.
         * @return Pool The cache item pool from Stash.
         */
        $container['cache'] = function (Container $container) {
            $cacheBuilder = $container['cache/builder'];
            $cacheConfig  = $container['cache/config'];

            if ($cacheConfig['active'] === true) {
                $cacheDrivers = $cacheConfig['types'];
            } else {
                $cacheDrivers = $cacheConfig['default_types'];
            }

            return $cacheBuilder($cacheDrivers);
        };

        /**
         * The facade for the main cache pool.
         *
         * @param  Container $container The service container.
         * @return CachePoolFacade The facade for the main cache pool.
         */
        $container['cache/facade'] = function (Container $container) {
            $args = [
                'cache' => $container['cache'],
            ];

            $cacheConfig = $container['cache/config'];
            if (isset($cacheConfig['default_ttl'])) {
                $args['default_ttl'] = $cacheConfig['default_ttl'];
            }

            return new CachePoolFacade($args);
        };
    }

    /**
     * @param  Container $container A container instance.
     * @return void
     */
    private function registerMiddleware(Container $container)
    {
        /**
         * The cache middleware configset.
         *
         * @param  Container $container The service container.
         * @return array
         */
        $container['cache/middleware/config'] = function (Container $container) {
            $appConfig = isset($container['config']) ? $container['config'] : [];

            if (isset($appConfig['middlewares']['charcoal/cache/middleware/cache'])) {
                $wareConfig = $appConfig['middlewares']['charcoal/cache/middleware/cache'];
            } else {
                $wareConfig = [];
            }

            $wareConfig['cache'] = $container['cache'];

            return $wareConfig;
        };

        /**
         * The middleware for caching HTTP responses.
         *
         * @param  Container $container A container instance.
         * @return CacheMiddleware
         */
        $container['middlewares/charcoal/cache/middleware/cache'] = function (Container $container) {
            return new CacheMiddleware($container['cache/middleware/config']);
        };
    }
}
