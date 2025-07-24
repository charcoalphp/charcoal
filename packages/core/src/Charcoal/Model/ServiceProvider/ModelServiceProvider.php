<?php

namespace Charcoal\Model\ServiceProvider;

use DI\Container;
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
class ModelServiceProvider
{
    /**
     * @param Container $container A DI Container.
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
     * @param Container $container A DI Container.
     * @return void
     */
    protected function registerBuilderDependencies(Container $container)
    {
        /**
         * @param Container $container A DI Container.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container->set('model/factory', function (Container $container) {
            return new Factory([
                'base_class' => ModelInterface::class,
                'arguments'  => [ $container->get('model/dependencies') ]
            ]);
        });

        /**
         * @param Container $container A DI Container.
         * @return ModelBuilder
         */
        $container->set('model/builder', function (Container $container) {
            return new ModelBuilder([
                'factory'           => $container->get('model/factory'),
                'metadata_loader'   => $container->get('metadata/loader'),
                'source_factory'    => $container->get('source/factory')
            ]);
        });

        /**
         * @param Container $container A DI Container.
         * @return ModelLoaderBuilder
         */
        $container->set('model/loader/builder', function (Container $container) {
            return new ModelLoaderBuilder([
                'factory' => $container->get('model/factory'),
                'cache'   => $container->get('cache')
            ]);
        });
    }

    /**
     * @param Container $container A DI Container.
     * @return void
     */
    protected function registerCollectionDependencies(Container $container)
    {
        /** The default collection class name. */
        $container->set('model/collection/class', Collection::class);

        /**
         * @param Container $container A DI Container.
         * @return \ArrayAccess|\Traversable
         */
        $container->set('model/collection', function (Container $container) {
            return (new ($container->get('model/collection/class')))();
        });

        /**
         * @param Container $container A DI Container.
         * @return CollectionLoader
         */
        $container->set('model/collection/loader', function (Container $container) {
            $factory = $container->get('model/collection/loader/factory');
            return $factory->create($factory->defaultClass());
        });

        /**
         * @param Container $container A DI Container.
         * @return \Charcoal\Factory\FactoryInterface
         */
        $container->set('model/collection/loader/factory', function (Container $container) {
            return new Factory([
                'default_class' => CollectionLoader::class,
                'arguments'     => [[
                    'logger'        => $container->get('logger'),
                    'factory'       => $container->get('model/factory'),
                    'collection'    => $container->get('model/collection/class')
                ]]
            ]);
        });
    }

    /**
     * @param Container $container A DI Container.
     * @return void
     */
    protected function registerModelDependencies(Container $container)
    {
        // The model dependencies might be already set from elsewhere; defines it if not.
        if (!($container->has('model/dependencies'))) {
            /**
             * @param Container $container A DI Container.
             * @return array The model dependencies array.
             */
            $container->set('model/dependencies', function (Container $container) {
                return [
                    'container'        => $container,
                    'logger'           => $container->get('logger'),
                    'view'             => $container->get('view'),
                    'property_factory' => $container->get('property/factory'),
                    'metadata_loader'  => $container->get('metadata/loader'),
                    'source_factory'   => $container->get('source/factory')
                ];
            });
        }

        // The property factory might be already set from elsewhere; defines it if not.
        if (!($container->has('property/factory'))) {
            /**
             * @param Container $container A DI Container.
             * @return \Charcoal\Factory\FactoryInterface
             */
            $container->set('property/factory', function (Container $container) {
                return new Factory([
                    'base_class'       => PropertyInterface::class,
                    'default_class'    => GenericProperty::class,
                    'resolver_options' => [
                        'prefix' => '\\Charcoal\\Property\\',
                        'suffix' => 'Property'
                    ],
                    'arguments' => [[
                        'container'  => $container,
                        'database'   => $container->get('database'),
                        'logger'     => $container->get('logger'),
                        'translator' => $container->get('translator')
                    ]]
                ]);
            });
        }

        if (!($container->has('source/factory'))) {
            /**
             * @param Container $container A DI Container.
             * @return \Charcoal\Factory\FactoryInterface
             */
            $container->set('source/factory', function (Container $container) {
                return new Factory([
                    'map' => [
                        'database' => DatabaseSource::class
                    ],
                    'base_class' => SourceInterface::class,
                    'arguments'  => [[
                        'logger' => $container->get('logger'),
                        'cache'  => $container->get('cache'),
                        'pdo'    => $container->get('database')
                    ]]
                ]);
            });
        }
    }

    /**
     * @param Container $container A DI Container.
     * @return void
     */
    protected function registerMetadataDependencies(Container $container)
    {
        if (!($container->has('metadata/config'))) {
            /**
             * The application's configset for "config.metadata".
             *
             * @param  Container $container DI Container.
             * @return MetadataConfig
             */
            $container->set('metadata/config', function (Container $container) {
                $appConfig  = ($container->has('config')) ? $container->get('config') : [];
                $metaConfig = isset($appConfig['metadata']) ? $appConfig['metadata'] : null;
                $metaConfig = new MetadataConfig($metaConfig);

                if (($container->has('module/classes'))) {
                    $extraPaths = [];
                    $basePath   = $appConfig['base_path'];
                    $modules    = $container->get('module/classes');
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
            });
        }

        if (!($container->has('metadata/cache'))) {
            /**
             * The application's metadata source cache.
             *
             * @param  Container $container A container instance.
             * @return \Psr\Cache\CacheItemPoolInterface|null
             */
            $container->set('metadata/cache', function (Container $container) {
                $cache = $container->get('metadata/config')['cache'];
                if (!is_object($cache)) {
                    if (is_bool($cache)) {
                        return $cache
                               ? $container->get('cache')
                               : $container->get('cache/builder')->build('memory');
                    }

                    if (is_array($cache)) {
                        return $container->get('cache/builder')->build($cache);
                    }
                }

                return $cache;
            });
        }

        if (!($container->has('metadata/loader'))) {
            /**
             * The application's metadata source loader and factory.
             *
             * @param  Container $container A DI Container.
             * @return MetadataLoader
             */
            $container->set('metadata/loader', function (Container $container) {
                $appConfig  = $container->get('config');
                $metaConfig = $container->get('metadata/config');

                return new MetadataLoader([
                    'logger'    => $container->get('logger'),
                    'cache'     => $container->get('metadata/cache'),
                    'paths'     => $container->get('config')->resolveValues($metaConfig['paths']),
                    'base_path' => $appConfig['base_path'],
                ]);
            });
        }
    }
}
