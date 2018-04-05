<?php

namespace Charcoal\Cache\ServiceProvider;

// From Pimple
use Pimple\ServiceProviderInterface;
use Pimple\Container;

// From 'tedivm/stash'
use Stash\DriverList;
use Stash\Interfaces\DriverInterface;
use Stash\Pool;

// From 'charcoal-cache'
use Charcoal\Cache\CacheBuilder;
use Charcoal\Cache\CacheConfig;

/**
 * Cache Service Provider
 *
 * Provides a Stash cache pool (PSR-6 compatible).
 *
 * ## Dependencies
 *
 * - `config`: An {@link https://packagist.org/packages/locomotivemtl/charcoal-app application configset}.
 * - `logger` A {@link https://packagist.org/providers/psr/log-implementation PSR-3 logging client}.
 *
 * ## Services
 *
 * - `cache`: The default PSR-6 cache pool
 *
 * ## Helpers
 *
 * - `cache/config`: The cache configset.
 * - `cache/driver`: The default cache driver.
 * - `cache/factory`: A simple cache pool factory.
 * - `cache/builder`: An advacned cache pool factory.
 */
class CacheServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A container instance.
     * @return void
     */
    public function register(Container $container)
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
                return new $drivers['Apc']([
                    'ttl'       => $container['cache/config']['default_ttl'],
                    'namespace' => $container['cache/config']['prefix'],
                ]);
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
                    'servers' => []
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

        /**
         * The main cache driver.
         *
         * @param  Container $container The service container.
         * @return DriverInterface Primary cache driver from Stash.
         */
        $container['cache/driver'] = function (Container $container) {
            $cacheConfig = $container['cache/config'];

            if ($cacheConfig['active'] === true) {
                $cacheTypes = $cacheConfig['types'];
                foreach ($cacheTypes as $type) {
                    if (isset($container['cache/drivers'][$type])) {
                        return $container['cache/drivers'][$type];
                    }
                }
            }

            /**
             * If no working drivers were available
             * or the cache is disabled,
             * use the memory driver.
             */
            return $container['cache/drivers']['memory'];
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
         * A simple cache pool factory, using Stash and the shared "memory" driver.
         *
         * @param  Container $container A Pimple DI container.
         * @return Pool
         */
        $container['cache/factory'] = $container->factory(function (Container $container) {
            return new Pool($container['cache/drivers']['memory']);
        });

        /**
         * The main cache item pool.
         *
         * @param  Container $container The service container.
         * @return Pool The cache item pool from Stash.
         */
        $container['cache'] = function (Container $container) {
            $cacheDriver  = $container['cache/driver'];
            $cacheBuilder = $container['cache/builder'];

            $pool = $cacheBuilder($cacheDriver);
            return $pool;
        };
    }
}
