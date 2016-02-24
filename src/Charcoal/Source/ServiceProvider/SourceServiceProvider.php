<?php

namespace Charcoal\Source\ServiceProvider;

// Pimple dependencies
use \Pimple\Container;

// Intra-module (`charcoal-source`) dependencies
use \Charcoal\Source\SourceFactory;

/**
 * Source Service Provider
 */
class SourceServiceProvider
{
    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    public function register(Container $container)
    {
        /**
         * @param Container $container A pimple DI container.
         * @return SourceFactory
         */
        $container['source/factory'] = function (Container $container) {
        
            $factory = new SourceFactory();
            $factory->setArguments([
                'database' => $container['database']
            ]);
            return $factory;
        };
    }
}
