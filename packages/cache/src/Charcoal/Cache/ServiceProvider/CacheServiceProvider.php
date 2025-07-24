<?php

namespace Charcoal\Cache\ServiceProvider;

use Charcoal\Cache\Facade\CachePoolFacade;
use DI\Container;
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
class CacheServiceProvider
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
        $container->set('cache/available-drivers', DriverList::getAvailableDrivers());

        /**
         * The collection of cache driver instances.
         *
         * @param  Container $container The service container.
         * @return Container Service container of cache drivers from Stash.
         */
        $container->set('cache/drivers', function (Container $container) {
            $drivers = [];

            $available = $container->get('cache/available-drivers');

            // APC
            $drivers['apc'] = function () use ($container, $available) {
                if (!isset($available['Apc'])) {
                    return null;
                }
                $cacheConfig   = $container->get('cache/config');
                $driverOptions = [
                    'ttl'       => $cacheConfig['default_ttl'],
                    'namespace' => $cacheConfig['prefix'],
                ];
                $class = $available['Apc'];
                return new $class($driverOptions);
            };

            // SQLite
            $drivers['db'] = function () use ($available) {
                if (!isset($available['SQLite'])) {
                    return null;
                }
                $class = $available['SQLite'];
                return new $class();
            };

            // FileSystem
            $drivers['file'] = function () use ($available) {
                $class = $available['FileSystem'];
                return new $class();
            };

            // Memcache
            $drivers['memcache'] = function () use ($container, $available) {
                if (!isset($available['Memcache'])) {
                    return null;
                }
                $cacheConfig   = $container->get('cache/config');
                $driverOptions = [ 'servers' => [] ];
                if (isset($cacheConfig['servers'])) {
                    $servers = [];
                    foreach ($cacheConfig['servers'] as $server) {
                        $servers[] = array_values($server);
                    }
                    $driverOptions['servers'] = $servers;
                } else {
                    $driverOptions['servers'][] = [ '127.0.0.1', 11211 ];
                }
                $class = $available['Memcache'];
                return new $class($driverOptions);
            };

            // Ephemeral
            $drivers['memory'] = new $available['Ephemeral']();

            // BlackHole
            $drivers['noop'] = new $available['BlackHole']();

            // Redis
            /*if (!empty($available['Redis'])) {
                $drivers['redis'] = new ($available['Redis'])();
            }*/

            return $drivers;
        });
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
        $container->set('cache/config', function (Container $container) {
            $appConfig   = $container->has('config') ? $container->get('config') : [];
            $cacheConfig = isset($appConfig['cache']) ? $appConfig['cache'] : null;
            return new CacheConfig($cacheConfig);
        });

        /**
         * A cache pool builder, using Stash.
         *
         * @param  Container $container A DI Container.
         * @return CacheBuilder
         */
        $container->set('cache/builder', function (Container $container) {
            $cacheConfig = $container->get('cache/config');

            return new CacheBuilder([
                'logger'     => $container->get('logger'),
                'drivers'    => $container->get('cache/drivers'),
                'namespace'  => $cacheConfig['prefix'],
            ]);
        });

        /**
         * The driver of the main cache pool "cache".
         *
         * @param  Container $container The service container.
         * @return DriverInterface Primary cache driver from Stash.
         */
        $container->set('cache/driver', function (Container $container) {
            return $container->get('cache')->getDriver();
        });

        /**
         * The main cache item pool.
         *
         * @param  Container $container The service container.
         * @return Pool The cache item pool from Stash.
         */
        $container->set('cache', function (Container $container) {
            $cacheBuilder = $container->get('cache/builder');
            $cacheConfig  = $container->get('cache/config');

            if ($cacheConfig['active'] === true) {
                $cacheDrivers = $cacheConfig['types'];
            } else {
                $cacheDrivers = $cacheConfig['default_types'];
            }

            return $cacheBuilder($cacheDrivers);
        });

        /**
         * The facade for the main cache pool.
         *
         * @param  Container $container The service container.
         * @return CachePoolFacade The facade for the main cache pool.
         */
        $container->set('cache/facade', function (Container $container) {
            $args = [
                'cache' => $container->get('cache'),
            ];

            $cacheConfig = $container->get('cache/config');
            if (isset($cacheConfig['default_ttl'])) {
                $args['default_ttl'] = $cacheConfig['default_ttl'];
            }

            return new CachePoolFacade($args);
        });
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
        $container->set('cache/middleware/config', function (Container $container) {
            $appConfig = $container->has('config') ? $container->get('config') : [];

            if (isset($appConfig['middlewares']['charcoal/cache/middleware/cache'])) {
                $wareConfig = $appConfig['middlewares']['charcoal/cache/middleware/cache'];
            } else {
                $wareConfig = [];
            }

            $wareConfig['cache'] = $container->get('cache');

            return $wareConfig;
        });

        /**
         * The middleware for caching HTTP responses.
         *
         * @param  Container $container A container instance.
         * @return CacheMiddleware
         */
        $container->set('middlewares/charcoal/cache/middleware/cache', function (Container $container) {
            return new CacheMiddleware($container->get('cache/middleware/config'));
        });
    }
}
