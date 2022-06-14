<?php

namespace Charcoal\App\ServiceProvider;

use Charcoal\App\Service\PackageMapService;
use Pimple\Container;

/**
 * Class PackageServiceProvider
 */
class PackageServiceProvider implements \Pimple\ServiceProviderInterface
{

    /**
     * @param Container $container Pimple DI container.
     * @return void
     */
    public function register(Container $container)
    {
        $container['package/map'] = function (Container $container) {
            return new PackageMapService([
                'basePath' => $container['config']['base_path']
            ]);
        };
    }
}
