<?php

namespace Charcoal\Object;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Object Service Provider
 */
class ObjectServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $this->registerRevisionServices($pimple);
    }

    private function registerRevisionServices(Container $pimple)
    {
        $pimple['revision/service'] = function (Container $pimple): RevisionService {
            return new RevisionService([
                'config'        => $pimple['admin/config'],
                'model/factory' => $pimple['model/factory'],
            ]);
        };
    }
}
