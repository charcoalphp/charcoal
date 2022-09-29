<?php

namespace Charcoal\Object;

use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
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
        $pimple['revision/config'] = function (Container $pimple): RevisionConfig {
            $configData = $pimple['config']->get('revisions');

            // If the config data is a boolean, it means we only want to affect the enabled state.
            if (is_bool($configData)) {
                $configData = [
                    'enabled' => $configData,
                ];
            }

            return new RevisionConfig($configData);
        };

        $pimple['revision/service'] = function (Container $pimple): RevisionService {
            $services = new ServiceLocator($pimple, ['revision/config', 'model/factory', 'logger']);

            return new RevisionService($services);
        };
    }
}
