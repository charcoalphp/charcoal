<?php

namespace Charcoal\Model\ServiceProvider;

// Dependencies from `Pimple`
use \Pimple\Container;
use \Pimple\ServiceProviderInterface;

// Module `charcoal-property` dependencies
use \Charcoal\Property\PropertyFactory;

// Intra-module (`charcoal-core`) dependencies
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
        /**
        * @param Container $container A container instance.
        * @return ModelFactory
        */
        $container['model/factory'] = function (Container $container) {
            $modelFactory = new ModelFactory();
            $modelFactory->setArguments($container['model/dependencies']);
            return $modelFactory;
        };

        $container['model/builder'] = function (Container $container) {
            $factory = $container['model/factory'];
            $modelBuilder = new ModelBuilder($factory, $container);
            return $modelBuilder;
        };

        $container['model/loader'] = function (Container $container) {


        };

        $container['model/collection/loader'] = function (Container $container) {

        };

        $container['model/dependencies'] = function (Container $container) {
            return [
                'logger'            => $container['logger'],
                'view'              => $container['view'],
                'property_factory'  => $container['model/dependency/property/factory'],
                'metadata_loader'   => $container['model/dependency/metadata/loader']
            ];
        };

        $container['model/dependency/metadata/loader'] = function (Container $container) {
            $metadataLoader = new MetadataLoader();
            return $metadataLoader;
        };

        $container['model/dependency/property/factory'] = function (Container $container) {
            $propertyFactory = new PropertyFactory();
            return $propertyFactory;
        };
    }
}
