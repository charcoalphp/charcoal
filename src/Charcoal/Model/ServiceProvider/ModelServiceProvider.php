<?php

namespace Charcoal\Model\ServiceProvider;

// Dependencies from `Pimple`
use \Pimple\Container;
use \Pimple\ServiceProviderInterface;

// Module `charcoal-factory` dependencies
use \Charcoal\Factory\GenericFactory as Factory;

// Module `charcoal-property` dependencies
use \Charcoal\Property\PropertyInterface;
use \Charcoal\Property\GenericProperty;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Loader\CollectionLoader;
use \Charcoal\Model\MetadataLoader;
use \Charcoal\Model\ModelFactory;
use \Charcoal\Model\ModelInterface;
use \Charcoal\Source\SourceInterface;
use \Charcoal\Source\DatabaseSource;

/**
 * Model Service Providers.
 *
 * ##Container dependencies
 *
 * The following keys are expected to be set on the container
 * (from external sources / providers):
 *
 * - `cache` A PSR-6 compliant cache pool.
 * - `config` A charcoal app config (\Charcoal\Config\ConfigInterface)q
 * - `logger` A PSR-3 compliant logger.
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
     * @param Container $container A Pimple DI container instance.
     * @return void
     */
    public function register(Container $container)
    {
        $this->registerModelDependencies($container);

        /**
         * @param Container $container A container instance.
         * @return ModelFactory
         */
        $container['model/factory'] = function (Container $container) {
            return new ModelFactory([
                'base_class' => ModelInterface::class,
                'arguments'  => [ $container['model/dependencies'] ]
            ]);
        };

        /**
         * @param Container $container A container instance.
         * @return CollectionLoader
         */
        $container['model/collection/loader'] = $container->factory(function (Container $container) {
            return new CollectionLoader([
                'logger'     => $container['logger'],
                'factory'    => $container['model/factory'],
                'collection' => $container['model/collection/class']
            ]);
        });

        /** The default collection class name. */
        $container['model/collection/class'] = \Charcoal\Model\Collection::class;
    }

    /**
     * @param Container $container A Pimple DI container instance.
     * @return void
     */
    protected function registerModelDependencies(Container $container)
    {
        // The model dependencies might be already set from elsewhere; defines it if not.
        if (!isset($container['model/dependencies'])) {
            /**
             * @param Container $container A container instance.
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
             * @param Container $container Pimple DI container.
             * @return \Charcoal\Factory\FactoryInterface
             */
            $container['property/factory'] = function (Container $container) {
                return new Factory([
                    'base_class'       => PropertyInterface::class,
                    'default_class'    => GenericProperty::class,
                    'resolver_options' => [
                        'prefix' => '\Charcoal\Property\\',
                        'suffix' => 'Property'
                    ],
                    'arguments' => [[
                        'container' => $container,
                        'logger'    => $container['logger']
                    ]]
                ]);
            };
        }

        if (!isset($container['metadata/loader'])) {
            /**
             * @param Container $container Pimple DI container.
             * @return MetadataLoader
             */
            $container['metadata/loader'] = function (Container $container) {
                return new MetadataLoader([
                    'logger'    => $container['logger'],
                    'cache'     => $container['cache'],
                    'base_path' => $container['config']['base_path'],
                    'paths'     => $container['config']['metadata.paths']
                ]);
            };
        }

        if (!isset($container['source/factory'])) {
            /**
             * @param Container $container Pimple DI container.
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
}
