<?php

namespace Charcoal\Model\ServiceProvider;

// From Pimple
use Charcoal\Cms\ConfigInterface;
use Charcoal\Factory\GenericResolver;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;
// From 'charcoal-property'
use Charcoal\Property\PropertyInterface;
use Charcoal\Property\GenericProperty;
// From 'charcoal-core'
use Charcoal\Loader\CollectionLoader;
use Charcoal\Model\Collection;
use Charcoal\Model\ModelInterface;
use Charcoal\Model\Service\MetadataConfig;
use Charcoal\Model\Service\MetadataLoader;
use Charcoal\Model\Service\ModelBuilder;
use Charcoal\Model\Service\ModelLoaderBuilder;
use Charcoal\Source\SourceInterface;
use Charcoal\Source\DatabaseSource;

/**
 * Model Service Providers.
 *
 * ##Container dependencies
 *
 * The following keys are expected to be set on the container
 * (from external sources / providers):
 *
 * - `cache` A PSR-6 caching pool.
 * - `config` A charcoal app config (\Charcoal\Config\ConfigInterface)q
 * - `database` A PDO database instance
 * - `logger` A PSR-3 logger.
 * - `view` A \Charcoal\View\ViewInterface instance
 *
 * ## Services
 *
 * The following services are registered on the container:
 *
 * - `model/factory` A \Charcoal\Factory\FactoryInterface factory to create models.
 * - `model/collection/loader` A collection loader (should not be used).
 */
class ModelServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    public function register(Container $container)
    {
        $this->registerModelDependencies($container);
        $this->registerMetadataDependencies($container);
        $this->registerBuilderDependencies($container);
        $this->registerCollectionDependencies($container);
    }

    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    protected function registerBuilderDependencies(Container $container)
    {
        /**
         * @param Container $container A Pimple DI container.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container['model/factory'] = function (Container $container) {
            return new Factory([
                'base_class' => ModelInterface::class,
                'arguments'  => [ $container['model/dependencies'] ]
            ]);
        };

        /**
         * @param Container $container A Pimple DI container.
         * @return ModelBuilder
         */
        $container['model/builder'] = function (Container $container) {
            return new ModelBuilder([
                'factory'           => $container['model/factory'],
                'metadata_loader'   => $container['metadata/loader'],
                'source_factory'    => $container['source/factory']
            ]);
        };

        /**
         * @param Container $container A Pimple DI container.
         * @return ModelLoaderBuilder
         */
        $container['model/loader/builder'] = function (Container $container) {
            return new ModelLoaderBuilder([
                'factory' => $container['model/factory'],
                'cache'   => $container['cache']
            ]);
        };
    }

    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    protected function registerCollectionDependencies(Container $container)
    {
        /** The default collection class name. */
        $container['model/collection/class'] = Collection::class;

        /**
         * @param Container $container A Pimple DI container.
         * @return \ArrayAccess|\Traversable
         */
        $container['model/collection'] = $container->factory(function (Container $container) {
            return new $container['model/collection/class']();
        });

        /**
         * @param Container $container A Pimple DI container.
         * @return CollectionLoader
         */
        $container['model/collection/loader'] = $container->factory(function (Container $container) {
            $factory = $container['model/collection/loader/factory'];
            return $factory->create($factory->defaultClass());
        });

        /**
         * @param Container $container A Pimple DI container.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container['model/collection/loader/factory'] = function (Container $container) {
            return new Factory([
                'default_class' => CollectionLoader::class,
                'arguments'     => [[
                    'logger'        => $container['logger'],
                    'factory'       => $container['model/factory'],
                    'collection'    => $container['model/collection/class']
                ]]
            ]);
        };
    }

    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    protected function registerModelDependencies(Container $container)
    {
        // The model dependencies might be already set from elsewhere; defines it if not.
        if (!isset($container['model/dependencies'])) {
            /**
             * @param Container $container A Pimple DI container.
             * @return array The model dependencies array.
             */
            $container['model/dependencies'] = function (Container $container) {
                return [
                    'container'        => $container,
                    'logger'           => $container['logger'],
                    'view'             => $container['view'],
                    'property_factory' => $container['property/factory'],
                    'metadata_loader'  => $container['metadata/loader'],
                    'source_factory'   => $container['source/factory']
                ];
            };
        }

        // The property factory might be already set from elsewhere; defines it if not.
        if (!isset($container['property/factory'])) {
            /**
             * @param Container $container A Pimple DI container.
             * @return \Charcoal\Factory\FactoryInterface
             */
            $container['property/factory'] = function (Container $container) {
                return new Factory([
                    'base_class'       => PropertyInterface::class,
                    'default_class'    => GenericProperty::class,
                    'resolver_options' => [
                        'prefix' => '\\Charcoal\\Property\\',
                        'suffix' => 'Property'
                    ],
                    'arguments' => [[
                        'container'  => $container,
                        'database'   => $container['database'],
                        'logger'     => $container['logger'],
                        'translator' => $container['translator']
                    ]]
                ]);
            };
        }

        if (!isset($container['source/factory'])) {
            /**
             * @param Container $container A Pimple DI container.
             * @return \Charcoal\Factory\FactoryInterface
             */
            $container['source/factory'] = function (Container $container) {
                return new Factory([
                    'map' => [
                        'database' => DatabaseSource::class
                    ],
                    'base_class' => SourceInterface::class,
                    'arguments'  => [[
                        'logger' => $container['logger'],
                        'cache'  => $container['cache'],
                        'pdo'    => $container['database']
                    ]]
                ]);
            };
        }
    }

    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    protected function registerMetadataDependencies(Container $container)
    {
        if (!isset($container['metadata/config'])) {
            /**
             * The application's configset for "config.metadata".
             *
             * @param  Container $container Pimple DI container.
             * @return MetadataConfig
             */
            $container['metadata/config'] = function (Container $container) {
                $appConfig  = isset($container['config']) ? $container['config'] : [];
                $metaConfig = isset($appConfig['metadata']) ? $appConfig['metadata'] : null;
                $metaConfig = new MetadataConfig($metaConfig);

                if (isset($container['module/classes'])) {
                    $extraPaths = [];
                    $basePath   = $appConfig['base_path'];
                    $modules    = $container['module/classes'];
                    foreach ($modules as $module) {
                        if (defined(sprintf('%s::APP_CONFIG', $module))) {
                            $configPath = ltrim($module::APP_CONFIG, '/');
                            $configPath = $basePath . DIRECTORY_SEPARATOR . $configPath;

                            $configData = $metaConfig->loadFile($configPath);
                            if (isset($configData['metadata']['paths'])) {
                                $extraPaths = array_merge(
                                    $extraPaths,
                                    $configData['metadata']['paths']
                                );
                            }
                        };
                    }

                    if (!empty($extraPaths)) {
                        $metaConfig->addPaths($extraPaths);
                    }
                }

                return $metaConfig;
            };
        }

        if (!isset($container['metadata/cache'])) {
            /**
             * The application's metadata source cache.
             *
             * @param  Container $container A container instance.
             * @return \Psr\Cache\CacheItemPoolInterface|null
             */
            $container['metadata/cache'] = function (Container $container) {
                $cache = $container['metadata/config']['cache'];
                if (!is_object($cache)) {
                    if (is_bool($cache)) {
                        return $cache
                               ? $container['cache']
                               : $container['cache/builder']->build('memory');
                    }

                    if (is_array($cache)) {
                        return $container['cache/builder']->build($cache);
                    }
                }

                return $cache;
            };
        }

        if (!isset($container['metadata/loader'])) {
            /**
             * The application's metadata source loader and factory.
             *
             * @param  Container $container A Pimple DI container.
             * @return MetadataLoader
             */
            $container['metadata/loader'] = function (Container $container) {
                $appConfig  = $container['config'];
                $metaConfig = $container['metadata/config'];

                return new MetadataLoader([
                    'logger'    => $container['logger'],
                    'cache'     => $container['metadata/cache'],
                    'paths'     => $container['config']->resolveValues($metaConfig['paths']),
                    'base_path' => $appConfig['base_path'],
                ]);
            };
        }
    }
}
