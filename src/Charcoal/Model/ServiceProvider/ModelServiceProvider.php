<?php

namespace Charcoal\Model\ServiceProvider;

// Dependencies from `Pimple`
use \Pimple\Container;
use \Pimple\ServiceProviderInterface;

// Module `charcoal-property` dependencies
use \Charcoal\Property\PropertyFactory;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Loader\CollectionLoader;
use \Charcoal\Model\MetadataLoader;
use \Charcoal\Model\ModelBuilder;
use \Charcoal\Model\ModelFactory;

/**
*
*/
class ModelServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container A Pimple DI container instance.
     * @return void
     */
    public function register(Container $container)
    {
        if (!isset($container['property/factory'])) {
            $container['property/factory'] = function (Container $container) {
                $propertyFactory = new PropertyFactory();
                $propertyFactory->setArguments([
                    'container'         => $container,
                    'logger'            => $container['logger'],
                    'metadata_loader'   => $container['metadata/loader']
                ]);
                return $propertyFactory;
            };
        }

        if (!isset($container['metadata/loader'])) {
            $container['metadata/loader'] = function (Container $container) {
                return new MetadataLoader([
                    'logger' => $container['logger']
                ]);
            };
        }

        /**
        * @param Container $container A container instance.
        * @return ModelFactory
        */
        $container['model/factory'] = function (Container $container) {
            $modelFactory = new ModelFactory();
            $modelFactory->setArguments($container['model/dependencies']);
            return $modelFactory;
        };

        /**
        * @param Container $container A container instance.
        * @return ModelBuilder
        */
        $container['model/builder'] = function (Container $container) {
            return new ModelBuilder($container['model/factory'], $container);
        };

        /**
        * @param Container $container A container instance.
        * @return CollectionLoader
        */
        $container['model/collection/loader'] = $container->factory(function (Container $container) {
            return new CollectionLoader([
                'logger'  => $container['logger'],
                'factory' => $container['model/factory']
            ]);
        });

        /**
         * @param Container $container
         * @return array
         */
        $container['model/dependencies'] = function (Container $container) {
            return [
                'container'         => $container,
                'logger'            => $container['logger'],
                'view'              => $container['view'],
                'property_factory'  => $container['property/factory'],
                'metadata_loader'   => $container['metadata/loader']
            ];
        };

        /**
        * @param Container $container A container instance.
        * @return MetadataLoader
        */
        $container['model/dependency/metadata/loader'] = function (Container $container) {
            return $container['metadata/loader'];
        };

        /**
        * @param Container $container A container instance.
        * @return PropertyFactory
        */
        $container['model/dependency/property/factory'] = function (Container $container) {
            return $container['property/factory'];
        };
    }
}
